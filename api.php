<?php
/**
 * TechText - API Endpoints
 * Handles all AJAX requests for conversion and history operations
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/parsers.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Generate CSRF token if not exists
if (empty($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Get request data
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Verify CSRF for POST requests
if ($method === 'POST') {
    $headers = getallheaders();
    $csrfHeader = $headers['X-CSRF-Token'] ?? '';
    $csrfPost = $_POST['csrf_token'] ?? '';
    $csrfReceived = $csrfHeader ?: $csrfPost;
    
    if (!hash_equals($_SESSION[CSRF_TOKEN_NAME], $csrfReceived)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid security token']);
        exit;
    }
}

// Route requests
try {
    switch ($action) {
        case 'convert':
            handleConvert();
            break;
        case 'history':
            handleHistory();
            break;
        case 'get':
            handleGet();
            break;
        case 'delete':
            handleDelete();
            break;
        case 'clear':
            handleClear();
            break;
        case 'upload':
            handleUpload();
            break;
        case 'csrf':
            echo json_encode(['success' => true, 'token' => $_SESSION[CSRF_TOKEN_NAME]]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Handle markup conversion
 */
function handleConvert() {
    global $SUPPORTED_MARKUP, $SUPPORTED_OUTPUT, $ERROR_MESSAGES;
    
    // Validate input
    $content = $_POST['content'] ?? '';
    $markupType = $_POST['markup_type'] ?? '';
    $outputFormat = $_POST['output_format'] ?? '';
    
    // Sanitize and validate content
    $content = sanitizeInput($content);
    
    if (empty($content)) {
        echo json_encode(['success' => false, 'error' => $ERROR_MESSAGES['empty_input']]);
        return;
    }
    
    if (strlen($content) > MAX_INPUT_LENGTH) {
        echo json_encode(['success' => false, 'error' => 'Content exceeds maximum length']);
        return;
    }
    
    if (!array_key_exists($markupType, $SUPPORTED_MARKUP)) {
        echo json_encode(['success' => false, 'error' => $ERROR_MESSAGES['unsupported_format']]);
        return;
    }
    
    if (!array_key_exists($outputFormat, $SUPPORTED_OUTPUT)) {
        echo json_encode(['success' => false, 'error' => $ERROR_MESSAGES['unsupported_format']]);
        return;
    }
    
    try {
        // Perform conversion
        $output = MarkupParsers::convert($content, $markupType, $outputFormat);
        
        // Save to database
        $db = Database::getInstance();
        $sessionId = session_id();
        $conversionId = $db->saveConversion($content, $markupType, $outputFormat, $output, $sessionId);
        
        echo json_encode([
            'success' => true,
            'output' => $output,
            'conversion_id' => $conversionId,
            'markup_type' => $markupType,
            'output_format' => $outputFormat
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Handle get conversion history
 */
function handleHistory() {
    $db = Database::getInstance();
    $sessionId = session_id();
    $history = $db->getConversionHistory($sessionId, 20);
    
    // Sanitize output
    foreach ($history as &$item) {
        $item['preview'] = htmlspecialchars($item['preview'], ENT_QUOTES, 'UTF-8');
        $item['markup_type'] = htmlspecialchars($item['markup_type'], ENT_QUOTES, 'UTF-8');
    }
    
    echo json_encode(['success' => true, 'history' => $history]);
}

/**
 * Handle get single conversion
 */
function handleGet() {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        return;
    }
    
    $db = Database::getInstance();
    $sessionId = session_id();
    $conversion = $db->getConversion($id, $sessionId);
    
    if ($conversion) {
        // Escape output for safe display
        $conversion['output_content'] = htmlspecialchars($conversion['output_content'], ENT_QUOTES, 'UTF-8');
        echo json_encode(['success' => true, 'conversion' => $conversion]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Conversion not found']);
    }
}

/**
 * Handle delete conversion
 */
function handleDelete() {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        return;
    }
    
    $db = Database::getInstance();
    $sessionId = session_id();
    
    if ($db->deleteConversion($id, $sessionId)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete']);
    }
}

/**
 * Handle clear all history
 */
function handleClear() {
    $db = Database::getInstance();
    $sessionId = session_id();
    $deleted = $db->clearHistory($sessionId);
    
    echo json_encode(['success' => true, 'deleted' => $deleted]);
}

/**
 * Handle file upload
 */
function handleUpload() {
    global $ERROR_MESSAGES;
    
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
        return;
    }
    
    $file = $_FILES['file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Upload failed']);
        return;
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        echo json_encode(['success' => false, 'error' => $ERROR_MESSAGES['file_too_large']]);
        return;
    }
    
    // Validate file type (only text files)
    $allowedTypes = ['text/plain', 'text/markdown', 'text/x-markdown', 'text/html', 'text/x-rst', 'application/octet-stream'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes) && !str_starts_with($mimeType, 'text/')) {
        echo json_encode(['success' => false, 'error' => $ERROR_MESSAGES['invalid_file']]);
        return;
    }
    
    // Read file content
    $content = file_get_contents($file['tmp_name']);
    
    if ($content === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to read file']);
        return;
    }
    
    // Clean up temp file
    unlink($file['tmp_name']);
    
    echo json_encode([
        'success' => true,
        'content' => $content,
        'filename' => htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8')
    ]);
}

/**
 * Sanitize input content
 */
function sanitizeInput($input) {
    // Remove null bytes
    $input = str_replace("\0", '', $input);
    
    // Normalize line endings
    $input = str_replace(["\r\n", "\r"], "\n", $input);
    
    return trim($input);
}
?>
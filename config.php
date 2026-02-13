<?php
/**
 * TechText - Markup Language Converter
 * Configuration File
 * 
 * Built by: Santosh Baral
 * Company: Techzen Corporation
 * Web: https://techzeninc.com
 */

// Prevent direct access
if (!defined('TECHTEXT_LOADED')) {
    define('TECHTEXT_LOADED', true);
}

// Application settings
define('APP_NAME', 'TechText');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Santosh Baral');
define('APP_COMPANY', 'Techzen Corporation');
define('APP_WEBSITE', 'https://techzeninc.com');

// Database settings
define('DB_PATH', __DIR__ . '/data/techtext.db');
define('DB_TIMEOUT', 5000);

// Security settings
define('SESSION_NAME', 'TechTextSession');
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_INPUT_LENGTH', 1000000); // 1MB max input
define('MAX_FILE_SIZE', 2097152); // 2MB max file upload

// Supported markup languages
$SUPPORTED_MARKUP = [
    'markdown' => 'Markdown',
    'bbcode' => 'BBCode',
    'rst' => 'reStructuredText',
    'textile' => 'Textile',
    'html' => 'HTML',
    'wiki' => 'Wiki Markup'
];

// Supported output formats
$SUPPORTED_OUTPUT = [
    'plaintext' => 'Plain Text',
    'richtext' => 'Rich Text (HTML)',
    'html' => 'Clean HTML',
    'json' => 'JSON'
];

// Error messages
$ERROR_MESSAGES = [
    'invalid_markup' => 'Invalid markup content provided.',
    'unsupported_format' => 'Unsupported markup language or output format.',
    'empty_input' => 'Please provide some content to convert.',
    'file_too_large' => 'File size exceeds maximum limit of 2MB.',
    'invalid_file' => 'Invalid file type. Only text files are supported.',
    'database_error' => 'Database operation failed.',
    'csrf_invalid' => 'Invalid security token. Please refresh the page.',
    'parse_error' => 'Failed to parse markup content.'
];

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    session_name(SESSION_NAME);
    session_start();
}
?>
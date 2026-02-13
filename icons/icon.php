<?php
/**
 * TechText - Icon Generator
 * Generates PNG icons from SVG for PWA compatibility
 */

// Get requested size
$size = isset($_GET['size']) ? intval($_GET['size']) : 192;

// Validate size
$validSizes = [72, 96, 128, 144, 152, 192, 384, 512];
if (!in_array($size, $validSizes)) {
    $size = 192;
}

// Read SVG content
$svgPath = __DIR__ . '/icon.svg';
if (!file_exists($svgPath)) {
    header('HTTP/1.1 404 Not Found');
    exit('Icon not found');
}

$svgContent = file_get_contents($svgPath);

// If Imagick is available, use it to convert SVG to PNG
if (extension_loaded('imagick')) {
    try {
        $imagick = new Imagick();
        $imagick->setBackgroundColor(new ImagickPixel('transparent'));
        $imagick->readImageBlob($svgContent);
        $imagick->setImageFormat('png32');
        $imagick->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);
        
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=31536000');
        echo $imagick->getImageBlob();
        $imagick->clear();
        exit;
    } catch (Exception $e) {
        // Fall through to fallback
    }
}

// Fallback: Return SVG with proper headers for browsers that support it
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=31536000');
echo $svgContent;
?>
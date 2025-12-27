<?php
/**
 * P4PDF Compress API Endpoint for Vercel
 * 
 * POST /api/compress
 * Body: multipart/form-data with 'file' field
 * Query params: public_key, secret_key, compression_level (optional)
 */

require_once(__DIR__ . '/../vendor/autoload.php');

use P4Pdf\P4Pdf;
use P4Pdf\Exceptions\ProcessException;
use P4Pdf\Exceptions\AuthException;
use P4Pdf\Exceptions\UploadException;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get API keys from query params or environment variables
    $publicKey = $_GET['public_key'] ?? $_ENV['P4PDF_PUBLIC_KEY'] ?? null;
    $secretKey = $_GET['secret_key'] ?? $_ENV['P4PDF_SECRET_KEY'] ?? null;
    
    if (!$publicKey || !$secretKey) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing API keys. Provide public_key and secret_key in query params or set P4PDF_PUBLIC_KEY and P4PDF_SECRET_KEY environment variables.']);
        exit;
    }

    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded or upload error occurred.']);
        exit;
    }

    $uploadedFile = $_FILES['file'];
    $compressionLevel = $_GET['compression_level'] ?? 'recommended';

    // Initialize P4PDF
    $p4pdf = new P4Pdf($publicKey, $secretKey);
    
    // Create compress task
    $task = $p4pdf->newTask('compress');
    $task->setCompressionLevel($compressionLevel);
    
    // Add uploaded file
    $file = $task->addFile($uploadedFile['tmp_name']);
    
    // Execute task
    $task->execute();
    
    // Download to temporary location
    $tempOutput = sys_get_temp_dir() . '/' . uniqid('compressed_') . '.pdf';
    $task->download($tempOutput);
    
    // Read the file and send as response
    $fileContent = file_get_contents($tempOutput);
    $base64Content = base64_encode($fileContent);
    
    // Clean up temp file
    unlink($tempOutput);
    
    // Return the compressed PDF as base64
    echo json_encode([
        'success' => true,
        'filename' => 'compressed.pdf',
        'data' => $base64Content,
        'size' => strlen($fileContent)
    ]);
    
} catch (AuthException $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication failed', 'message' => $e->getMessage()]);
} catch (UploadException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload failed', 'message' => $e->getMessage()]);
} catch (ProcessException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Processing failed', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred', 'message' => $e->getMessage()]);
}

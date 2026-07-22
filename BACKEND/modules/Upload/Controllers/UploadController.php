<?php

use App\Core\Response;
use App\Core\Database;

class UploadController extends \App\Core\BaseController
{
    private $uploadDir;
    private $uploadUrlBase;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
        $this->uploadUrlBase = '/uploads/';
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Upload a single image
     */
    public function uploadImage($request)
    {
        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return Response::error('No file uploaded or upload error', 400);
            }

            $file = $_FILES['file'];

            if ($file['size'] > $this->maxFileSize) {
                return Response::error('File size exceeds maximum limit of 5MB', 400);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $this->allowedTypes)) {
                return Response::error('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed', 400);
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_', true) . '_' . time() . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return Response::error('Failed to move uploaded file', 500);
            }

            // Create thumbnail
            $this->createThumbnail($filepath, $this->uploadDir . 'thumb_' . $filename, 200);

            $url = $this->uploadUrlBase . $filename;

            return Response::success([
                'url' => $url,
                'filename' => $filename,
                'thumbnail_url' => $this->uploadUrlBase . 'thumb_' . $filename,
                'size' => $file['size'],
                'mime_type' => $mimeType
            ], 'Image uploaded successfully');

        } catch (\Exception $e) {
            return Response::error('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Upload multiple images
     */
    public function uploadMultiple($request)
    {
        try {
            if (!isset($_FILES['files'])) {
                return Response::error('No files uploaded', 400);
            }

            $files = $_FILES['files'];
            $uploaded = [];
            $errors = [];

            $fileCount = is_array($files['name']) ? count($files['name']) : 1;

            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                    'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                    'size' => is_array($files['size']) ? $files['size'][$i] : $files['size'],
                    'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
                ];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = ['name' => $file['name'], 'error' => 'Upload error'];
                    continue;
                }

                if ($file['size'] > $this->maxFileSize) {
                    $errors[] = ['name' => $file['name'], 'error' => 'File too large'];
                    continue;
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $this->allowedTypes)) {
                    $errors[] = ['name' => $file['name'], 'error' => 'Invalid type'];
                    continue;
                }

                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_', true) . '_' . time() . '.' . $extension;
                $filepath = $this->uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $this->createThumbnail($filepath, $this->uploadDir . 'thumb_' . $filename, 200);
                    $uploaded[] = [
                        'url' => $this->uploadUrlBase . $filename,
                        'filename' => $filename,
                        'thumbnail_url' => $this->uploadUrlBase . 'thumb_' . $filename
                    ];
                } else {
                    $errors[] = ['name' => $file['name'], 'error' => 'Move failed'];
                }
            }

            return Response::success([
                'uploaded' => $uploaded,
                'errors' => $errors,
                'count' => count($uploaded)
            ], count($uploaded) . ' images uploaded');
        } catch (\Exception $e) {
            return Response::error('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Upload product image and link to product
     */
    public function uploadProductImage($request)
    {
        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return Response::error('No file uploaded', 400);
            }

            $productId = $request['body']['product_id'] ?? ($_POST['product_id'] ?? null);
            $tenantId = $request['body']['tenant_id'] ?? ($_POST['tenant_id'] ?? null);

            if (!$productId) {
                return Response::error('product_id is required', 400);
            }

            // First upload the image
            $uploadResult = $this->uploadImage($request);
            $uploadData = json_decode($uploadResult, true);

            if (!isset($uploadData['data']['url'])) {
                return $uploadResult;
            }

            $imageUrl = $uploadData['data']['url'];

            // Update product with image URL
            $pdo = Database::getInstance()->connect();
            $stmt = $pdo->prepare("UPDATE products SET image_url = ?, updated_at = NOW() WHERE product_id = ?");
            $params = [$imageUrl, $productId];

            if ($tenantId) {
                $stmt = $pdo->prepare("UPDATE products SET image_url = ?, updated_at = NOW() WHERE product_id = ? AND tenant_id = ?");
                $params = [$imageUrl, $productId, $tenantId];
            }

            $stmt->execute($params);

            return Response::success([
                'product_id' => (int)$productId,
                'image_url' => $imageUrl,
                'thumbnail_url' => $uploadData['data']['thumbnail_url'] ?? null
            ], 'Product image uploaded and linked');
        } catch (\Exception $e) {
            return Response::error('Product image upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete image
     */
    public function deleteImage($request)
    {
        try {
            $data = $request['body'] ?? [];
            $filename = $data['filename'] ?? null;

            if (!$filename) {
                return Response::error('Filename is required', 400);
            }

            // Prevent path traversal
            $filename = basename($filename);
            $filepath = $this->uploadDir . $filename;

            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    // Also delete thumbnail
                    $thumbPath = $this->uploadDir . 'thumb_' . $filename;
                    if (file_exists($thumbPath)) {
                        @unlink($thumbPath);
                    }
                    return Response::success(null, 'Image deleted successfully');
                } else {
                    return Response::error('Failed to delete image', 500);
                }
            } else {
                return Response::error('Image not found', 404);
            }
        } catch (\Exception $e) {
            return Response::error('Delete failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List uploaded images
     */
    public function listImages($request)
    {
        try {
            $images = [];
            $files = glob($this->uploadDir . 'img_*.*');

            foreach ($files as $filepath) {
                $filename = basename($filepath);
                if (strpos($filename, 'thumb_') === 0) continue;

                $images[] = [
                    'filename' => $filename,
                    'url' => $this->uploadUrlBase . $filename,
                    'thumbnail_url' => $this->uploadUrlBase . 'thumb_' . $filename,
                    'size' => filesize($filepath),
                    'modified' => date('Y-m-d H:i:s', filemtime($filepath))
                ];
            }

            return Response::success([
                'images' => $images,
                'count' => count($images)
            ], 'Images retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to list images: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a thumbnail from source image
     */
    private function createThumbnail($sourcePath, $destPath, $maxSize)
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $sourcePath);
        finfo_close($finfo);

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$source) return false;

        $origWidth = imagesx($source);
        $origHeight = imagesy($source);

        if ($origWidth <= $maxSize && $origHeight <= $maxSize) {
            imagedestroy($source);
            return copy($sourcePath, $destPath);
        }

        $ratio = min($maxSize / $origWidth, $maxSize / $origHeight);
        $newWidth = (int)($origWidth * $ratio);
        $newHeight = (int)($origHeight * $ratio);

        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($thumb, $destPath, 85);
                break;
            case 'image/png':
                imagepng($thumb, $destPath, 8);
                break;
            case 'image/gif':
                imagegif($thumb, $destPath);
                break;
            case 'image/webp':
                imagewebp($thumb, $destPath, 85);
                break;
        }

        imagedestroy($source);
        imagedestroy($thumb);
        return true;
    }
}

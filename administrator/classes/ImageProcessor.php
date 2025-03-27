<?php

class ImageProcessor {
    private const MAX_FILE_SIZE = 2097152; // 2MB in bytes
    private const IMAGE_WIDTH = 500;
    private const IMAGE_HEIGHT = 500;
    private const JPG_QUALITY = 85;
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png'];

    public static function validateImage($file) {
        $errors = [];

        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['error' => true, 'message' => 'No file was uploaded'];
        }

        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $errors[] = 'File size must be less than 2MB';
        }

        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, self::ALLOWED_TYPES)) {
            $errors[] = 'Only JPG and PNG files are allowed';
        }

        // Verify it's a real image
        if (!getimagesize($file['tmp_name'])) {
            $errors[] = 'File is not a valid image';
        }

        if (!empty($errors)) {
            return ['error' => true, 'message' => implode(', ', $errors)];
        }

        return ['error' => false];
    }

    public static function processImage($file, $targetPath) {
        // Create image from uploaded file
        $sourceImage = null;
        $mime_type = mime_content_type($file['tmp_name']);
        
        switch ($mime_type) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($file['tmp_name']);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Get original dimensions
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Calculate new dimensions while maintaining aspect ratio
        $ratio = min(self::IMAGE_WIDTH/$width, self::IMAGE_HEIGHT/$height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);

        // Create new image with exact dimensions (500x500)
        $finalImage = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
        
        // Fill with white background
        $white = imagecolorallocate($finalImage, 255, 255, 255);
        imagefill($finalImage, 0, 0, $white);

        // Calculate centering position
        $x = (self::IMAGE_WIDTH - $new_width) / 2;
        $y = (self::IMAGE_HEIGHT - $new_height) / 2;

        // Copy and resize the image
        imagecopyresampled(
            $finalImage, $sourceImage,
            $x, $y, 0, 0,
            $new_width, $new_height,
            $width, $height
        );

        // Save the final image
        $result = imagejpeg($finalImage, $targetPath, self::JPG_QUALITY);

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($finalImage);

        return $result;
    }
} 
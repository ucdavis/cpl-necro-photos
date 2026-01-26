<?php

namespace App\Services;

class ImageService
{
    /**
     * Generate a centered thumbnail within a fixed canvas size.
     * Returns true on success, false on failure.
     */
    public function generateThumbnail(string $sourcePath, string $destPath, int $maxWidth = 350, int $maxHeight = 350): bool
    {
        // Ensure source exists
        if (!is_file($sourcePath)) {
            return false;
        }

        // Create destination directory if needed
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                return false;
            }
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Calculate new dimensions maintaining aspect ratio
        $aspectRatio = $width / $height;

        if ($width > $height) {
            $newWidth = $maxWidth;
            $newHeight = (int) round($maxWidth / $aspectRatio);
        } else {
            $newHeight = $maxHeight;
            $newWidth = (int) round($maxHeight * $aspectRatio);
        }

        // Ensure we don't exceed max dimensions (safety)
        if ($newWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round($maxWidth / $aspectRatio);
        }
        if ($newHeight > $maxHeight) {
            $newHeight = $maxHeight;
            $newWidth = (int) round($maxHeight * $aspectRatio);
        }

        // Create source image resource
        $sourceImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Create destination canvas
        $destImage = imagecreatetruecolor($maxWidth, $maxHeight);

        // Handle transparency for PNG/GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagecolortransparent($destImage, imagecolorallocatealpha($destImage, 0, 0, 0, 127));
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
        }

        // Fill background with white for JPEG
        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            $white = imagecolorallocate($destImage, 255, 255, 255);
            imagefill($destImage, 0, 0, $white);
        }

        // Center the resized image
        $xOffset = (int) round(($maxWidth - $newWidth) / 2);
        $yOffset = (int) round(($maxHeight - $newHeight) / 2);

        $resampled = imagecopyresampled(
            $destImage, $sourceImage,
            $xOffset, $yOffset, 0, 0,
            $newWidth, $newHeight, $width, $height
        );

        if (!$resampled) {
            // Clean up safely: destroy resources on PHP <8, unset objects on PHP >=8
            if (is_resource($sourceImage)) {
                imagedestroy($sourceImage);
            } else {
                $sourceImage = null;
            }
            if (is_resource($destImage)) {
                imagedestroy($destImage);
            } else {
                $destImage = null;
            }
            return false;
        }

        // Save thumbnail
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $result = imagejpeg($destImage, $destPath, 85);
                break;
            case 'image/png':
                $result = imagepng($destImage, $destPath, 8);
                break;
            case 'image/gif':
                $result = imagegif($destImage, $destPath);
                break;
        }

        // Free memory safely: call imagedestroy only for resources (PHP < 8); for GdImage objects on PHP >= 8, unset to allow GC
        if (is_resource($sourceImage)) {
            imagedestroy($sourceImage);
        } else {
            $sourceImage = null;
        }
        if (is_resource($destImage)) {
            imagedestroy($destImage);
        } else {
            $destImage = null;
        }

        return (bool) $result;
    }
}

<?php

namespace App\Services;

class ImageService
{
    /**
     * Generate a landscape thumbnail (350x262) with portrait rotation.
     * Returns true on success, false on failure.
     */
    public function generateThumbnail(string $sourcePath, string $destPath, int $maxWidth = 350, int $maxHeight = 263): bool
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
        
        // Determine if image is portrait and needs rotation
        $isPortrait = $height > $width;
        
        // If portrait, we'll work with rotated dimensions for calculations
        if ($isPortrait) {
            // Swap width and height for landscape calculation
            $calcWidth = $height;
            $calcHeight = $width;
        } else {
            $calcWidth = $width;
            $calcHeight = $height;
        }

        // Calculate new dimensions for landscape format (350x262)
        $aspectRatio = $calcWidth / $calcHeight;
        $targetAspect = $maxWidth / $maxHeight; // 350/262 ≈ 1.336
        
        if ($aspectRatio > $targetAspect) {
            // Image is wider than target - fit by width
            $newWidth = $maxWidth;
            $newHeight = (int) round($maxWidth / $aspectRatio);
        } else {
            // Image is taller than target - fit by height  
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
        
        // Rotate portrait images to landscape
        if ($isPortrait) {
            $rotatedImage = imagerotate($sourceImage, 90, 0);
            if ($rotatedImage) {
                // Clean up original
                if (is_resource($sourceImage)) {
                    imagedestroy($sourceImage);
                } else {
                    $sourceImage = null;
                }
                $sourceImage = $rotatedImage;
                // Update dimensions after rotation
                $width = $height; // Original height becomes new width
                $height = imagesx($sourceImage); // Get actual height of rotated image
            }
        }

        // Create destination canvas (always landscape 350x262)
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
                $result = imagejpeg($destImage, $destPath, 30);
                break;
            case 'image/png':
                $result = imagepng($destImage, $destPath, 9);
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

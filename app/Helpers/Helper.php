<?php
namespace App\Helpers;

class Helper {

    public static function shortEmail($email, $mask = "**********") {
        
        return $email;
    }

    public static function shortNumber($number, $mask = "**********") {
        
        return $number;
    }

    public static function compressFile($source, $destination, $quality) { 
        // Get image info 
        $imgInfo = getimagesize($source); 
        $mime = $imgInfo['mime']; 
        // Create a new image from file 
        switch($mime){ 
            case 'image/jpeg': 
                $image = imagecreatefromjpeg($source); 
               imagejpeg($image, $destination, $quality);
                break; 
            case 'image/png': 
                $image = imagecreatefrompng($source); 
                imagepng($image, $destination, $quality);
                break; 
            case 'image/gif': 
                $image = imagecreatefromgif($source); 
                imagegif($image, $destination, $quality);
                break; 
            default: 
                $image = imagecreatefromjpeg($source); 
               imagejpeg($image, $destination, $quality);
        } 
        // Return compressed image
        return $destination;
    }

    /**
     * Uploads a file to ImageKit and returns its public URL. Throws on any
     * failure so callers can fall back to local disk (kept only as a
     * last-resort fallback — local disk doesn't survive a Render redeploy).
     */
    public static function compressImageFile($filePath, $mimeType, $quality = 75, $maxDim = 1280)
    {
        if (!function_exists('imagecreatefromstring')) {
            return $filePath;
        }

        try {
            $data = @file_get_contents($filePath);
            if (!$data) return $filePath;

            $src = @imagecreatefromstring($data);
            if (!$src) return $filePath;

            $width = imagesx($src);
            $height = imagesy($src);

            if ($width > $maxDim || $height > $maxDim) {
                if ($width > $height) {
                    $newWidth = $maxDim;
                    $newHeight = intval($height * ($maxDim / $width));
                } else {
                    $newHeight = $maxDim;
                    $newWidth = intval($width * ($maxDim / $height));
                }

                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($src);
                $src = $dst;
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'opt_') . '.jpg';
            imagejpeg($src, $tmpPath, $quality);
            imagedestroy($src);

            if (file_exists($tmpPath) && filesize($tmpPath) > 0) {
                return $tmpPath;
            }
            return $filePath;
        } catch (\Throwable $e) {
            \Log::warning('Image compression error: ' . $e->getMessage());
            return $filePath;
        }
    }

    public static function uploadToImageKit($file, $folder = '/uploads')
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'img_' . time() . '_' . uniqid() . '.' . $extension;

        $privateKey = config('imagekit.private_key');

        if (empty($privateKey)) {
            throw new \Exception('IMAGEKIT_PRIVATE_KEY is not configured on the server.');
        }

        $realPath = $file->getRealPath();
        $mimeType = $file->getMimeType();

        // Automatically optimize & compress image to save bandwidth and storage space
        $optimizedPath = self::compressImageFile($realPath, $mimeType, 75, 1280);
        $uploadFile = file_exists($optimizedPath) ? $optimizedPath : $realPath;

        $postData = [
            'file' => new \CURLFile($uploadFile, $mimeType, $filename),
            'fileName' => $filename,
            'folder' => $folder,
            'useUniqueFileName' => 'true',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('imagekit.upload_url'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($optimizedPath !== $realPath && file_exists($optimizedPath)) {
            @unlink($optimizedPath);
        }

        if ($curlError) {
            \Log::error('ImageKit cURL error: ' . $curlError);
            throw new \Exception('Upload connection failed: ' . $curlError);
        }

        if ($statusCode === 200) {
            $json = json_decode($response, true);
            if (isset($json['url'])) {
                return $json['url'];
            }
            throw new \Exception('ImageKit returned 200 but no URL in response.');
        }

        \Log::error("ImageKit upload failed [{$statusCode}]: {$response}");
        throw new \Exception("ImageKit error ({$statusCode})");
    }

    /**
     * Resolves a stored image value (either a full ImageKit URL or a legacy
     * local filename) to a URL the client can load.
     */
    public static function resolveImagePath($value, $localDir = 'assets/images/driver')
    {
        if (empty($value)) {
            return '';
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        if (file_exists(public_path($localDir . '/' . $value))) {
            return asset($localDir) . '/' . $value;
        }
        return asset('assets/images/placeholder_image.jpg');
    }

    /**
     * Get active currency symbol dynamically from tj_currency table
     */
    public static function getCurrencySymbol()
    {
        static $symbol = null;
        if ($symbol === null) {
            try {
                $currency = \DB::table('tj_currency')->where('statut', 'yes')->first() 
                         ?? \DB::table('tj_currency')->first();
                $symbol = $currency ? ($currency->symbole ?? '') : '';
            } catch (\Exception $e) {
                $symbol = '';
            }
        }
        return $symbol;
    }

    /**
     * Format a transaction ID as a 7-digit zero-padded string (e.g. 1 -> "0000001")
     */
    public static function formatTxnId($id)
    {
        if (empty($id)) {
            return '0000001';
        }
        $clean = trim((string)$id);
        if (is_numeric($clean)) {
            return str_pad($clean, 7, '0', STR_PAD_LEFT);
        }
        return $clean;
    }
}
?>
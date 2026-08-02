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
    public static function uploadToImageKit($file, $folder = '/uploads')
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'img_' . time() . '_' . uniqid() . '.' . $extension;

        $privateKey = config('imagekit.private_key');

        if (empty($privateKey)) {
            throw new \Exception('IMAGEKIT_PRIVATE_KEY is not configured on the server.');
        }

        $postData = [
            'file' => new \CURLFile($file->getRealPath(), $file->getMimeType(), $filename),
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
}
?>
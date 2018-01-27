<?php

/*
 * A wrapper around an image resource. 
 * Doing this allows me to type hint the image parameters. 
 */

namespace Programster\ImageLib;

class Image
{
    private $m_imageResource;
    
    
    /**
     * Private constructor for internal use only. 
     * If you wish to create one of these objects, please use one of the static create methods.
     * Can't wait for polymorphism in PHP.
     * @param type $resource
     */
    private function __construct($resource)
    {
        $this->m_imageResource = $resource;
    }
    
    
    /**
     * Create an image resource from the specified filepath.
     * You will need this for all the other functions, so it's pretty handy.
     * @param string $filepath - the path to the file you wish to manipulate.
     * @return \Programster\ImageLib\ImageLib
     * @throws \Exception
     */
    public static function createFromFilepath(string $filepath)
    {
        if (!file_exists($filepath))
        {
            throw new \Exception("Could not find provided file: {$filepath}");
        }
        
        $parts = explode(".", $filepath);
        $extension = end($parts);
        
        if ($extension === FALSE)
        {
            throw new \Exception("Failed to find extension of file");
        }
        
        $map = self::getLoadingMap();
        $loweredExtension = strtolower($extension);
        
        if (in_array($loweredExtension, array_keys($map)))
        {
            $callback = $map[$loweredExtension];
            $result = $callback($filepath);
        }
        else
        {
            throw new \Exception("Unsupported image extension: {$extension}");
        }
        
        if ($result === FALSE)
        {
            throw new \Exception("Failed to convert extension: {$extension}, into an image.");
        }
        
        // if we get here, $result is a resource.
        return new Image($result);
    }
    
    
    /**
     * Get a map of file extensions to callbacks that can be used to load the image.
     * @return array
     */
    private static function getLoadingMap() : array
    {
        return array(
            'bmp'  => function($input) { return imagecreatefrombmp($input); },
            'gif'  => function($input) { return imagecreatefromgif($input); },
            'jpg'  => function($input) { return imagecreatefromjpeg($input); },
            'jpeg' => function($input) { return imagecreatefromjpeg($input); },
            'png'  => function($input) { return imagecreatefrompng($input); },
            'gd'   => function($input) { return imagecreatefromgd($input); },
            'gd2'  => function($input) { return imagecreatefromgd2($input); },
            'webp' => function($input) { return imagecreatefromwebp($input); },
            'wbmp' => function($input) { return imagecreatefromwbmp($input); },
            'xbm'  => function($input) { return imagecreatefromxbm($input); },
            'xpm'  => function($input) { return imagecreatefromxpm($input); },
        );
    }
    
    
    /**
     * Create an image resource from an image hosting on the internet.
     * @param string $url - the url to grab the image from.
     * @param string $extension - e.g. "jpg" or "gif". You only need to provide this if the 
     *                            image extension is NOT at the end of the url.
     * @return type
     * @throws \Exception
     */
    public static function createFromUrl(string $url, string $extension="")
    {
        if ($extension === "")
        {
            // assume the url has the extension in it.
            $parts = explode(".", $url);
            $extension = end($parts);
            
            if ($extension === FALSE)
            {
                $msg = "Failed to dynamically figure out image extension from url. " . 
                       "Please manually provide it.";
                throw new \Exception($msg);
            }
        }
        
        $loweredExtension = strtolower($extension);
        $loadingMap = self::getLoadingMap();
        $supportedExtensions = array_keys($loadingMap);
        
        if (!in_array($loweredExtension, $supportedExtensions))
        {
            throw new \Exception("Unsupported file extension: {$extension}");
        }
        
        $tmpFileName = tempnam(sys_get_temp_dir(), "");
        $newTmpFilename = $tmpFileName . '.' . $loweredExtension;
        $renamed = rename($tmpFileName, $newTmpFilename);
        
        if ($renamed === FALSE)
        {
            throw new \Exception("Failed to create temporary file for the downloaded image.");
        }
        
        $imageData = file_get_contents($url);
        file_put_contents($newTmpFilename, $imageData);
        return self::createFromFilepath($newTmpFilename);
    }
    
    
    /**
     * Save the image to a file (without compression).
     * @param string $filepath
     * @throws \Exception
     * @throws Exception
     */
    public function save(string $filepath)
    {
        $parts = explode(".", $filepath);
        $extension = end($parts);
        
        if ($extension === FALSE)
        {
            throw new \Exception("Failed to find extension of file");
        }
        
        // todo
        $map = array(
            'bmp'  => function($image, $filepath) { return imagebmp($image, $filepath, false); },
            'gif'  => function($image, $filepath) { return imagegif($image, $filepath); },
            'jpg'  => function($image, $filepath) { return imagejpeg($image, $filepath, 100); },
            'jpeg' => function($image, $filepath) { return imagejpeg($image, $filepath, 100); },
            'png'  => function($image, $filepath) { return imagepng($image, $filepath, 0); },
            'gd'   => function($image, $filepath) { return imagegd($image, $filepath); },
            'gd2'  => function($image, $filepath) { return imagegd2($image, $filepath); },
            'webp' => function($image, $filepath) { return imagewebp($image, $filepath, 100); },
            'wbmp' => function($image, $filepath) { return imagewbmp($image, $filepath); },
            'xbm'  => function($image, $filepath) { return imagexbm($image, $filepath); },
        );
            
        $loweredExtension = strtolower($extension);
        
        if (in_array($loweredExtension, array_keys($map)))
        {
            $saveMethod = $map[$loweredExtension];
            $result = $saveMethod($this->m_imageResource, $filepath);
            
            if ($result === FALSE)
            {
                throw new \Exception("Failed to save image.");
            }
        }
        else
        {
            throw new \Exception("Unsupported image extension: {$extension}");
        }
    }
    
    
    public static function createFromResource($resource)
    {
        return new Image($resource);
    }
    
    
    # Accessors
    public function getResource() { return $this->m_imageResource; }
}

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
     * Create an image resource from the specified filepath.
     * You will need this for all the other functions, so it's pretty handy.
     * @param string $filepath - the path to the file you wish to manipulate.
     * @return \Programster\ImageLib\ImageLib
     * @throws \Exception
     */
    public function __construct(string $filepath) : ImageLib
    {
        if (!file_exists($filepath))
        {
            throw new \Exception("Could not find provided file: {$filepath}");
        }
        
        $extension = end(explode(".", $filepath));
        
        if ($extension === FALSE)
        {
            throw new \Exception("Failed to find extension of file");
        }
        
        $map = array(
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
        
        $this->m_imageResource = $result;
    }
    
    
    # Accessors
    public function getResource() { return $this-m_resource; }
}

    

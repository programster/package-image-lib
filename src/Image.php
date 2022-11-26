<?php

/*
 * A wrapper around an image resource.
 * Doing this allows me to type hint the image parameters.
 */

namespace Programster\ImageLib;

class Image
{
    private \GdImage $m_imageResource;
    private string $m_type;


    /**
     * Private constructor for internal use only.
     * If you wish to create one of these objects, please use one of the static create methods.
     * Can't wait for polymorphism in PHP.
     * @param \GdImage $resource
     * @param string $type -  the type/extension of the file. E.g. "jpg", "bmp" etc.
     */
    private function __construct(\GdImage $resource, string $type)
    {
        $this->m_imageResource = $resource;
        $this->m_type = $type;
    }


    /**
     * Create an image resource from the specified filepath.
     * You will need this for all the other functions, so it's pretty handy.
     * @param string $filepath - the path to the file you wish to manipulate.
     * @param string $type - manually specify the type. E.g. gif, jpg etc.
     * @return \Programster\ImageLib\Image
     * @throws \Exception
     */
    public static function createFromFilepath(string $filepath, string $type = "") : Image
    {
        if (!file_exists($filepath))
        {
            throw new \Exception("Could not find provided file: {$filepath}");
        }

        if ($type === "")
        {
            $parts = explode(".", $filepath);
            $type = end($parts);

            if ($type === FALSE)
            {
                throw new \Exception("Failed to dynamically find type of file from extension");
            }
        }

        $map = self::getLoadingMap();
        $loweredExtension = strtolower($type);

        if (in_array($loweredExtension, array_keys($map)))
        {
            $callback = $map[$loweredExtension];
            $result = $callback($filepath);
        }
        else
        {
            throw new \Exception("Unsupported image extension: {$type}");
        }

        if ($result === FALSE)
        {
            throw new \Exception("Failed to convert extension: {$type}, into an image.");
        }

        // if we get here, $result is a resource.
        return new Image($result, $type);
    }


    /**
     * Create an image resource from an image hosting on the internet.
     * @param string $url - the url to grab the image from.
     * @param string $extension - e.g. "jpg" or "gif". You only need to provide this if the
     *                            image extension is NOT at the end of the url.
     * @return type
     * @throws \Exception
     */
    public static function createFromUrl(string $url, string $extension = "") : Image
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
     * @param type $filepath
     * @param int $quality - The quality parameter that will be used. This number varies based on file format.
     *             jpg - From 0 (worst) to 100 (best quality). A value of -1 uses the default IJG quality value (about 75).
     *             webp -  quality ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file).
     *                     if you leave as -1 default, will default to 75.
     *             png - Compression level: from 0 (no compression) to 9. The default (-1) uses the zlib compression
     *                   default. For more information see the zlib manual.
     * @throws \Exception
     */
    public function save(string $filepath, int $quality = -1) : void
    {
        $parts = explode(".", $filepath);
        $extension = end($parts);

        if ($extension === FALSE)
        {
            throw new \Exception("Failed to find extension of file");
        }

        // webp doesn't have a -1 default, so default to 100
        if ($quality === -1 && $extension === 'webp')
        {
            $quality = 75;
        }

        // todo
        $map = array(
            'bmp'  => function($image, $filepath, $quality) { return imagebmp($image, $filepath, false); },
            'gif'  => function($image, $filepath, $quality) { return imagegif($image, $filepath); },
            'jpg'  => function($image, $filepath, $quality) { return imagejpeg($image, $filepath, $quality); },
            'jpeg' => function($image, $filepath, $quality) { return imagejpeg($image, $filepath, $quality); },
            'png'  => function($image, $filepath, $quality) { return imagepng($image, $filepath, $quality); },
            'gd'   => function($image, $filepath, $quality) { return imagegd($image, $filepath); },
            'gd2'  => function($image, $filepath, $quality) { return imagegd2($image, $filepath); },
            'webp' => function($image, $filepath, $quality) { return imagewebp($image, $filepath, $quality); },
            'wbmp' => function($image, $filepath, $quality) { return imagewbmp($image, $filepath); },
            'xbm'  => function($image, $filepath, $quality) { return imagexbm($image, $filepath); },
        );

        $loweredExtension = strtolower($extension);

        if (in_array($loweredExtension, array_keys($map)))
        {
            $saveMethod = $map[$loweredExtension];
            $result = $saveMethod($this->m_imageResource, $filepath, $quality);

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


    /**
     * Save the image to a file (without compression).
     * @param string $filepath
     * @throws \Exception
     * @throws Exception
     */
    public function outputToBrowser() : void
    {
        $map = array(
            'bmp'  => function($image) { return imagebmp($image, NULL, false); },
            'gif'  => function($image) { return imagegif($image); },
            'jpg'  => function($image) { return imagejpeg($image, NULL, 100); },
            'jpeg' => function($image) { return imagejpeg($image, NULL, 100); },
            'png'  => function($image) { return imagepng($image, NULL, 0); },
            'gd'   => function($image) { return imagegd($image); },
            'gd2'  => function($image) { return imagegd2($image); },
            'webp' => function($image) { return imagewebp($image, NULL, 100); },
            'wbmp' => function($image) { return imagewbmp($image); },
            'xbm'  => function($image) { return imagexbm($image); },
        );

        header('Content-Type:' . $this->m_type); // without this, you see garble.
        $saveMethod = $map[$this->m_type];
        $result = $saveMethod($this->m_imageResource);

        if ($result === FALSE)
        {
            throw new \Exception("Failed to output image.");
        }
    }


    /**
     * Create the image from the image resource.
     * @param type $resource
     * @param string $type - the type for the image. E.g. "png", "jpg" etc.
     * @return \Programster\ImageLib\Image
     */
    public static function createFromResource(\GdImage $resource, $type) : \GdImage
    {
        return new Image($resource, $type);
    }


    /**
     * Get a map of file extensions to callbacks that can be used to load the image.
     * @return array - the map of types to the functin to run to load the image.
     */
    private static function getLoadingMap()
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


    # Accessors
    public function getResource() : \GdImage { return $this->m_imageResource; }
    public function getType() : string       { return $this->m_type; }
}
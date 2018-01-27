<?php

class ImageLib
{
    public static function getImageObject(string $filepath) : ImageLib
    {
        $extension = end(explode(".", $file));
        
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
            throw new Exception("Unsupported image extension: {$extension}");
        }
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to convert extension: {$extension}, into an image.");
        }
        
        return $result;
    }
    
    
    // crop an image.
    public static function crop($image, Rectangle $box)
    {
        $param2 = array(
            'x' => $box->getStartX(),
            'y' => $box->getStartY(),
            'width' => $box->getWidth(),
            'height' => $box->getHeight()
        );
        
        return imagecrop($image, $param2);
    }
    
    
    // Create a grid of images using this number or rows and columns.
    public static function divideImage($image, int $numColumns, int $numRows)
    {
        $images = array();
        $width = self::getWidth($image);
        $height = self::getHeight($image);
        $boxWidth = floor($width / $numColumns);
        $boxHeight = floor($height / $numRows);
        
        for ($s=0; $s<$numColumns; $s++)
        {
            for ($t=0; $t<$numRows; $t++)
            {
                $startX = $s * $boxWidth;
                $startY = $t * $boxHeight;
                
                $box = new Rectangle($startX, $startY, $boxWidth, $boxHeight);
                $images[] = self::crop($image, $box);
            }
        }
        
        return $images;
    }
    
    
    public static function getWidth($image) { return imagesx($image); }
    public static function getHeight($image) { return imagesy($image); }
    
    
    
    
    // create grid of images cut out of larger image
    public static function gridCrop($image, Dimensions $dimensions)
    {
        $width = self::getWidth($image);
        $height = self::getHeight($image);
        $numColumns = floor($width) / $dimensions->getWidth();
        $numRows = floor($height) / $dimensions->getHeight();
        $images = array();
        
        for ($s=0; $s<$numColumns; $s++)
        {
            for ($t=0; $t<$numRows; $t++)
            {
                $startX = $s * $dimensions->getWidth();
                $startY = $t * $dimensions->getHeight();
                
                $cropBox = new Rectangle($startX, $startY, $dimensions->getWidth(), $dimensions->getHeight());
                $images[] = self::crop($image, $cropBox);
            }
        }
        
        return $images;
    }
    
    /**
     * Scale an image by a percentage of its original size. E.g. 50 to make it half its size or 
     * 200 to make it twice the size.
     * @param type $image
     * @param float $percentage - a percentage. e.g. 100 for 100%.
     * @return type
     */
    public static function scaleToPercentage($image, float $percentage)
    {
        $currentWidth = self::getWidth($image);
        $decimalPercentage = $percentage / 100;
        $newWidth = $currentWidth * $decimalPercentage;
        return imagescale($image, $newWidth);
    }
    
    // shrinks an image to this height, preserving aspect ratio
    public static function scaleToWidth($image, int $width)
    {
        return imagescale($image, $width);
    }
    
    // shrinks an image to this height, preserving aspect ratio
    public static function scaleToHeight($image, int $height)
    {
        $currentHeight = self::getHeight($image);
        $decimalPercentage = $height / $currentHeight;
        $currentWidth = self::getWidth($image);
        $newWidth = $currentWidth * $decimalPercentage;
        return imagescale($image, $newWidth);
    }
    
    
    public static function scaleToDimensions($image, Dimensions $dimensions)
    {
        return imagescale($image, $dimensions->getWidth(), $dimensions->getHeight());
    }
    
    // Alias for scaleToDimensions
    public static function setDimensions($image, Dimensions $dimensions)
    {
        return self::scaleToDimensions($image, $dimensions);
    }
    
    
    // shrink an image so that it would fit in a box or specified height and width
    // whilst preserving aspect ratio
    public static function scaleToFit($image, Dimensions $box)
    {
        $currentHeight = self::getHeight($image);
        $currentWidth = self::getWidth($image);
        
        $widthPercentage = $box->getWidth() / $currentWidth;
        $heightPercentage = $box->getHeight() / $currentHeight;
        
        $percentage = min($widthPercentage, $heightPercentage);
        return self::scaleToPercentage($image, $percentage);
    }
    
    
    public static function shrinkToFit($image, Dimensions $box) 
    {
        return self::scaleToFit($image, $box);
    }
            
    
    
    // shrink an image so that the either the width or the height will be what is 
    // specified, but the other will be larger.
    // preserves aspect ratio.
    // This is similar to scale to fit, but this image will likely be bigger than the 
    // bounding box.
    public static function shrinkToMin($image, Dimensions $box)
    {
        $currentHeight = self::getHeight($image);
        $currentWidth = self::getWidth($image);
        
        $widthPercentage = $box->getWidth() / $currentWidth;
        $heightPercentage = $box->getHeight() / $currentHeight;
        
        $percentage = max($widthPercentage, $heightPercentage);
        return self::scaleToPercentage($image, $percentage);
    }
    
    
    // shrink an image so that it has as close to the specified number of pixels as possible
    // whilst preserving aspect ratio.
    public static function shrinkToNumPixels($image, int $maxPixels)
    {
        $width = self::getWidth($image);
        $height = self::getHeight($image);
        $numPixels = $width * $height;
        $percentage = $maxPixels / $numPixels;
        return self::scaleToPercentage($image, $percentage);
    }
    
    
    // extract the specified size of a rectangle out of the center of an image.
    public static function centerCrop($image, Dimensions $size)
    {
        $width = self::getWidth($image);
        $height = self::getHeight($image);
        
        $centerX = $width / 2.0;
        $centerY = $height / 2.0;
        
        $halfBoxWidth = $size->getWidth() / 2.0;
        $halfBoxHeight = $size->getHeight() / 2.0;
        
        $startX = round($centerX - $halfBoxHeight);
        $startY = round($centerY - $halfBoxWidth);
        
        $rectangle = rectangle::createFromPointAndSize(new Point($startX, $startY), $size);
        return self::crop($image, $rectangle);
    }
    
    
    // create a thumbnail of the specified size.
    // this will shrink the image before cropping/expanding to ensure we capture as much
    // of the image as possible.
    // you may widh to look at centercrop
    public static function thumbnail($image, Dimensions $size)
    {
        $shrunkImage = self::shrinkToMin($image, $size->getWidth(), $size->getHeight());
        return self::centerCrop($shrunkImage, $size);
    }
    
    
    public static function cropToAspectRatio($image,  Dimensions $ratio)
    {
        $widthFactor =  self::getWidth($image) / $ratio->getWidth();
        $heightFactor = self::getHeight($image) / $ratio->getHeight();
        
        $minFactor = min($widthFactor, $heightFactor);
        
        $dimensions = new Dimensions(
            $ratio->getWidth() * $minFactor, 
            $ratio->getHeight() * $minFactor
        );
        
        return self::centerCrop($image, $dimensions);
    }
    
    
    public static function drawRectangle(Rectangle $box, $image, Color $color, int $thickness)
    {
        foreach ($box->getLines() as $line)
        {
            self::drawLine($image, $line, $color, $thickness);
        }
    }
    
    
    public static function drawLine($image, Line $line, Color $color, int $thickness) : bool
    {
        imagesetthickness($image, $thickness);
        
        return imageline(
            $image,
            $line->getStartPoint()->getX(), 
            $line->getStartPoint()->getY(), 
            $line->getEndPoint()->getX(), 
            $line->getEndPoint()->getY(),
            imagecolorallocate($image, $color->getRed(), $color->getGreen(), $color->getBlue())
        );
    }
}
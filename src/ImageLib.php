<?php

/*
 * This is the main point of the library. Call it's static methods to achieve what you want. 
 * The other classes just help aid with calling these functions with the necessary parameters
 */


namespace Programster\ImageLib;

class ImageLib
{
    /**
     * Crop an image
     * @param resource $image - the image resource from getImageObject
     * @param \Programster\ImageLib\Rectangle $box
     * @return type
     */
    public static function crop(Image $image, Rectangle $box)
    {
        $param2 = array(
            'x' => $box->getStartX(),
            'y' => $box->getStartY(),
            'width' => $box->getWidth(),
            'height' => $box->getHeight()
        );
        
        return imagecrop($image->getResource(), $param2);
    }
    
    
    // Create a grid of images using this number or rows and columns.
    public static function divideImage(Image $image, int $numColumns, int $numRows)
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
    
    
    public static function getWidth(Image $image) { return imagesx($image->getResource()); }
    public static function getHeight(Image $image) { return imagesy($image->getResource()); }
    
    
    
    
    // create grid of images cut out of larger image
    public static function gridCrop(Image $image, Dimensions $dimensions)
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
    public static function scaleToPercentage(Image $image, float $percentage)
    {
        $currentWidth = self::getWidth($image);
        $decimalPercentage = $percentage / 100;
        $newWidth = $currentWidth * $decimalPercentage;
        return imagescale($image->getResource(), $newWidth);
    }
    
    // shrinks an image to this height, preserving aspect ratio
    public static function scaleToWidth(Image $image, int $width)
    {
        return imagescale($image->getResource(), $width);
    }
    
    // shrinks an image to this height, preserving aspect ratio
    public static function scaleToHeight(Image $image, int $height)
    {
        $currentHeight = self::getHeight($image);
        $decimalPercentage = $height / $currentHeight;
        $currentWidth = self::getWidth($image);
        $newWidth = $currentWidth * $decimalPercentage;
        return imagescale($image->getResource(), $newWidth);
    }
    
    
    public static function scaleToDimensions(Image $image, Dimensions $dimensions)
    {
        return imagescale($image->getResource(), $dimensions->getWidth(), $dimensions->getHeight());
    }
    
    // Alias for scaleToDimensions
    public static function setDimensions(Image $image, Dimensions $dimensions)
    {
        return self::scaleToDimensions($image, $dimensions);
    }
    
    
    // shrink an image so that it would fit in a box or specified height and width
    // whilst preserving aspect ratio
    public static function scaleToFit(Image $image, Dimensions $box)
    {
        $currentHeight = self::getHeight($image);
        $currentWidth = self::getWidth($image);
        
        $widthPercentage = $box->getWidth() / $currentWidth;
        $heightPercentage = $box->getHeight() / $currentHeight;
        
        $percentage = min($widthPercentage, $heightPercentage);
        return self::scaleToPercentage($image, $percentage);
    }
    
    
    public static function shrinkToFit(Image $image, Dimensions $box) 
    {
        return self::scaleToFit($image, $box);
    }
            
    
    
    // shrink an image so that the either the width or the height will be what is 
    // specified, but the other will be larger.
    // preserves aspect ratio.
    // This is similar to scale to fit, but this image will likely be bigger than the 
    // bounding box.
    public static function shrinkToMin(Image $image, Dimensions $box)
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
    public static function shrinkToNumPixels(Image $image, int $maxPixels)
    {
        $width = self::getWidth($image);
        $height = self::getHeight($image);
        $numPixels = $width * $height;
        $percentage = $maxPixels / $numPixels;
        return self::scaleToPercentage($image, $percentage);
    }
    
    
    // extract the specified size of a rectangle out of the center of an image.
    public static function centerCrop(Image $image, Dimensions $size)
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
    public static function thumbnail(Image $image, Dimensions $size)
    {
        $shrunkImage = self::shrinkToMin($image, $size->getWidth(), $size->getHeight());
        return self::centerCrop($shrunkImage, $size);
    }
    
    
    public static function cropToAspectRatio(Image $image,  Dimensions $ratio)
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
    
    
    public static function drawRectangle(Image $image, Rectangle $box, Color $color, int $thickness)
    {
        foreach ($box->getLines() as $line)
        {
            self::drawLine($image, $line, $color, $thickness);
        }
    }
    
    
    public static function drawLine(Image $image, Line $line, Color $color, int $thickness) : bool
    {
        imagesetthickness($image, $thickness);
        
        return imageline(
            $image,
            $line->getStartPoint()->getX(), 
            $line->getStartPoint()->getY(), 
            $line->getEndPoint()->getX(), 
            $line->getEndPoint()->getY(),
            imagecolorallocate($image->getResource(), $color->getRed(), $color->getGreen(), $color->getBlue())
        );
    }
}
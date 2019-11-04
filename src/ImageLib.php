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
     * @return Image - the resulting image object.
     */
    public static function crop(Image $image, Rectangle $box)
    {
        $param2 = array(
            'x' => $box->getStartX(),
            'y' => $box->getStartY(),
            'width' => $box->getWidth(),
            'height' => $box->getHeight()
        );

        $resource = imagecrop($image->getResource(), $param2);

        if ($resource === FALSE)
        {
            $msg = "Failed to crop image using point: {$box->getStartX()}, $box->getStartY() " .
                   "width: {$box->getWidth()} and height: {$box->getHeight()}";
            throw new \Exception($msg);
        }

        return Image::createFromResource($resource, $image->getType());
    }


    /**
     * Create a grid of images using this number or rows and columns.
     * @param \Programster\ImageLib\Image $image
     * @param int $numColumns
     * @param int $numRows
     * @return array - the resulting "grid" of images.
     */
    public static function divideImage(Image $image, $numColumns, $numRows)
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


    /**
     * GEt the width of the image
     * @param \Programster\ImageLib\Image $image
     * @return int - the number of pixels wide.
     */
    public static function getWidth(Image $image)
    {
        return imagesx($image->getResource());
    }


    /**
     * Get the height of the image.
     * @param \Programster\ImageLib\Image $image
     * @return int - the number of pixels tall
     */
    public static function getHeight(Image $image)
    {
        return imagesy($image->getResource());
    }


    /**
     * Get the number of pixels the image has (width x height)
     * @param \Programster\ImageLib\Image $image
     * @return int - the number of pixels in the image.
     */
    public static function getNumPixels(Image $image)
    {
        return ImageLib::getHeight($image) * ImageLib::getWidth($image);
    }


    /**
     * Create grid of images cut out of larger image
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $dimensions
     * @return array - the array of images.
     */
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
     * @param \Programster\ImageLib\Image $image
     * @param float $percentage - a percentage. e.g. 100 for 100%.
     * @return \Programster\ImageLib\Image - the resulting image.
     */
    public static function scaleToPercentage(Image $image, $percentage)
    {
        $currentWidth = self::getWidth($image);
        $decimalPercentage = $percentage / 100.0;
        $newWidth = $currentWidth * $decimalPercentage;
        $newResource = imagescale($image->getResource(), $newWidth);
        return Image::createFromResource($newResource, $image->getType());
    }


    /**
     * Shrinks an image to this height, preserving aspect ratio
     * @param \Programster\ImageLib\Image $image
     * @param int $width
     * @return \Programster\ImageLib\Image
     */
    public static function scaleToWidth(Image $image, $width)
    {
        return imagescale($image->getResource(), $width);
    }


    /**
     * Shrinks an image to this height, preserving aspect ratio
     * @param \Programster\ImageLib\Image $image
     * @param int $height
     * @return \Programster\ImageLib\Image
     */
    public static function scaleToHeight(Image $image, $height)
    {
        $currentHeight = self::getHeight($image);
        $decimalPercentage = $height / $currentHeight;
        $currentWidth = self::getWidth($image);
        $newWidth = $currentWidth * $decimalPercentage;
        return imagescale($image->getResource(), $newWidth);
    }


    /**
     * Scale an image to the provided dimensions.
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $dimensions
     * @return \Programster\ImageLib\Image
     */
    public static function scaleToDimensions(Image $image, Dimensions $dimensions)
    {
        $resource = imagescale(
            $image->getResource(),
            $dimensions->getWidth(),
            $dimensions->getHeight()
        );

        return Image::createFromResource($resource, $image->getType());
    }


    /**
     * Alias for scaleToDimensions
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $dimensions
     * @return \Programster\ImageLib\Image - the resulting image
     */
    public static function setDimensions(Image $image, Dimensions $dimensions)
    {
        return self::scaleToDimensions($image, $dimensions);
    }


    /**
     * Alias for scaleToDimensions
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $dimensions
     * @return \Programster\ImageLib\Image - the resulting image.
     */
    public function setSize(Image $image, Dimensions $dimensions)
    {
        return self::scaleToDimensions($image, $dimensions);
    }


    /**
     * shrink an image so that it would fit in a box or specified height and width
     * whilst preserving aspect ratio
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $box
     * @return \Programster\ImageLib\Image
     */
    public static function scaleToFit(Image $image, Dimensions $box)
    {
        $currentHeight = self::getHeight($image);
        $currentWidth = self::getWidth($image);

        $widthPercentage = $box->getWidth() / $currentWidth;
        $heightPercentage = $box->getHeight() / $currentHeight;

        $percentage = min($widthPercentage, $heightPercentage) * 100;
        return self::scaleToPercentage($image, $percentage);
    }


    /**
     *
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $box
     * @return \Programster\ImageLib\Image
     */
    public static function shrinkToFit(Image $image, Dimensions $box)
    {
        return self::scaleToFit($image, $box);
    }


    /**
     * shrink an image so that the either the width or the height will be what is
     * specified, but the other will be larger.
     * preserves aspect ratio.
     * This is similar to scale to fit, but this image will likely be bigger than the
     * bounding box.
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $box
     * @return \Programster\ImageLib\Image
     */
    public static function shrinkToMin(Image $image, Dimensions $box)
    {
        $currentHeight = self::getHeight($image);
        $currentWidth = self::getWidth($image);

        $widthPercentage = $box->getWidth() / $currentWidth;
        $heightPercentage = $box->getHeight() / $currentHeight;

        $percentage = max($widthPercentage, $heightPercentage) * 100;
        return self::scaleToPercentage($image, $percentage);
    }


    /**
     * Alias for scaleToNumPixels
     * @param \Programster\ImageLib\Image $image
     * @param int $maxPixels
     * @return \Programster\ImageLib\Image
     */
    public static function shrinkToNumPixels(Image $image, $maxPixels)
    {
        return self::scaleToNumPixels($image, $maxPixels);
    }


    /**
     * Scale an image so it has as close to the number of pixels specified, as possible.
     * @param \Programster\ImageLib\Image $image
     * @param int $desiredNumPixels - the number of desired pixels. 1920 x 1080 is 2,073,600
     * @return \Programster\ImageLib\Image
     */
    public static function scaleToNumPixels(Image $image, $desiredNumPixels)
    {
        $width = self::getWidth($image);
        $height = self::getHeight($image);
        $currentNumPixels = $width * $height;
        $percentage = ($desiredNumPixels / $currentNumPixels) * 100;
        return self::scaleToPercentage($image, $percentage);
    }


    /**
     * Extract the specified size of a rectangle out of the center of an image.
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $size
     * @return \Programster\ImageLib\Image
     */
    public static function centerCrop(Image $image, Dimensions $size)
    {
        $width = self::getWidth($image);
        $height = self::getHeight($image);

        $centerX = $width / 2.0;
        $centerY = $height / 2.0;

        $halfBoxWidth = $size->getWidth() / 2.0;
        $halfBoxHeight = $size->getHeight() / 2.0;

        $startX = round($centerX - $halfBoxWidth);
        $startY = round($centerY - $halfBoxHeight);

        $rectangle = rectangle::createFromPointAndSize(new Point($startX, $startY), $size);
        return self::crop($image, $rectangle);
    }


    /**
     * Create a thumbnail of the specified size.
     * This will shrink the image before cropping/expanding to ensure we capture as much
     * f the image as possible. You may wish to look at centercrop
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $size
     * @return \Programster\ImageLib\Image
     */
    public static function thumbnail(Image $image, Dimensions $size)
    {
        $shrunkImage = self::shrinkToMin($image, $size);
        return self::centerCrop($shrunkImage, $size);
    }


    /**
     * Crop an image so that it will have the provided aspect ratio, preserving as much of the image as possible.
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Dimensions $ratio
     * @return \Programster\ImageLib\Image
     */
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


    /**
     * Draw a rectangle over an image.
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Rectangle $box
     * @param \Programster\ImageLib\Color $color
     * @param int $thickness
     * @return \Programster\ImageLib\Image
     */
    public static function drawRectangle(Image $image, Rectangle $box, Color $color, $thickness)
    {
        foreach ($box->getLines() as $line)
        {
            self::drawLine($image, $line, $color, $thickness);
        }

        return $image;
    }


    /**
     * Draw a line over an image.
     * @param \Programster\ImageLib\Image $image
     * @param \Programster\ImageLib\Line $line
     * @param \Programster\ImageLib\Color $color
     * @param int $thickness
     * @return bool - whether imageline operation was successful.
     */
    public static function drawLine(Image $image, Line $line, Color $color, $thickness)
    {
        imagesetthickness($image->getResource(), $thickness);

        return imageline(
            $image->getResource(),
            $line->getStartPoint()->getX(),
            $line->getStartPoint()->getY(),
            $line->getEndPoint()->getX(),
            $line->getEndPoint()->getY(),
            imagecolorallocate($image->getResource(), $color->getRed(), $color->getGreen(), $color->getBlue())
        );
    }
}
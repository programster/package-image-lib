<?php

/*
 * This is the main point of the library. Call its static methods to achieve what you want.
 * The other classes just help aid with calling these functions with the necessary parameters
 */


namespace Programster\ImageLib;

use Exception;

class ImageLib
{
    /**
     * Crop an image
     * @param Image $image - the image resource from getImageObject
     * @param Rectangle $box - The bounding box of the crop.
     * @return Image - the resulting image object.
     * @throws Exception
     */
    public static function crop(Image $image, Rectangle $box) : Image
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
            $msg = "Failed to crop image using point: {$box->getStartX()}, {$box->getStartY()} " .
                   "width: {$box->getWidth()} and height: {$box->getHeight()}";
            throw new Exception($msg);
        }

        return Image::createFromResource($resource, $image->getType());
    }


    /**
     * Create a grid of images using this number of rows and columns.
     * @param Image $image $image
     * @param int $numColumns - the number of columns in the grid
     * @param int $numRows - the number of rows in the grid
     * @return array - the resulting "grid" of images.
     */
    public static function divideImage(Image $image, int $numColumns, int $numRows) : array
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
     * Get the width of the image
     * @param Image $image
     * @return int - the number of pixels wide.
     */
    public static function getWidth(Image $image) : int
    {
        return imagesx($image->getResource());
    }


    /**
     * Get the height of the image.
     * @param Image $image
     * @return int - the number of pixels tall
     */
    public static function getHeight(Image $image) : int
    {
        return imagesy($image->getResource());
    }


    /**
     * Get the number of pixels the image has (width x height)
     * @param Image $image
     * @return int - the number of pixels in the image.
     */
    public static function getNumPixels(Image $image) : int
    {
        return ImageLib::getHeight($image) * ImageLib::getWidth($image);
    }


    /**
     * Create grid of images cut out of larger image
     * @param Image $image
     * @param Dimensions $dimensions
     * @return array - the array of images.
     */
    public static function gridCrop(Image $image, Dimensions $dimensions) : array
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
     * @param Image $image
     * @param float $percentage - a percentage. e.g. 100 for 100%.
     * @return Image - the resulting image.
     */
    public static function scaleToPercentage(Image $image, float $percentage) : Image
    {
        $currentWidth = self::getWidth($image);
        $decimalPercentage = $percentage / 100.0;
        $newWidth = intval($currentWidth * $decimalPercentage);
        $newResource = imagescale($image->getResource(), $newWidth);

        if ($newResource === false)
        {
            throw new \Exception("Failed to scale image to new width.");
        }

        return Image::createFromResource($newResource, $image->getType());
    }


    /**
     * Shrinks an image to this height, preserving aspect ratio
     * @param Image $image
     * @param int $width
     * @return Image
     */
    public static function scaleToWidth(Image $image, int $width) : Image
    {
        $newImage = imagescale($image->getResource(), $width);

        if ($newImage === false)
        {
            throw new \Exception("Failed to scale image to new width.");
        }

        return Image::createFromResource($newImage, $image->getType());
    }


    /**
     * Shrinks an image to this height, preserving aspect ratio
     * @param Image $image
     * @param int $height
     * @return Image
     */
    public static function scaleToHeight(Image $image, $height) : Image
    {
        $currentHeight = self::getHeight($image);
        $decimalPercentage = $height / $currentHeight;
        $currentWidth = self::getWidth($image);
        $newWidth = $currentWidth * $decimalPercentage;
        $rescaledImage = imagescale($image->getResource(), $newWidth);

        if ($rescaledImage === false)
        {
            throw new \Exception("Failed to scale image to new width");
        }

        return Image::createFromResource($rescaledImage, $image->getType());
    }


    /**
     * Scale an image to the provided dimensions.
     * @param Image $image
     * @param Dimensions $dimensions
     * @return Image
     */
    public static function scaleToDimensions(Image $image, Dimensions $dimensions) : Image
    {
        $resource = imagescale(
            $image->getResource(),
            $dimensions->getWidth(),
            $dimensions->getHeight()
        );

        if ($resource === false)
        {
            throw new \Exception("Failed to scale image to new width");
        }

        return Image::createFromResource($resource, $image->getType());
    }


    /**
     * Alias for scaleToDimensions
     * @param Image $image
     * @param Dimensions $dimensions
     * @return Image - the resulting image
     */
    public static function setDimensions(Image $image, Dimensions $dimensions) : Image
    {
        return self::scaleToDimensions($image, $dimensions);
    }


    /**
     * Alias for scaleToDimensions
     * @param Image $image
     * @param Dimensions $dimensions
     * @return Image - the resulting image.
     */
    public function setSize(Image $image, Dimensions $dimensions) : Image
    {
        return self::scaleToDimensions($image, $dimensions);
    }


    /**
     * shrink an image so that it would fit in a box or specified height and width whilst preserving aspect ratio
     * @param Image $image
     * @param Dimensions $box
     * @return Image
     */
    public static function scaleToFit(Image $image, Dimensions $box) : Image
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
     * @param Image $image
     * @param Dimensions $box
     * @return Image
     */
    public static function shrinkToFit(Image $image, Dimensions $box) : Image
    {
        return self::scaleToFit($image, $box);
    }


    /**
     * Shrink an image so that either the width or the height will be what is specified, but the other will be larger.
     * Preserves aspect ratio.
     * This is similar to scale to fit, but this image will likely be bigger than the bounding box.
     * @param Image $image
     * @param Dimensions $box
     * @return Image
     */
    public static function shrinkToMin(Image $image, Dimensions $box) : Image
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
     * @param Image $image
     * @param int $maxPixels
     * @return Image
     */
    public static function shrinkToNumPixels(Image $image, $maxPixels) : Image
    {
        return self::scaleToNumPixels($image, $maxPixels);
    }


    /**
     * Scale an image, so that it has as close to the number of pixels specified, as possible.
     * @param Image $image
     * @param int $desiredNumPixels - the number of desired pixels. 1920 x 1080 is 2,073,600
     * @return Image
     */
    public static function scaleToNumPixels(Image $image, $desiredNumPixels) : Image
    {
        $width = self::getWidth($image);
        $height = self::getHeight($image);
        $currentNumPixels = $width * $height;
        $percentage = ($desiredNumPixels / $currentNumPixels) * 100;
        return self::scaleToPercentage($image, $percentage);
    }


    /**
     * Extract the specified size of a rectangle out of the center of an image.
     * @param Image $image
     * @param Dimensions $size
     * @return Image
     */
    public static function centerCrop(Image $image, Dimensions $size) : Image
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
     * @param Image $image
     * @param Dimensions $size
     * @return Image
     */
    public static function thumbnail(Image $image, Dimensions $size) : Image
    {
        $shrunkImage = self::shrinkToMin($image, $size);
        return self::centerCrop($shrunkImage, $size);
    }


    /**
     * Crop an image so that it will have the provided aspect ratio, preserving as much of the image as possible.
     * @param Image $image
     * @param Dimensions $ratio
     * @return Image
     */
    public static function cropToAspectRatio(Image $image,  Dimensions $ratio) : Image
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
     * @param Image $image
     * @param Rectangle $box
     * @param Color $color
     * @param int $thickness
     * @return Image
     */
    public static function drawRectangle(Image $image, Rectangle $box, Color $color, int $thickness) : Image
    {
        foreach ($box->getLines() as $line)
        {
            self::drawLine($image, $line, $color, $thickness);
        }

        return $image;
    }


    /**
     * Draw a line over an image.
     * @param Image $image
     * @param \Programster\ImageLib\Line $line
     * @param Color $color
     * @param int $thickness
     * @return bool - whether imageline operation was successful.
     */
    public static function drawLine(Image $image, Line $line, Color $color, int $thickness) : bool
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


    /**
     * Blurs out the specified rectangle in an image. The easiest way to do this is to use imageconvolution()
     * https://stackoverflow.com/questions/20114956/php-gd-blur-part-of-an-image
     * @param Image $sourceImage
     * @param Rectangle $rectangle
     * @param int $strength - how strong the blurring should be. This is actually the number of iterations we perform the
     * blur, so the number needs to be between 1 and infinite.
     * @return Image
     */
    public static function blurArea(Image $sourceImage, Rectangle $rectangle, int $strength = 1) : Image
    {
        if ($strength < 1)
        {
            $strength = 1;
        }

        $image = $sourceImage->getResource();

        $gaussian = array(
            array(1.0, 2.0, 1.0),
            array(2.0, 4.0, 2.0),
            array(1.0, 2.0, 1.0)
        );

        // make a canvas for a second image...
        $img2 = imagecreatetruecolor($rectangle->getWidth(), $rectangle->getHeight()); // create img2 for selection

        // create the second image from a selection of the source image.
        imagecopy(
            $img2,
            $image,
            0,
            0,
            $rectangle->getStartX(),
            $rectangle->getStartY(),
            $rectangle->getWidth(),
            $rectangle->getHeight()
        );

        for ($i=0; $i<$strength; $i++)
        {
            // apply the blur to the selection image
            imageconvolution($img2, $gaussian, 16, 0); // apply convolution
        }

        // merge the blurred image back onto the original source
        imagecopymerge(
            $image,
            $img2,
            $rectangle->getStartX(),
            $rectangle->getStartY(),
            0,
            0,
            $rectangle->getWidth(),
            $rectangle->getHeight(),
            100
        );

        imagedestroy($img2);

        return Image::createFromResource($image, $sourceImage->getType());
    }
}
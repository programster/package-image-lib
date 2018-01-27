# ImageLib Package
This library aims to make it much easier to manipulate images by providing wrapping around the
[GD and Image Functions](http://php.net/manual/en/ref.image.php) with an object oriented approach.

Wherever possible, methods alter and return a copy of the image passed in,
rather than editing the original. However this probably needs further testing/work.


## Example Usage

```
<?php

# Include the ImageLib library
require_once(__DIR__ . '/vendor/autoload.php');

# Load an image for manipulation
$imageFilepath = __DIR__ . '/input.png';
$image = \Programster\ImageLib\Image::createFromFilepath($imageFilepath);



# Crop an image
$rectangle = new Programster\ImageLib\Rectangle(100, 100, 400, 200);    
$alteredImage = Programster\ImageLib\ImageLib::crop($image, $rectangle);

# Crop a 400 x 300 image from the center of the image.
$dimensions = new Programster\ImageLib\Dimensions(400, 300);
$alteredImage = Programster\ImageLib\ImageLib::centerCrop($image, $dimensions);

# Crop an image to get it to a certain aspect ratio, not caring about resolution/size
$aspectRatio = new \Programster\ImageLib\Dimensions(4, 3);
$alteredImage = Programster\ImageLib\ImageLib::cropToAspectRatio($image, $aspectRatio);

# Create a 400 x 300 thumbnail of an image.
# This will shrink and crop as necessary in order to preserve as much of what
# the image shows as possible but will perform cropping instead of stretching
# in order to achieve the thumbnails aspect ratio.
$dimensions = new Programster\ImageLib\Dimensions(400, 300);
$alteredImage = Programster\ImageLib\ImageLib::thumbnail($image, $dimensions);

# Scale an image so that its size is as close to the number of pixels as possible
# This is good if you are taking user uploads and targeting a certain file size.
# E.g. you don't need 4k photos on a dataing app.
# However, this may end up expanding the image if the input is smaller.
$desiredNumPixels = 2000000; // two megapixel
$alteredImage = Programster\ImageLib\ImageLib::scaleToNumPixels($image, $desiredNumPixels);

# Change an image to have the specified dimensions. This will stretch/distort
# the image if necessary to achieve the aspect ratio
$dimensions = new Programster\ImageLib\Dimensions(200, 400);
$alteredImage = Programster\ImageLib\ImageLib::setDimensions($image, $dimensions);

# Shrink the image so that it would fit into a box specified by the dimensions.
# This will preserve aspect ratio and will not distort the image, so the
# resulting image may not fill the dimensions, unlike thumbnail()
$dimensions = new Programster\ImageLib\Dimensions(200, 400);
$alteredImage = Programster\ImageLib\ImageLib::shrinkToFit($image, $dimensions);


# Draw a red rectangle
$red = new Programster\ImageLib\Color(255, 0, 0);
$rectangle = new Programster\ImageLib\Rectangle(100, 100, 400, 200);    

$alteredImage = Programster\ImageLib\ImageLib::drawRectangle(
    $image,
    $rectangle,
    $red,
    $thickness=5
);


# After performing your actions, don't forget to save the edited copy to a file!
$alteredImage->save(__DIR__ . '/output.png');

```

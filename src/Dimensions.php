<?php

/* 
 * A way to store the width and height of something. If you combine this with a coordinate system
 * (aka a point), you can upgrade to a Rectangle.
 */

namespace Programster\ImageLib;

class Dimensions
{
    private $m_width;
    private $m_height;
    
    public function __construct($width, $height)
    {
        $this->m_width = $width;
        $this->m_height = $height;
    }
    
    
    # Accessors
    public function getWidth() { return $this->m_width; }
    public function getHeight() { return $this->m_height; }
}


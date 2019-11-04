<?php

/*
 * An object to represent a point in a picture, hence it only allows integers rather than floats.
 */

namespace Programster\ImageLib;

class Point
{
    private $m_x;
    private $m_y;


    /**
     * Create a Point object.
     * @param int $x - the x coordinate in number of pixels
     * @param int $y - the y coordinate in number of pixels.
     */
    public function __construct($x, $y)
    {
        $this->m_x = $x;
        $this->m_y = $y;
    }


    # Accessors
    public function getX() { return $this->m_x; }
    public function getY() { return $this->m_y; }
}

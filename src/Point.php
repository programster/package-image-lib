<?php

/* 
 * An object to represent a point in a picture, hence it only allows integers rather than floats.
 */

namespace Programster\ImageLib;

class Point
{
    private $m_x;
    private $m_y;
    
    public function __construct(int $x, int $y)
    {
        $this->m_x = $x;
        $this->m_y = $y;
    }
    
    # Accessors
    public function getX() { return $this->m_x; }
    public function getY() { return $this->m_y; }
}


<?php

/* 
 * An object to represent a line we may wish to draw on an image.
 */

namespace Programster\ImageLib;

class Line
{
    private $m_startPoint;
    private $m_endPoint;
    
    
    public function __construct(Point $start, Point $end)
    {
        $this->m_startPoint = $start;
        $this->m_endPoint = $end;
    }
    
    
    public function getStartPoint() { return $this->m_startPoint; }
    public function getEndPoint() { return $this->m_endPoint; }
}

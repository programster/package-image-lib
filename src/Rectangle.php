<?php

/*
 * A rectangle that can be used for operations such as cropping an image, or drawing.
 */

namespace Programster\ImageLib;

class Rectangle
{
    private $m_startX;
    private $m_startY;
    private $m_width;
    private $m_height;
    
    
    /**
     * Create a rectangle.
     * WARNING - when performing operations like drawing and cropping, a Y value of 0 is at the 
     *           top of the image, and the higher it gets, the further down the image it is.
     * @param int $startX
     * @param int $startY
     * @param int $width
     * @param int $height
     */
    public function __construct(int $startX, int $startY, int $width, int $height)
    {
        $this->m_startX = $startX;
        $this->m_startY = $startY;
        $this->m_width = $width;
        $this->m_height = $height;
    }
    
    
    /**
     * Create a rectangle from the top left point specified and the specified dimensions
     * @param Point $point
     * @param Dimensions $size
     * @return \Rectangle
     */
    public static function createFromPointAndSize(Point $point, Dimensions $size)
    {
        return new Rectangle($point->getX(), $point->getY(), $size->getWidth(), $size->getHeight());
    }
    
    
    
    /**
     * Create a rectangle that has the specified dimensions and is centred on the specified point.
     * This is good for drawing a box around something you know the locatio of.
     * @param Point $point
     * @param Dimensions $size
     * @return \Rectangle
     */
    public static function createFromCenterPointAndSize(Point $point, Dimensions $size)
    {
        $halfWidth = $size->getWidth() / 2;
        $halfHeight = $size->getHeight() / 2;
        
        $topLeftPoint = new Point($point->getX() - $halfWidth, $point->getY() - $halfHeight);
        
        return new Rectangle($topLeftPoint, $size->getWidth(), $size->getHeight());
    }
    
    
    public static function createFromPoints(Point $point1, Point $point2)
    {
        $x1 = min($point1->getX(), $point2->getX());
        $x2 = max($point1->getX(), $point2->getX());
        $y1 = min($point1->getY(), $point2->getY());
        $y2 = max($point1->getY(), $point2->getY());
        
        $width = $x2 - $x1;
        $height = $y2 - $y1;
        
        return new Rectangle($x1, $y1, $width, $height);
    }
    
    
    public function getDimensions() : Dimensions
    {
        return new Dimensions($this->getWidth(), $this->getHeight());
    }
    
    
    public function getLines()
    {
        $x1 = $this->getStartX();
        $x2 = $this->getStartX() + $this->getWidth();
        $y1 = $this->getStartY();
        $y2 = $this->getStartY() + $this->getHeight();
        
        $lines = array(
            new Line(new Point($x1, $y1), new Point($x2, $y1)),
            new Line(new Point($x1, $y1), new Point($x1, $y2)),
            new Line(new Point($x1, $y2), new Point($x2, $y2)),
            new Line(new Point($x2, $y1), new Point($x2, $y2)),
        );
        
        return $lines;
    }
    
    
    # Accessors
    public function getStartX() { return $this->m_startX; }
    public function getStartY() { return $this->m_startY; }
    public function getWidth() { return $this->m_width; }
    public function getHeight() { return $this->m_height; }
}
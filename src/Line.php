<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

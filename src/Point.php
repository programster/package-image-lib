<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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


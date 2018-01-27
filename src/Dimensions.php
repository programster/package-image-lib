<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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


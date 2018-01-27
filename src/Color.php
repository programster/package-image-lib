<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Color
{
    private $m_red;
    private $m_green;
    private $m_blue;
    
    
    public function __construct(int $red, int $green, int $blue)
    {
        if ($red > 255 || $red < 0)
        {
            throw new \Exception("Red needs to be a value between 0 and 255.");
        }
        
        if ($green > 255 || $green < 0)
        {
            throw new \Exception("Green needs to be a value between 0 and 255.");
        }
        
        if ($blue > 255 || $blue < 0)
        {
            throw new \Exception("Blue needs to be a value between 0 and 255.");
        }
        
        $this->m_red = $red;
        $this->m_green = $green;
        $this->m_blue = $blue;
    }
    
    
    # Accessors
    public function getRed() { return $this->m_red; }
    public function getBlue() { return $this->m_blue; }
    public function getGreen() { return $this->m_green; }
}
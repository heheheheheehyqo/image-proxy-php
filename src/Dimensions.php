<?php

namespace Hyqo\ImageProxy;

class Dimensions
{
    public $width;
    public $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }
}

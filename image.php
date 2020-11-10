<?php

class Image
{
    private $file;
    private $image;
    private $width;
    private $height;
    private $bits;
    private $mime;

    public function __construct($file)
    {
        if (is_file($file)) {
            $this->file = $file;
            $this->image = new Imagick($file);
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
            $this->bits = $this->image->getImageLength();
            $this->mime = $this->image->getFormat();
        } else {
            exit('Error: Could not load image ' . $file . '!');
        }
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getBits()
    {
        return $this->bits;
    }

    public function getMime()
    {
        return $this->mime;
    }

    public function save($file, $quality = 90)
    {
        $this->image->setCompressionQuality($quality);
        $this->image->setImageCompressionQuality($quality);
        $this->image->writeImage($file);
    }

    public function resize($width = 0, $height = 0, $default = '')
    {
        if (!$this->width || !$this->height) {
            return;
        }
        $this->image->thumbnailImage($width, $height, true, true);
        $this->width = $width;
        $this->height = $height;
    }

    public function watermark($watermark, $position = 'bottomright')
    {
        $watermark_pos_x = 0;
        $watermark_pos_y = 0;
        switch ($position) {
            case 'topleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = 0;
                break;
            case 'topright':
                $watermark_pos_x = $this->width - $watermark->getWidth();
                $watermark_pos_y = 0;
                break;
            case 'bottomleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = $this->height - $watermark->getHeight();
                break;
            case 'bottomright':
                $watermark_pos_x = $this->width - $watermark->getWidth();
                $watermark_pos_y = $this->height - $watermark->getHeight();
                break;
            case 'middle':
                $watermark_pos_x = ($this->width - $watermark->getWidth()) / 2;
                $watermark_pos_y = ($this->height - $watermark->getHeight()) / 2;
        }

        $this->image->compositeImage($watermark, imagick::COMPOSITE_OVER, $watermark_pos_x, $watermark_pos_y);
    }

    public function crop($top_x, $top_y, $bottom_x, $bottom_y)
    {
        $this->width = $bottom_x - $top_x;
        $this->height = $bottom_y - $top_y;
        $this->image->cropImage($top_x, $top_y, $bottom_x, $bottom_y);
    }

    public function rotate($degree, $color = '#FFFFFF')
    {
        $rgb = $this->html2rgb($color);
        $this->image->rotateImage(new ImagickPixel($rgb), $degree);
    }

    private function html2rgb($color)
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }
        if (strlen($color) == 6) {
            list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
        } elseif (strlen($color) == 3) {
            list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array($r, $g, $b);
    }

    function __destruct()
    {
    }

    private function filter($filter)
    {
        imagefilter($this->image, $filter);
    }

    private function text($text, $x = 0, $y = 0, $size = 5, $color = '000000')
    {
        $draw = new ImagickDraw();
        $draw->setFontSize($size);
        $draw->setFillColor(new ImagickPixel($this->html2rgb($color)));
        $this->image->annotateImage($draw, $x, $y, 0, $text);
    }

    private function merge($merge, $x = 0, $y = 0, $opacity = 100)
    {
        $merge->getImage->setImageOpacity($opacity / 100);
        $this->image->compositeImage($merge, imagick::COMPOSITE_ADD, $x, $y);
    }
}

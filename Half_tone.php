<?php

/*
 *
 * created:
 *      2016-02-23
 *      Miroslav Bodis
 *
 * dependencies:
 *      php-pecl-imagick.x86_64
 *
 * sources:
 *      http://php.net/manual/en/book.imagick.php
 *      http://php.net/manual/en/imagick.examples-1.php
 *      http://phpimagick.com/Imagick/getPixelIterator
 *
 */

class Half_tone {

    /*
     * constants
     */
    const PRINTER_WIDTH = 384;
    const HT_IMG_WIDTH = self::PRINTER_WIDTH * 2;
    const DEBUG = false;

    /*
     * variables
     */
    var $BLOCK_SIZE = 3; // result of testing
    var $R_CONST = -1; //-1.1 // result of testing
    var $image;

    var $circle_helper;
    var $square_helper;

    function __construct($file_path)
    {
        if (!file_exists($file_path)){
            die($file_path . " file not exists \n");
        }

        $this->image = new Imagick($file_path);
        $this->set_block_size($this->BLOCK_SIZE);

        $this->circle_helper = new ImagickDraw();
        $this->square_helper = new ImagickDraw();
    }

    /*
     * setup block size:
     * keep in mind that block has to be odd number
     * so circle can be drawn in center
     */
    public function set_block_size($new_block_size)
    {
        if ($new_block_size % 2 == 1){
            $this->BLOCK_SIZE = $new_block_size;
        }else{
            echo 'set_block_size: new block size must be odd number';
            die();
        }
    }

    /*
     * [int] return center of block rounded to upper value
     */
    private function get_block_center(){
        return ceil($this->BLOCK_SIZE / 2);
    }

    public function set_ratio_const($new_ratio_const){
        $this->R_CONST = $new_ratio_const;
    }

    /*
     * - set output format jpg format
     * - convert image to grayscale
     * - transform to halftone image
     * - save result to output file
     */
    public function create_half_tone_image()
    {
        $this->image->setImageFormat('jpg');
        $this->image->setImageColorSpace(Imagick::COLORSPACE_GRAY);
        $this->resize_img_to_printer_width();

        $this->create_half_tone_effect();
    }

    public function save_to_file($img_out){
        $this->image->writeImages($img_out, true);
    }

    public function print_to_output(){
        ob_clean();
        header("Content-Type: image/jpg");
        echo $this->image->getImageBlob();
    }

    /*
     * - loop image for BLOCK_SIZE regions
     * - calculate sum of pixels in each block
     * - draw representation (black circle on white square)
     * over each region of ratio black/white
     */
    private function create_half_tone_effect()
    {
        $help_canvas = new Imagick();
        $help_canvas->newImage($this->BLOCK_SIZE, $this->BLOCK_SIZE, new ImagickPixel());
        $help_canvas->setImageFormat('jpg');

        $max_white = 255 * 3 * $this->BLOCK_SIZE * $this->BLOCK_SIZE;
        $max_black = 0 * 3 * $this->BLOCK_SIZE * $this->BLOCK_SIZE;

        for ($y=0; $y<$this->image->getImageHeight()/$this->BLOCK_SIZE; $y ++){

            for ($x=0; $x<$this->image->getImageWidth()/$this->BLOCK_SIZE; $x ++){

                // exportImagePixels returns 3 values for each pixel R,G,B <0-255>
                // all values are join into one array
                // 0 - balck, 255 - white
                $pixels = $this->image->exportImagePixels($x*$this->BLOCK_SIZE, $y*$this->BLOCK_SIZE, $this->BLOCK_SIZE, $this->BLOCK_SIZE,
                    "RGB", Imagick::PIXEL_CHAR);

                $ratio = array_sum($pixels) / $max_white;
                $size = ($this->R_CONST - $ratio) * $this->BLOCK_SIZE ;

                $help_canvas = $this->generate_block($help_canvas, $size);
                $this->image->compositeImage($help_canvas, $help_canvas->getImageCompose(), $x*$this->BLOCK_SIZE, $y*$this->BLOCK_SIZE);
            }
        }

    }

    /*
     * resize image to double size of printer paper width
     */
    private function resize_img_to_printer_width()
    {
        $height = $this->image->getImageHeight() / $this->image->getImageWidth() * self::HT_IMG_WIDTH;
        $this->image->resizeImage(self::HT_IMG_WIDTH, $height, Imagick::FILTER_POINT, 0);
    }

    /*
     * draw black circle in centre of
     * white square(BLOCK_SIZE x BLOCK_SIZE) with $size as diameter
     * $size is aprox <0,2*BLOCK_SIZE>
     */
    private function generate_block($help_canvas, $size)
    {
        // square
        $square = $this->get_square(0, 0);
        $help_canvas->drawImage($square);

        // circle
        $circle = $this->get_circle(0, 0, $size);
        $help_canvas->drawImage($circle);

        return $help_canvas;
    }

    /*
     * return imagic black circle object
     */
    private function get_circle($originX, $originY, $size)
    {
        $this->circle_helper = new ImagickDraw();
        $this->circle_helper->setFillColor(new ImagickPixel("#000000"));
        $this->circle_helper->circle(
            $originX + $this->get_block_center(),
            $originY + $this->get_block_center(),
            $originX + $this->get_block_center() + $size/2,
            $originY + $this->get_block_center() + $size/2
        );

        return $this->circle_helper;
    }

    /*
     * return imagic white square object
     */
    private function get_square($originX, $originY)
    {
        $this->square_helper = new ImagickDraw();
        $this->square_helper->setFillColor(new ImagickPixel("#FFFFFF"));
        $this->square_helper->rectangle($originX, $originY,
            $originX + $this->BLOCK_SIZE-1, $originY + $this->BLOCK_SIZE - 1);

        return $this->square_helper;
    }
}

?>
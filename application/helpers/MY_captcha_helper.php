<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Генерирует капчу
*
* @access   public
* @param    array
* @return   string
*/

if ( ! function_exists('create_captcha_stream'))
{
	function create_captcha_stream($data = '')
    {
        $defaults = array('word' => '', 'img_width' => 150, 'img_height' => 30, 'font_path' => '', 'random_str_length' => '5', 'border' => TRUE);

        foreach ($defaults as $key => $val)
        {
            if ( ! is_array($data))
            {
                if ( ! isset($$key) OR $$key == '')
                {
                    $$key = $val;
                }
            }
            else
            {
                $$key = ( ! isset($data[$key])) ? $val : $data[$key];
            }
        }



        if ( ! extension_loaded('gd'))
        {
            return FALSE;
        }


        // -----------------------------------
        // Do we have a "word" yet?
        // -----------------------------------

       if ($word == '')
       {
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $str = '';
            for ($i = 0; $i < $random_str_length; $i++)
            {
                $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
            }

            $word = $str;
       }

        // -----------------------------------
        // Determine angle and position
        // -----------------------------------

        $length	= strlen($word);
        $angle	= ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
        $x_axis	= rand(6, (360/$length)-16);
        $y_axis = ($angle >= 0 ) ? rand($img_height, $img_width) : rand(6, $img_height);

        // -----------------------------------
        // Create image
        // -----------------------------------

        if (function_exists('imagecreatetruecolor'))
        {
            $im = imagecreatetruecolor($img_width, $img_height);
        }
        else
        {
            $im = imagecreate($img_width, $img_height);
        }

        // -----------------------------------
        //  Assign colors
        // -----------------------------------

        /* RAND */
        $red = rand(50, 100);
        $green = rand(50, 100);
        $blue = rand(50, 100);

        $bg_color	= imagecolorallocate($im, 255, 255, 255);
        $border_color	= imagecolorallocate($im, $red, $green, $blue);
        $text_color	= imagecolorallocate($im, $red+30, $green+30, $blue+30);
        $grid_color	= imagecolorallocate($im, $red+60, $green+60, $blue+60);
        $shadow_color	= imagecolorallocate($im, 255, 240, 240);



        // -----------------------------------
        //  Create the rectangle
        // -----------------------------------

        ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

        // -----------------------------------
        //  Create the spiral pattern
        // -----------------------------------

        $theta		= 1;
        $thetac		= 7;
        $radius		= 16;
        $circles	= 20;
        $points		= 32;

        for ($i = 0; $i < ($circles * $points) - 1; $i++)
        {
            $theta = $theta + $thetac;
            $rad = $radius * ($i / $points );
            $x = ($rad * cos($theta)) + $x_axis;
            $y = ($rad * sin($theta)) + $y_axis;
            $theta = $theta + $thetac;
            $rad1 = $radius * (($i + 1) / $points);
            $x1 = ($rad1 * cos($theta)) + $x_axis;
            $y1 = ($rad1 * sin($theta )) + $y_axis;
            imageline($im, $x, $y, $x1, $y1, $grid_color);
            $theta = $theta - $thetac;
        }

        // -----------------------------------
        //  Write the text
        // -----------------------------------

        $use_font = ($font_path != '' AND file_exists($font_path) AND function_exists('imagettftext')) ? TRUE : FALSE;

        if ($use_font == FALSE)
        {
            $font_size = 7;
            $x = rand(1, $img_width-(($length*$font_size)*2));
            $y = 0;
        }
        else
        {
            $font_size	= 22;
            $x = rand(1, $img_width-($length*$font_size));
            $y = $font_size+2;
        }

        for ($i = 0; $i < strlen($word); $i++)
        {
            if ($use_font == FALSE)
            {
                $y = rand(1 , $img_height-($font_size*3));
                imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
                $x += ($font_size*2);
            }
            else
            {
                $y = rand($font_size , $img_height-($font_size/3));
                imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, substr($word, $i, 1));
                $x += $font_size;
            }
        }


        // -----------------------------------
        //  Create the border
        // -----------------------------------

        if ($border == TRUE)
        {
            imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);
        }

        // -----------------------------------
        //  Generate the image
        // -----------------------------------

        header("Content-type: image/jpeg");
        ImageJPEG($im);

        ImageDestroy($im);

        return $word;
    }
}
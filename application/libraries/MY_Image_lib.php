<?php
class MY_Image_lib extends CI_Image_lib {

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Преобразование BMP в JPG. Эквивалент imagecreatefromgif
    *
    * @access	public
    * @param    string - месторасположение файла
    * @return	string
    */

    function imagecreatefrombmp($filename)
	{
		if (! $f1 = fopen($filename,"rb")) return FALSE;

		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		if ($FILE['file_type'] != 19778) return FALSE;

		$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
			'/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
			'/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
		$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);

		if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4) $BMP['decal'] = 0;

		$PALETTE = array();
		if ($BMP['colors'] < 16777216 && $BMP['colors'] != 65536) {
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
		}

		$IMG = fread($f1,$BMP['size_bitmap']);
		$VIDE = chr(0);

		$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		$P = 0;
		$Y = $BMP['height']-1;
		while ($Y >= 0) {
			$X=0;
			while ($X < $BMP['width']) {
				if ($BMP['bits_per_pixel'] == 24)
					$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
				elseif ($BMP['bits_per_pixel'] == 16) {
					$COLOR = unpack("v",substr($IMG,$P,2));
					$blue  = ($COLOR[1] & 0x001f) << 3;
					$green = ($COLOR[1] & 0x07e0) >> 3;
					$red   = ($COLOR[1] & 0xf800) >> 8;
					$COLOR[1] = $red * 65536 + $green * 256 + $blue;
				}
				elseif ($BMP['bits_per_pixel'] == 8) {
					$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 4) {
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 1) {
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
					elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
					elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
					elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
					elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
					elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
					elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
					elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				else
					return FALSE;

				imagesetpixel($res,$X,$Y,$COLOR[1]);

				$X++;
				$P += $BMP['bytes_per_pixel'];
			}
			$Y--;
			$P+=$BMP['decal'];
		}

		fclose($f1);
		return $res;
	}

    // ------------------------------------------------------------------------

    /**
    * Преобразование любого загруженного изображения в jpg
    *
    * @access	public
    * @param    string - месторасположение файла
    * @param    int - id элемента в базе, к которому имеет отношение загруженное изображение
    * @param    string - постфикс для созданного изображения
    * @return	void
    */
	function src_img_convert ($path, $id, $postfix = '_src')
    {
        $iid = ceil(intval($id)/1000);
        $dir = FCPATH.substr($path, 1).$iid.'/';

        if (is_dir($dir) && $handle = opendir($dir))
        {
            while (false !== ($file = readdir($handle)))
            {
                if (preg_match("/^".$id."\.([[:alnum:]])*$/", $file))
                {
                    $pieces = explode(".", $file);
                    $extension = strtolower($pieces[count($pieces)-1]);

                    $pic_path = $dir . $file;
                    $src_pic_path = $dir . $id . $postfix . '.' . $extension;
                    $ready_pic_path = $dir . $id . '.jpg';

                    if($extension == 'jpeg')
                    {
                        copy($pic_path, $ready_pic_path);
                        rename($pic_path, $src_pic_path);
                    }

                    if ($extension == 'gif' || $extension == 'bmp' || $extension == 'png')
                    {
                        if($extension == 'gif') $ee = imagecreatefromgif ($pic_path);
                        if($extension == 'bmp') $ee = $this->imagecreatefrombmp ($pic_path);
                        if($extension == 'png') $ee = imagecreatefrompng ($pic_path);

                        ImageJPEG ($ee, $ready_pic_path, 100);
                        rename($pic_path, $src_pic_path);
                    }
                }
            }
        }
	}

    // ------------------------------------------------------------------------

    /**
    * Расширенные возможности по созданию превьюшек. Отличие от нативной функции заключается в том,
    * что если указаны x и y, то изображение сначала будет пропорционально уменьшено, а затем лишнее
    * будет обрезано с двух сторон.
    *
    * @access	public
    * @param    string - месторасположение файла
    * @param    int - id элемента в базе, к которому имеет отношение загруженное изображение
    * @param    string - постфикс
    * @param    int - ширина
    * @param    int - высота
    * @return	void
    */
	function thumb_create ($path, $id, $prefix='_thumb', $x=0, $y=0)
    {
        $iid = ceil(intval($id)/1000);
        $dir = FCPATH.substr($path, 1).$iid.'/';
        $src_file = $dir . $id . '.jpg';
        $dst_file = $dir . $id . $prefix . '.jpg';

        if (is_file($src_file) || ($x != 0 && $y != 0))
        {
            $size = getimagesize ($src_file);
            $xsize = $size[0];
            $ysize = $size[1];

            $config['source_image'] = $src_file;
            $config['new_image'] = $dst_file;
            $config['image_library'] = 'gd2';
            $config['maintain_ratio'] = TRUE;

            if ($y == 0)
            {
                $config['width'] = $x;
                $config['height'] = round ($ysize/($xsize/$x));
                $config['master_dim'] = 'width';
				$this->initialize($config);
				$this->resize();
				$this->clear();
            }

            if ($x == 0)
            {
                $config['width'] = round ($xsize/($ysize/$y));
                $config['height'] = $y;
                $config['master_dim'] = 'height';
				$this->initialize($config);
				$this->resize();
				$this->clear();
            }

            if ($x != 0 && $y != 0)
            {
				if ($y > $x)
				{
					$xt = round ($xsize/($ysize/$y));

					if ($xt > $x)
					{
						$config['width'] = $xt;
						$config['height'] = $y;
						$config['master_dim'] = 'height';
					}
					else
					{
						$xt = $x;
						$config['width'] = $x;
						$config['height'] = round ($ysize/($xsize/$x));
						$config['master_dim'] = 'width';
					}
				}

                if (($y < $x) || ($y == $x))
				{
					$yt = round ($ysize/($xsize/$x));

					if ($yt > $y)
					{
						$config['width'] = $x;
						$config['height'] = $yt;
						$config['master_dim'] = 'width';
					}
					else
					{
						$config['width'] = round ($xsize/($ysize/$y));
						$config['height'] = $y;
						$config['master_dim'] = 'height';
					}
				}

				$this->initialize($config);
				$this->resize();
				$this->clear();

				$size = getimagesize ($dst_file);
				$xsize = $size[0];
				$ysize = $size[1];

				if ($xsize > $x) {$x_axis = ceil(($xsize - $x)/2); $y_axis = 0;}
				if ($ysize > $y) {$y_axis = ceil(($ysize - $y)/2); $x_axis = 0;}

				$dst_image = imagecreatetruecolor($x,$y);
				$src_image = imagecreatefromjpeg($dst_file);

				@imagecopyresized ($dst_image, $src_image, 0, 0, $x_axis, $y_axis, $x, $y, $x, $y);
				$ready_tn = ImageJPEG ($dst_image, $dst_file, 100);
            }
        }
	}
}
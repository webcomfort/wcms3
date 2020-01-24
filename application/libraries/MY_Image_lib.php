<?php
class MY_Image_lib extends CI_Image_lib {

	private $CI;
	public function __construct()
    {
	    $this->CI =& get_instance();
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
	 * Получение значения счетчика для файлов в директории
	 *
	 * @access	public
	 * @param   string - директория
	 * @return	int
	 */
	function get_counter($path){
		if (is_dir($path) && $handle = opendir($path)) {
			$num = array();
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( preg_match( "/^[0-9]*\.([[:alnum:]])*$/", $file ) ) {
					$pieces = explode(".", $file);
					$digit = $pieces[0];
					$num[] = $digit;
				}
			}
			if(count($num) > 0){
				rsort($num);
				return intval($num[0]) + 1;
			} else {
				return 1;
			}
		}
	}

	/**
	 * Перемещение загруженного файла
	 *
	 * @access	public
	 * @param   string - имя файла
	 * @param   string - месторасположение итогового файла
	 * @param   int    - id элемента в базе, к которому имеет отношение загруженное изображение
	 * @param   bool   - мультизагрузка
	 * @param   bool   - конвертировать в jpg?
	 * @param   array  - параметры иконок
	 * @return	bool
	 */
	function src_file_move ($name, $path, $id, $multiple = false, $convert = false, $thumbs = array(), $tmp = true) {

		$iid = ceil( intval( $id ) / 1000 );

		if ( $multiple ) {
			$dir = FCPATH . substr( $path, 1 ) . $iid . '/' . $id . '/';
			if(!is_dir($dir)) mkdir($dir, 0755, true);
			$i = $this->get_counter($dir);
		} else {
			$dir = FCPATH . substr( $path, 1 ) . $iid . '/';
			if(!is_dir($dir)) mkdir($dir, 0755, true);
		}

		$resdir = ($tmp) ? FCPATH . 'tmp/' : $dir;

		if(is_file($resdir.$name)) {

			$pieces    = explode( ".", $name );
			$extension = strtolower( $pieces[ count( $pieces ) - 1 ] );
			$pic_path  = ( !$multiple ) ? $dir . $id . '.' . $extension : $dir . $i . '.' . $extension;
			rename( $resdir . $name, $pic_path );

			if($convert && getimagesize ($pic_path)) $converted = $this->src_img_convert ($pic_path);
			if($convert && count($thumbs) > 0 && isset($converted) && getimagesize ($converted)) {
				foreach ($thumbs as $key => $value) {
					$this->thumb_create( $converted, $key, $value['width'], $value['height'] );
				}
			}

			return true;
		} else {
			return false;
		}
	}

    // ------------------------------------------------------------------------

    /**
    * Преобразование любого загруженного изображения в jpg
    *
    * @access	public
    * @param    string - месторасположение файла
    * @return	mixed
    */
	function src_img_convert ($path)
    {
	    if(is_file($path)) {

	    	$path_parts = pathinfo( $path );
		    $extension  = $path_parts['extension'];
		    $filename   = $path_parts['filename'];
		    $dir        = $path_parts['dirname'];

		    $src_pic_path   = $dir . DIRECTORY_SEPARATOR . $filename . '_src' . '.' . $extension;
		    $ready_pic_path = $dir . DIRECTORY_SEPARATOR . $filename . '.jpg';
		    $ready_pic_path_webp = $dir . DIRECTORY_SEPARATOR . $filename . '_webp' . '.webp';

		    if ( $extension == 'jpeg' ) {
			    copy( $path, $ready_pic_path );
			    rename( $path, $src_pic_path );
		    }

		    if ( $extension == 'gif' || $extension == 'bmp' || $extension == 'png' ) {
			    if ( $extension == 'gif' ) {
				    $ee = imagecreatefromgif( $path );
			    }
			    if ( $extension == 'bmp' ) {
				    $ee = $this->imagecreatefrombmp( path );
			    }
			    if ( $extension == 'png' ) {
				    $ee = imagecreatefrompng( $path );
			    }

			    ImageJPEG( $ee, $ready_pic_path, 100 );
			    rename( $path, $src_pic_path );
		    }

		    if($this->CI->config->item('cms_webp')) {
			    $ii = imagecreatefromjpeg( $ready_pic_path );
			    imagewebp( $ii, $ready_pic_path_webp, 80);
			    if (filesize($ready_pic_path_webp) % 2 == 1) {
				    file_put_contents($ready_pic_path_webp, "\0", FILE_APPEND);
			    }
		    }

		    return $ready_pic_path;
	    } else {
	    	return false;
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
    * @param    string - постфикс
    * @param    int - ширина
    * @param    int - высота
    * @return	void
    */
	function thumb_create ($path, $prefix='_thumb', $x=0, $y=0)
    {
	    $path_parts     = pathinfo($path);
	    $filename       = $path_parts['filename'];
	    $dir            = $path_parts['dirname'];

	    $dst_file = $dir . DIRECTORY_SEPARATOR . $filename . $prefix . '.jpg';
	    $dst_file_webp = $dir . DIRECTORY_SEPARATOR . $filename . $prefix . '.webp';

        if (is_file($path) || ($x != 0 && $y != 0))
        {
            $size = getimagesize ($path);
            $xsize = $size[0];
            $ysize = $size[1];

            $config['source_image'] = $path;
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
				ImageJPEG ($dst_image, $dst_file, 100);
            }

	        if($this->CI->config->item('cms_webp')) {
		        $ii = imagecreatefromjpeg( $dst_file );
		        imagewebp( $ii, $dst_file_webp, 80 );
		        if ( filesize( $dst_file_webp ) % 2 == 1 ) {
			        file_put_contents( $dst_file_webp, "\0", FILE_APPEND );
		        }
	        }
        }
	}

	/**
	 * Вернет массив параметров изображения для вывода в макет
	 *
	 * @access  public
	 * @param   string - каталог, где хранятся изображения
	 * @param   array - массив постфиксов и параметров
	 * @param   int - id изображения
	 * @param   string - alt
	 * @param   string - css class
	 * @return  array
	 */
	function get_img($dir, $thumbs, $id, $name, $css='')
	{
		$CI =& get_instance();
		$CI->load->helper(array('html'));

		$images = array();
		$iid = ceil(intval($id)/1000);

		foreach ($thumbs as $key => $value)
		{
			$path = FCPATH.substr($dir, 1).$iid.'/'.$id.$key.'.jpg';
			$url  = $dir.$iid.'/'.$id.$key.'.jpg';

			if (is_file ($path))
			{
				$size   = getimagesize ($path);
				$width  = $size[0];
				$height = $size[1];

				$image_properties = array(
					'src'       => $url,
					'alt'       => $name,
					'width'     => $width,
					'height'    => $height,
					'title'     => $name,
					'class'     => $css
				);

				$images[$key]['img'] = img($image_properties);
				$images[$key]['url'] = $url;
			}
		}

		return $images;
	}
}
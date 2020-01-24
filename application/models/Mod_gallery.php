<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода галереи
 */

class Mod_gallery extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем галерею
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        $id = $params[0];

        // Параметры галереи
        $query = $this->db->get_where('w_galleries', array('gallery_id' => $id, 'gallery_active' => 1));

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
	        $dimensions = $this->config->item('cms_gallery_sizes');
            $views = $this->config->item('cms_gallery_views');
            $view_id = $row->gallery_view_id;
            $view = $views[$view_id]['file'];

            // Фотографии
            $this->db->order_by("photo_sort", "asc");
			$query = $this->db->get_where('w_gallery_photos', array('photo_gallery_id' => $id, 'photo_active' => 1));

            if ($query->num_rows() > 0)
    		{
                $this->load->helper('html');
                $images = array();

                foreach ($query->result() as $row)
                {
	                $iid = ceil(intval($row->photo_id)/1000);

                	foreach ($dimensions as $key => $value)
                    {
	                    // Main image
	                    if ($this->config->item('cms_webp')){
		                    $path = FCPATH.substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.'_webp.webp';
		                    $url  = substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.'_webp.webp';
	                    } else {
		                    $path = FCPATH.substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.'.jpg';
		                    $url  = substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.'.jpg';
	                    }
	                    if (is_file ($path))
	                    {
		                    $size   = getimagesize ($path);
		                    $width  = $size[0];
		                    $height = $size[1];

		                    $image_properties = array(
			                    'src'       => $url,
			                    'alt'       => $row->photo_name,
			                    'class'     => '',
			                    'width'     => $width,
			                    'height'    => $height,
			                    'title'     => $row->photo_name,
			                    'rel'       => ''
		                    );

		                    $images['_main'][$row->photo_id]['url']  = $url;
		                    $images['_main'][$row->photo_id]['img']  = img($image_properties, FALSE);
		                    $images['_main'][$row->photo_id]['link'] = ($row->photo_link) ? $row->photo_link : '#';
		                    $images['_main'][$row->photo_id]['text'] = $row->photo_text;
		                    $images['_main'][$row->photo_id]['name'] = $row->photo_name;
	                    }

                    	if ($this->config->item('cms_webp')){
		                    $path = FCPATH.substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.$key.'.webp';
		                    $url  = substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.$key.'.webp';
	                    } else {
		                    $path = FCPATH.substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.$key.'.jpg';
		                    $url  = substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$row->photo_id.$key.'.jpg';
	                    }
                        if (is_file ($path))
                        {
                            $size   = getimagesize ($path);
                            $width  = $size[0];
                            $height = $size[1];

                            $image_properties = array(
                                'src'       => $url,
                                'alt'       => $row->photo_name,
                                'class'     => $dimensions[$key]['class'],
                                'width'     => $width,
                                'height'    => $height,
                                'title'     => $row->photo_name,
                                'rel'       => $dimensions[$key]['rel']
                            );

                            $images[$key][$row->photo_id]['url']  = $url;
                            $images[$key][$row->photo_id]['img']  = img($image_properties, FALSE);
                            $images[$key][$row->photo_id]['link'] = ($row->photo_link) ? $row->photo_link : '#';
                            $images[$key][$row->photo_id]['text'] = $row->photo_text;
                            $images[$key][$row->photo_id]['name'] = $row->photo_name;
                        }
                    }

                    // Исходники
	                $folder = $this->config->item('cms_gallery_dir').$iid.'/';
	                $path = FCPATH.substr($folder, 1);

	                if ($handle = opendir($path))
	                {
		                while (($file = readdir($handle)) !== false)
		                {
			                if (preg_match ("/^".$row->photo_id."_src\.([[:alnum:]])*$/", $file))
			                {
				                $url  = substr($this->config->item('cms_gallery_dir'), 1).$iid.'/'.$file;
			                	$size = getimagesize($path.$file);
				                $width  = $size[0];
				                $height = $size[1];

				                $image_properties = array(
					                'src'       => $url,
					                'alt'       => $row->photo_name,
					                'class'     => '',
					                'width'     => $width,
					                'height'    => $height,
					                'title'     => $row->photo_name,
					                'rel'       => ''
				                );

				                $images['_src'][$row->photo_id]['url']  = $url;
				                $images['_src'][$row->photo_id]['img']  = img($image_properties, FALSE);
				                $images['_src'][$row->photo_id]['link'] = ($row->photo_link) ? $row->photo_link : '#';
				                $images['_src'][$row->photo_id]['text'] = $row->photo_text;
				                $images['_src'][$row->photo_id]['name'] = $row->photo_name;
			                }
		                }
	                }
                }

			    $data = array(
				    'gallery_id'        => $id,
				    'gallery_images'    => $images
			    );
                return $this->load->view('site/'.$view, $data, true);
            }
        }
    }
}

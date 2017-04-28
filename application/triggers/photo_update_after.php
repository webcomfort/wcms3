<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка картинок
 *
 * @action	update
 * @mode	after
 */

$id = $this->rec;
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

// Изображения
if ($_FILES['pic']['tmp_name'] != '')
{
	$params = $this->CI->config->item('cms_gallery_views');

	$this->CI->load->library('image_lib');

	$this->CI->db->select('gallery_view_id AS vid');
    $this->CI->db->from('w_galleries');
    $this->CI->db->where('gallery_id', $this->CI->session->userdata('photo_filter'));
    $query = $this->CI->db->get();

    if ($query->num_rows() > 0)
    {
        $row = $query->row();

        $dimensions = $params[$row->vid]['img'];

	    $this->CI->image_lib->src_img_convert($this->CI->config->item('cms_gallery_dir'), $id);

	    foreach ($dimensions as $key => $value)
	    {
		    $this->CI->image_lib->thumb_create($this->CI->config->item('cms_gallery_dir'), $id, $key, $value['width'], $value['height']);
		}
	}
}
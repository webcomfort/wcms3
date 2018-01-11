<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка картинок
 *
 * @action	insert
 * @mode	after
 */

$this->CI->db->select_max('photo_id', 'id');
$query = $this->CI->db->get('w_gallery_photos');
$row = $query->row();
$id = $row->id;
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

// Изображения
if ($_FILES['pic']['tmp_name'] != '')
{
	$this->CI->load->library('image_lib');
	$params = $this->CI->config->item('cms_gallery_sizes');
	$this->CI->image_lib->src_img_convert($this->CI->config->item('cms_gallery_dir'), $id);
	foreach ($params as $key => $value)
	{
		$this->CI->image_lib->thumb_create($this->CI->config->item('cms_gallery_dir'), $id, $key, $value['width'], $value['height']);
	}
}

// Теги
$this->CI->Cms_tags->admin_tags_insert_update($id, 'photo');
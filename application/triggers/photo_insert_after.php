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
$files = $this->CI->input->post('pic_files', true);
if (is_array($files))
{
	$this->CI->load->library( 'image_lib' );
	foreach ($files as $value) {
		$this->CI->image_lib->src_file_move ($value, $this->CI->config->item( 'cms_gallery_dir' ), $id, false, true, $this->CI->config->item('cms_gallery_sizes'), true);
	}
}

// Теги
$this->CI->Cms_tags->admin_tags_insert_update($id, 'photo');
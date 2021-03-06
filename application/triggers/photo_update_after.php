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
$files = $this->CI->input->post('pic_files', true);
if (is_array($files))
{
	$this->CI->load->library( 'image_lib' );
	foreach ($files as $value) {
		$this->CI->image_lib->src_file_move ($value, $this->CI->config->item( 'cms_gallery_dir' ), $id, false, true, $this->CI->config->item('cms_gallery_sizes'), false);
	}
	$this->CI->cms_utils->update_updated('w_gallery_photos', 'photo_id', $id);
}

// Теги
$this->CI->Cms_tags->admin_tags_insert_update($id, 'photo');
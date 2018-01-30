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
		$this->CI->image_lib->src_file_move ($value, $this->CI->config->item( 'cms_bg_dir' ), $id, false, true, array('_thumb' => array('width'=>150,'height'=>150)), false);
	}
}
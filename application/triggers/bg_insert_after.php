<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка картинок
 *
 * @action	insert
 * @mode	after
 */

$this->CI->db->select_max('bg_id', 'id');
$query = $this->CI->db->get('w_backgrounds');
$row = $query->row();
$id = $row->id;
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

// Изображения
if ($_FILES['pic']['tmp_name'] != '')
{
	$this->CI->load->library('image_lib');
    $this->CI->image_lib->src_img_convert($this->CI->config->item('cms_bg_dir'), $id);
    $this->CI->image_lib->thumb_create($this->CI->config->item('cms_bg_dir'), $id, '_thumb', 150, 0);
}
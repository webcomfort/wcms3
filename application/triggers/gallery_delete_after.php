<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление изображений
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------
// Удаление изображений

$query = $this->CI->db->get_where('w_gallery_photos', array('photo_gallery_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->photo_id, $last_basket_element, 'w_gallery_photos', 'photo_id', 'Фото', '', $this->CI->config->item('cms_gallery_dir'));
    }
}
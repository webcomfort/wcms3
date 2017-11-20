<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление дочерних элементов и данных из таблиц пересечений
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

// Теги
$this->CI->Cms_tags->admin_tags_delete($id, 'photo', $last_basket_element);
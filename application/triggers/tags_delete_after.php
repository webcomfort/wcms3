<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление дочерних элементов и данных из таблиц пересечений
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$this->CI->load->model('Cms_tags');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

// Удаляем
$this->CI->Cms_tags->delete_tags_cross($id, $last_basket_element);
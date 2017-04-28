<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление дочерних страниц в корзину
 *
 * @action	delete
 * @mode	after
 */
 
$this->CI->db->select('cms_page_id, cms_page_pid')->from('w_cms_pages');
$query = $this->CI->db->get();

if ($query->num_rows() > 0)
{
    $forest =& $this->CI->tree->get_tree('cms_page_id', 'cms_page_pid', $query->result_array(), $this->rec);
    
    $this->CI->load->library('trigger');
    $this->CI->trigger->delete_child($forest, $this->CI->trigger->get_last_basket_element(), 'w_cms_pages', 'cms_page_id', 'Страница администрирования', 'cms_page_name');
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление товарной категории
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------
// Удаление пересечений удаляемого элемента

$query = $this->CI->db->get_where('w_shop_items_cats', array('cat_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($sic->ncc_id, $last_basket_element, 'w_shop_items_cats', 'sic_id', 'Пересечение', '');
    }
}
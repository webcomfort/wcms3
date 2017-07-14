<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление вендора
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

$query = $this->CI->db->get_where('w_shop_items', array('item_vendor_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->change_relative($row->item_id, $last_basket_element, 'w_shop_items', 'item_id', 'item_vendor_id', 'Товар', '');
        $data = array( 'item_vendor_id' => '0' );
        $this->CI->db->update('w_shop_items', $data, "item_id = '".$row->item_id."'");
    }
}
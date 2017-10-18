<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление товарного типа
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------
// Удаление полей удаляемого элемента

$query = $this->CI->db->get_where('w_shop_types_fields', array('type_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->tf_id, $last_basket_element, 'w_shop_types_fields', 'tf_id', 'Поля', '');
    }
}
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
        $this->CI->trigger->delete_relative($row->sic_id, $last_basket_element, 'w_shop_items_cats', 'sic_id', 'Пересечение', '');
    }
}

// ------------------------------------------------------------------------
// Удаление дочерних элементов и их пересечений

$this->CI->db->select('cat_id, cat_pid, cat_name')->from('w_shop_categories');
$query = $this->CI->db->get();

if ($query->num_rows() > 0)
{
    $forest =& $this->CI->tree->get_tree('cat_id', 'cat_pid', $query->result_array(), $id);
    $parameters_array = array(
        'table'         => 'w_shop_items_cats',
        'table_pid'     => 'cat_id',
        'table_where'   => array(),
        'table_key'     => 'sic_id',
        'title'         => 'Пересечение',
    );
    $this->CI->trigger->delete_child($forest, $last_basket_element, 'w_shop_categories', 'cat_id', 'Товарная категория', 'cat_name', $parameters_array, false);
}
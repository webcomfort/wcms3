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
// Удаление модулей

$query = $this->CI->db->get_where('w_sidebar_widgets', array('sidebar_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->widget_id, $last_basket_element, 'w_sidebar_widgets', 'widget_id', 'Виджет', '', false);
    }
}

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Преобразование объектов в неподключенные
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

$query = $this->CI->db->get_where('w_tree_objects_and_tasks', array('oat_project_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->change_relative($row->oat_id, $last_basket_element, 'w_tree_objects_and_tasks', 'oat_id', 'oat_project_id', 'Объект', '');
        $data = array( 'oat_project_id' => '0' );
        $this->CI->db->update('w_tree_objects_and_tasks', $data, "oat_id = '".$row->oat_id."'");
    }
}
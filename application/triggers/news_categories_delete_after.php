<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление новостной рубрики
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

$query = $this->CI->db->get_where('w_news_categories_cross', array('news_cat_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->ncc_id, $last_basket_element, 'w_news_categories_cross', 'ncc_id', 'Пересечение', '');
    }
}

// ------------------------------------------------------------------------
// Удаление дочерних элементов и их пересечений

$this->CI->db->select('news_cat_id, news_cat_pid, news_cat_name')->from('w_news_categories');
$query = $this->CI->db->get();

if ($query->num_rows() > 0)
{
	@$forest =& $this->CI->tree->get_tree('news_cat_id', 'news_cat_pid', $query->result_array(), $id);
	$parameters_array = array(
		'table'         => 'w_news_categories_cross',
		'table_pid'     => 'news_cat_id',
		'table_where'   => array(),
		'table_key'     => 'ncc_id',
		'title'         => 'Пересечение',
	);
	$this->CI->trigger->delete_child($forest, $last_basket_element, 'w_news_categories', 'news_cat_id', 'Новостная категория', 'news_cat_name', $parameters_array, false);
}

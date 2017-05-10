<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Преобразование новостей в неподключенные
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

$query = $this->CI->db->get_where('w_news_categories_cross', array('news_cat_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->ncc_id, $last_basket_element, 'w_news_categories_cross', 'ncc_id', 'Пересечение с новостью', '');
    }
}
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

$query = $this->CI->db->get_where('w_news', array('news_main_cat' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->change_relative($row->news_id, $last_basket_element, 'w_news', 'news_id', 'news_main_cat', 'Новость', '');
        $data = array( 'news_main_cat' => '99999' );
        $this->CI->db->update('w_news', $data, "news_id = '".$row->news_id."'");
    }
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление дочерних страниц, статей и подключений в корзину
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------
// Удаление дочерних элементов

$this->CI->db->select('page_id, page_pid')->from('w_pages');
$query = $this->CI->db->get();

if ($query->num_rows() > 0)
{
    $forest =& $this->CI->tree->get_tree('page_id', 'page_pid', $query->result_array(), $id);
    $this->CI->trigger->delete_child($forest, $this->CI->trigger->get_last_basket_element(), 'w_pages', 'page_id', 'Страница сайта', 'page_name');
}

// ------------------------------------------------------------------------
// Очистка индекса

if($this->CI->config->item('cms_site_indexing'))
{
    $this->CI->load->library('search');
    $url = ($oldvals['page_status'] == '4') ? $url = '/' : '/'.$oldvals['page_url'];
    $this->CI->search->index_delete($url);
}

// ------------------------------------------------------------------------
// Удаление статей

$query = $this->CI->db->get_where('w_pages_articles', array('article_page_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->pa_id, $last_basket_element, 'w_pages_articles', 'pa_id', 'Статья', '');
    }
}

// ------------------------------------------------------------------------
// Удаление подключений

$this->CI->Cms_inclusions->admin_inclusions_delete($id, 'pages');
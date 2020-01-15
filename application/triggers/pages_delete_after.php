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

// Права доступа к элементам
$this->CI->cms_user->delete_item_rights($id, 'page');

// ------------------------------------------------------------------------
// Удаление статей удаляемого элемента

$query = $this->CI->db->get_where('w_pages_articles', array('article_pid' => $id, 'article_pid_type' => 'pages'));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->article_id, $last_basket_element, 'w_pages_articles', 'article_id', 'Статья', '');
    }
}

// ------------------------------------------------------------------------
// Очистка индекса удаляемого элемента

$child_indexing = false;
if($this->CI->config->item('cms_site_indexing'))
{
    $this->CI->load->library('search');
    $this->CI->search->index_delete_by_url($oldvals['page_url']);
    $child_indexing = array('url' => 'page_url');
}

// ------------------------------------------------------------------------
// Удаление подключений удаляемого элемента

$this->CI->Cms_inclusions->admin_inclusions_delete($id, 'pages', $last_basket_element);

// ------------------------------------------------------------------------
// Удаление дочерних элементов, их статей, подключений и очистка поискового индекса

$this->CI->db->select('page_id, page_pid, page_url, page_name')->from('w_pages');
$query = $this->CI->db->get();

if ($query->num_rows() > 0)
{
    $forest =& $this->CI->tree->get_tree('page_id', 'page_pid', $query->result_array(), $id);
    $parameters_array[0] = array(
        'table'         => 'w_pages_articles',
        'table_pid'     => 'article_pid',
        'table_where'   => array('article_pid_type' => 'pages'),
        'table_key'     => 'article_id',
        'title'         => 'Статья',
    );
    $parameters_array[1] = array(
        'table'         => 'w_includes',
        'table_pid'     => 'obj_id',
        'table_where'   => array('inc_type' => 'pages'),
        'table_key'     => 'i_id',
        'title'         => 'Подключение',
    );
    $this->CI->trigger->delete_child($forest, $last_basket_element, 'w_pages', 'page_id', 'Страница сайта', 'page_name', $parameters_array, $child_indexing, 'pages');
}

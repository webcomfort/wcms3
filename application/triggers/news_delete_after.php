<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление дочерних элементов и данных из таблиц пересечений
 *
 * @action	delete
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$last_basket_element = $this->CI->trigger->get_last_basket_element();
$this->CI->db->cache_delete_all();

// Права доступа к элементам
$this->CI->cms_user->delete_item_rights($id, 'news');

// ------------------------------------------------------------------------
// Удаление статей удаляемого элемента

$query = $this->CI->db->get_where('w_pages_articles', array('article_pid' => $id, 'article_pid_type' => 'news'));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->article_id, $last_basket_element, 'w_pages_articles', 'article_id', 'Статья', '');
    }
}

// ------------------------------------------------------------------------
// Очистка индекса удаляемого элемента

if($this->CI->config->item('cms_site_indexing'))
{
    $this->CI->load->library('search');
    $url = '/post/'.$oldvals['news_url'];
    $this->CI->search->index_delete($url);
}

// ------------------------------------------------------------------------

// Удаление подключений
$this->CI->Cms_inclusions->admin_inclusions_delete($id, 'news', $last_basket_element);
// Теги
$this->CI->Cms_tags->admin_tags_delete($id, 'news', $last_basket_element);

// ------------------------------------------------------------------------

// Удаление пересечений

$query = $this->CI->db->get_where('w_news_categories_cross', array('news_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->ncc_id, $last_basket_element, 'w_news_categories_cross', 'ncc_id', 'Пересечение', '');
    }
}

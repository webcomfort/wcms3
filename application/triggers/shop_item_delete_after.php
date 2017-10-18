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

// ------------------------------------------------------------------------

// Удаление статей удаляемого элемента
$query = $this->CI->db->get_where('w_pages_articles', array('article_pid' => $id, 'article_pid_type' => 'shop'));

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
    $shop_page = $this->CI->Cms_shop->get_shop_page();
    $url = '/'.$shop_page.'/item/'.$oldvals['item_url'];
    $this->CI->search->index_delete($url);
    $child_indexing = array('pre_url' => '/'.$shop_page.'/item/', 'url' => 'item_url');
}

// ------------------------------------------------------------------------

// Удаление подключений
$this->CI->Cms_inclusions->admin_inclusions_delete($id, 'shop', $last_basket_element);

// ------------------------------------------------------------------------

// Удаление пересечений
$query = $this->CI->db->get_where('w_shop_items_cats', array('item_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->sic_id, $last_basket_element, 'w_shop_items_cats', 'sic_id', 'Пересечение', '');
    }
}

// ------------------------------------------------------------------------

// Удаление параметров удаляемого элемента
$query = $this->CI->db->get_where('w_shop_items_params', array('item_id' => $id));

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->par_id, $last_basket_element, 'w_shop_items_params', 'par_id', 'Параметр', '');
    }
}

// ------------------------------------------------------------------------
// Удаление дочерних элементов и их параметров

$this->CI->db->select('item_id, item_pid, item_name')->from('w_shop_items');
$query = $this->CI->db->get();
if ($query->num_rows() > 0) @$forest =& $this->CI->tree->get_tree('item_id', 'item_pid', $query->result_array(), $id);

// Параметры
$parameters_array[0] = array(
    'table'         => 'w_shop_items_params',
    'table_pid'     => 'item_id',
    'table_where'   => array(),
    'table_key'     => 'par_id',
    'title'         => 'Параметр',
);
// Пересечения
$parameters_array[1] = array(
    'table'         => 'w_shop_items_cats',
    'table_pid'     => 'item_id',
    'table_where'   => array(),
    'table_key'     => 'sic_id',
    'title'         => 'Пересечение',
);
// Статьи
$parameters_array[2] = array(
    'table'         => 'w_pages_articles',
    'table_pid'     => 'article_pid',
    'table_where'   => array('article_pid_type' => 'shop'),
    'table_key'     => 'article_id',
    'title'         => 'Статья',
);

$this->CI->trigger->delete_child($forest, $last_basket_element, 'w_shop_items', 'item_id', 'Товар', 'item_name', $parameters_array, $child_indexing, 'shop');
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
// Очистка индекса

if($this->CI->config->item('cms_site_indexing'))
{
    $inclusions = $this->CI->config->item('cms_site_inclusions');

    foreach ($inclusions as $key => $value)
    {
        if($value['file'] == 'mod_news') $inclusion_key = $key;
    }

    if(isset($inclusion_key))
    {
        $this->CI->db->select('w_pages.page_url AS url');
        $this->CI->db->from('w_includes');
        $this->CI->db->join('w_pages', 'w_includes.obj_id = w_pages.page_id');
        $this->CI->db->where('inc_id', $inclusion_key);
        $this->CI->db->where('inc_value', $oldvals['news_main_cat']);
		$this->CI->db->where('inc_type', 'pages');
        $this->CI->db->limit(1);

        $query = $this->CI->db->get();

        if ($query->num_rows() > 0)
        {
            $this->CI->load->library('search');

            // ------------------------------------------------------------------------

            $this->CI->db->cache_delete($row->url, $oldvals['news_url']);

            // ------------------------------------------------------------------------

            $url = '/'.$row->url.'/'.$oldvals['news_url'];
            $this->CI->search->index_delete($url);
        }
    }
}

// ------------------------------------------------------------------------

// Удаление подключений
$this->CI->Cms_inclusions->admin_inclusions_delete($id, 'news');

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
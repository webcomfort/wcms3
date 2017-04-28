<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Контроллер для страниц */

class Sitemap extends CI_Controller {

    function index()
    {
        $menues = $this->config->item('cms_site_menues');
        $output = array();

        foreach ($menues as $key => $value) {
            if ($value['map']) $output = array_merge($output, $this->_get_pages($key));
        }

        $output = array_merge($output, $this->_get_news());
        $data['xml_urls'] = $output;

        return $this->load->view('site/xml_map', $data);
    }

    // ------------------------------------------------------------------------

    /**
     * Страницы
     *
     * @access	private
     * @param   int
     * @return	array
     */
    function _get_pages($menu_id)
    {
        $statuses = array(1, 2);

        $this->db->select('page_id, page_pid, page_name, page_url, page_status, page_redirect');
        $this->db->from('w_pages');
        $this->db->where('page_menu_id =', $menu_id);
        $this->db->where_in('page_status', $statuses);
        $this->db->order_by('page_lang_id', 'asc');
        $this->db->order_by('page_sort', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $urls[] = 'http://'.$_SERVER["HTTP_HOST"].'/'.$row->page_url;
            }
        }

        return $urls;
    }

    // ------------------------------------------------------------------------

    /**
     * Новости
     *
     * @access	private
     * @return	array
     */
    function _get_news()
    {
        $this->load->model('Cms_news');

        // Ленты
        $this->db->select('news_cat_id');
        $this->db->from('w_news_categories');
        $query_lenta = $this->db->get();

        if ($query_lenta->num_rows() > 0)
        {
            foreach ($query_lenta->result() as $row_lenta)
            {
                $pages[$row_lenta->news_cat_id] = $this->Cms_news->get_news_page($row_lenta->news_cat_id);
            }
        }

        // Новости
        $this->db->select('news_url, news_main_cat');
        $this->db->from('w_news');
        $this->db->where('news_active', 1);
        $this->db->where('news_date <=', date('Y-m-d H:i:00'));
        $this->db->order_by('news_date', 'desc');
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $urls[] = 'http://'.$_SERVER["HTTP_HOST"].'/'.$pages[$row->news_main_cat].'/'.$row->news_url;
            }
        }

        return $urls;
    }
}
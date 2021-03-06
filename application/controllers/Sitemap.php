<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Контроллер для страниц */

class Sitemap extends CI_Controller {

    function index()
    {
        // Функции работы с пользователями и сессии
        $this->load->model('cms_user');
        $this->load->library('session');

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
	    // Новости
	    $this->db->select('news_id, news_url');
	    $this->db->from('w_news');
	    $this->db->where('news_active', 1);
	    $this->db->where('news_date <=', date('Y-m-d H:i:00'));
	    $this->db->order_by('news_date', 'desc');
	    $query = $this->db->get();

	    if ($query->num_rows() > 0)
	    {
		    foreach ($query->result() as $row)
		    {
			    $this->load->model('Cms_articles');
			    $articles = $this->Cms_articles->get_articles($row->news_id, 'news');
			    if(count($articles)>0) $urls[] = 'http://'.$_SERVER["HTTP_HOST"].'/post/'.$row->news_url;
		    }
	    }

	    return $urls;
    }
}
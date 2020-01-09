<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода последних новостей
 */

class Mod_news_latest extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Cms_news');
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем новости
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        $id         = (isset($params[0])) ? $params[0] : false;
        $limit      = (isset($params[1])) ? $params[1] : 3;
        $view       = (isset($params[2])) ? $params[2] : false;
        $cat        = $this->Cms_news->get_cat_params($id);
        $cat_name   = $cat['name'];
        $page_url   = $this->Cms_news->get_news_page($id);

        // Новости
        $this->db->select('w_news.news_id, news_name, news_date, news_cut, news_url');
        $this->db->from('w_news_categories_cross');
        $this->db->join('w_news', 'w_news.news_id = w_news_categories_cross.news_id');
        $this->db->where('news_cat_id', $id);
        $this->db->where('news_active', 1);
        $this->db->where('news_date <=', date('Y-m-d H:i:00'));
        $this->db->where('news_lang_id', LANG);
        $this->db->order_by('news_date', 'desc');
        $this->db->limit($limit);
        $this->db->cache_off();
        $query = $this->db->get();
        $this->db->cache_on();

        if ($query->num_rows() > 0)
		{
            $this->load->helper(array('date','text'));
            $news   = array();

            foreach ($query->result() as $row)
            {
                $news[] = array(
                    'news_id'   => $row->news_id,
                    'news_name' => $row->news_name,
                    'news_url'  => $page_url.'/'.$row->news_url,
                    'news_date' => date_format_rus ( $row->news_date, 'date' ),
                    'news_cut'  => $row->news_cut,
                    'news_img'  => $this->Cms_news->get_img($row->news_id, $row->news_name, 'card-img-top')
                );
            }

            $data = array(
                'news_latest_cat'   => $cat_name,
                'news_latest'       => $news,
                'news_latest_url'   => $page_url
            );

            if ($view) return $this->load->view('site/'.$view, $data, true);
        }
    }
}
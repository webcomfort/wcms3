<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода последних новостей
 */

class Mod_news extends CI_Model {

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
        $id = (isset($params[0])) ? $params[0] : false;

        if (!$this->uri->segment(2) || preg_int ($this->uri->segment(2))) $output = $this->_get_list($id);
        if ($this->uri->segment(1)  && $this->uri->segment(1) == 'post' && $this->uri->segment(2) && !preg_int ($this->uri->segment(2)) && preg_ext_string ($this->uri->segment(2))) $output = $this->_get_news($id, $this->uri->segment(2));

        return $output;
    }

    // ------------------------------------------------------------------------

    /**
     * Добавляем в head дополнительные теги
     *
     * @access  private
     * @param   int
     * @param   int
     * @param   int
     * @return  void
     */

    function _set_next_prev($start, $limit, $count)
    {
        $pages = ceil($count/$limit);
        $page = ceil($start/$limit)+1;

        if($page == 1 && $pages > $page) $this->Cms_page->add_head('<link rel="next" href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->uri->segment(1).'/'.$limit.'">'."\r\n");
        if($pages == $page)
        {
            if($page == 2) $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->uri->segment(1).'">'."\r\n");
			elseif($page == 1) {}
            else $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->uri->segment(1).'/'.(($page*$limit) - (2*$limit)).'">'."\r\n");
        }
        if($page != 1 && $pages != $page && $pages > $page)
        {
            if($page == 2)
            {
                $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->uri->segment(1).'">'."\r\n");
                if($pages > $page) $this->Cms_page->add_head('<link rel="next" href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->uri->segment(1).'/'.($page*$limit).'">'."\r\n");
            }
            else
            {
                $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->uri->segment(1).'/'.(($page*$limit) - (2*$limit)).'">'."\r\n");
                $this->Cms_page->add_head('<link rel="next" href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->uri->segment(1).'/'.($page*$limit).'">'."\r\n");
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем список новостей
     *
     * @access  private
     * @param   array
     * @return  string
     */
    function _get_list($id)
    {
        $page_url   = $this->uri->segment(1);
        $cat        = $this->Cms_news->get_cat_params($id);
        $cat_name   = $cat['name'];
        $views      = $this->config->item('cms_news_views');
        $view       = $views[$cat['view']]['file'];
        $limit      = $this->config->item('cms_news_limit');
		$statuses   = array(1);

        // Страницы
        $this->db->select('w_news.news_id');
        $this->db->from('w_news_categories_cross');
        $this->db->join('w_news', 'w_news.news_id = w_news_categories_cross.news_id');
        $this->db->where('news_cat_id', $id);
        $this->db->where('news_active', 1);
		$this->db->where_in('news_active', $statuses);
        $this->db->where('news_date <=', date('Y-m-d H:i:00'));
        $this->db->where('news_lang_id', LANG);

        $this->db->cache_off();
        //----------------------------------------------------------------------------------
        $query_count = $this->db->get();
		$count_rows = $query_count->num_rows();

        $this->load->library('pagination');
        $config['base_url']     = '/'.$page_url;
        $config['total_rows']   = $query_count->num_rows();
        $config['per_page']     = $limit;
        $config['first_link']   = $this->lang->line('pagination_first_link');
        $config['last_link']    = $this->lang->line('pagination_last_link');
        $config['next_link']    = $this->lang->line('pagination_next_link');
        $config['prev_link']    = $this->lang->line('pagination_prev_link');
        $config['uri_segment']  = 2;
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_links();

        // Лимиты
        $start = (preg_int ($this->uri->segment(2))) ? $this->uri->segment(2) : 0;
        if($start) $this->Cms_page->add_title(' - '.$this->lang->line('news_page').' '.(ceil(($start/$limit)+1)));
		$description = $this->Cms_page->get_description();
		$this->Cms_page->set_description($description . ' | '.$this->lang->line('news_page').' '.(ceil(($start/$limit)+1)).' из '.ceil($count_rows/$limit));
        $this->_set_next_prev($start, $limit, $count_rows);

        // Новости
        $this->db->select('w_news.news_id, news_name, news_date, news_cut, news_url');
        $this->db->from('w_news_categories_cross');
        $this->db->join('w_news', 'w_news.news_id = w_news_categories_cross.news_id');
        $this->db->where('news_cat_id', $id);
        $this->db->where_in('news_active', $statuses);
        $this->db->where('news_date <=', date('Y-m-d H:i:00'));
        $this->db->where('news_lang_id', LANG);
        $this->db->order_by('news_date', 'desc');
        $this->db->limit($limit,$start);
        $query = $this->db->get();
        //----------------------------------------------------------------------------------
        $this->db->cache_on();

        if ($query->num_rows() > 0)
        {
            $this->load->helper(array('date','text'));
            $thumbs = $this->config->item('cms_news_images');
            $news   = array();

            foreach ($query->result() as $row)
            {
                $news[] = array(
                    'news_id'   => $row->news_id,
                    'news_name' => $row->news_name,
                    'news_url'  => '/post/'.$row->news_url,
                    'news_date' => date_format_rus ( $row->news_date, 'date' ),
                    'news_cut'  => $row->news_cut,
                    'news_img'  => $this->Cms_news->get_img($row->news_id, $row->news_name, 'img-responsive')
                );
            }

            $data = array(
                'news_list_cat'   => $cat_name,
                'news_list'       => $news,
                'news_list_url'   => '/'.$page_url,
                'news_list_pages' => $pages
            );

            if ($view) return $this->load->view('site/'.$view, $data, true);
        }
        else
        {
            header("HTTP/1.0 404 Not Found");
            $data = array(
                'news_error'   => true
            );
            if ($view) return $this->load->view('site/'.$view, $data, true);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем новость
     *
     * @access  private
     * @param   array
     * @return  string
     */
    function _get_news($id, $news)
    {
        $this->load->model('Cms_articles');
        $statuses   = array(1, 2);
		
		// Новость
        $this->db->select('news_id, news_name, news_date, news_url, news_meta_title, news_meta_keywords, news_meta_description');
        $this->db->from('w_news');
        $this->db->where('news_url', $news);
        $this->db->where_in('news_active', $statuses);
        $this->db->where('news_date <=', date('Y-m-d H:i:00'));
        $this->db->where('news_lang_id', LANG);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            $this->load->library('parser');
            $this->load->helper('date');

            if($row->news_meta_title != '') $this->Cms_page->set_title($row->news_meta_title);
            if($row->news_meta_keywords != '') $this->Cms_page->set_keywords($row->news_meta_keywords);
            if($row->news_meta_description != '') $this->Cms_page->set_description($row->news_meta_description);

            // Тексты
            $articles = $this->Cms_articles->get_articles($row->news_id, 'news');
            $sidebar  = '';

            if(isset($articles) && is_array($articles) && isset($articles[1])){
                $this->Cms_page->set_articles(array(0 => array(), 1 => $articles[1]));
            }

            $data = array(
                'news_id'       => $row->news_id,
                'news_name'     => $row->news_name,
                'news_date'     => date_format_rus ( $row->news_date, 'date' ),
                'news_articles' => $articles,
                'news_img'      => $this->Cms_news->get_img($row->news_id, $row->news_name, 'img-responsive')
            );

            // Подключения
            $data = array_merge_recursive((array)$data, (array)$this->Cms_inclusions->get_inclusions($row->news_id, 'news'));

            return $this->load->view('site/news_content', $data, true);
        }
        else
        {
            header("HTTP/1.0 404 Not Found");
            $data = array(
                'news_error'   => true
            );
            return $this->load->view('site/news_content', $data, true);
        }
    }
}
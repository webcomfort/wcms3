<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода последних новостей
 */

class Mod_news extends CI_Model {

	private $segment;
	private $base_url;

	function __construct()
    {
        parent::__construct();
        $this->load->model('Cms_news');
	    $this->load->model('Cms_tags');
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
	    $this->segment = $this->Cms_page->get_page_segment();
	    $this->base_url = $this->Cms_page->get_base_url();

	    $id = (isset($params[0])) ? intval($params[0]) : false;

	    if (!$this->uri->segment($this->segment+1) || ($this->uri->segment($this->segment+1) && preg_int ($this->uri->segment($this->segment+1)))) $output = $this->_get_list($id);
	    if ($this->uri->segment($this->segment+1) && !preg_int($this->uri->segment($this->segment+1)) && preg_ext_string ($this->uri->segment($this->segment+1))) $output = $this->_get_news($id, $this->uri->segment($this->segment+1));

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

        if($page == 1 && $pages > $page) $this->Cms_page->add_head('<link rel="next" href="http://'.$_SERVER['HTTP_HOST'].$this->base_url.'/'.$limit.'">'."\r\n");
        if($pages == $page)
        {
            if($page == 2) $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].$this->base_url.'">'."\r\n");
			elseif($page == 1) {}
            else $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].$this->base_url.'/'.(($page*$limit) - (2*$limit)).'">'."\r\n");
        }
        if($page != 1 && $pages != $page && $pages > $page)
        {
            if($page == 2)
            {
                $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].$this->base_url.'">'."\r\n");
                if($pages > $page) $this->Cms_page->add_head('<link rel="next" href="http://'.$_SERVER['HTTP_HOST'].$this->base_url.'/'.($page*$limit).'">'."\r\n");
            }
            else
            {
                $this->Cms_page->add_head('<link rel="prev" href="http://'.$_SERVER['HTTP_HOST'].$this->base_url.'/'.(($page*$limit) - (2*$limit)).'">'."\r\n");
                $this->Cms_page->add_head('<link rel="next" href="http://'.$_SERVER['HTTP_HOST'].$this->base_url.'/'.($page*$limit).'">'."\r\n");
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
	    if($this->input->get('tag', true) && preg_int($this->input->get('tag', true))){
		    $tag = $this->Cms_tags->get_items_by_tag($this->input->get('tag', true),'news', 'w_news', 'news_id', array(
			    'news_active' => 1,
			    'news_lang_id' => LANG,
			    'w_news_categories_cross.news_cat_id' => $id,
		    ), array(
			    'news_date' => 'desc',
		    ), array(
			    'table' => 'w_news_categories_cross',
			    'field' => 'news_id',
		    ));
		    $tag_items = $tag['result'];
		    $tag_name  = $tag['name'];
		    $tag_title = ', '.lang('tags_with').' '.$tag_name;
		    $this->Cms_page->add_title($tag_title);
	    } else {
		    $tag_items = array();
		    $tag_name  = false;
		    $tag_title = '';
	    }

    	$page_url   = $this->base_url;
        $cat        = $this->Cms_news->get_cat_params($id);
        $cat_name   = $cat['name'];
        $views      = $this->config->item('cms_news_views');
        $view       = $views[$cat['view']]['file'];
        $limit      = $this->config->item('cms_news_limit');
		$statuses   = array(1);

        // Страницы
        $this->db->select('w_news.news_id');
        $this->db->from('w_news_categories_cross');
        $this->db->join('w_news', 'w_news.news_id = w_news_categories_cross.news_id', 'left');
        $this->db->where('news_cat_id', $id);
        $this->db->where('news_active', 1);
		$this->db->where_in('news_active', $statuses);
		if(is_array($tag_items) && count($tag_items)) $this->db->where_in('w_news.news_id', $tag_items);
        $this->db->where('news_date <=', date('Y-m-d H:i:00'));
        $this->db->where('news_lang_id', LANG);

        $this->db->cache_off();
        //----------------------------------------------------------------------------------
        $query_count = $this->db->get();
		$count_rows = $query_count->num_rows();

        $this->load->library('pagination');
        $config['base_url']     = $page_url;
        $config['total_rows']   = $query_count->num_rows();
        $config['per_page']     = $limit;
        $config['first_link']   = $this->lang->line('pagination_first_link');
        $config['last_link']    = $this->lang->line('pagination_last_link');
        $config['next_link']    = $this->lang->line('pagination_next_link');
        $config['prev_link']    = $this->lang->line('pagination_prev_link');
        $config['uri_segment']  = $this->segment + 1;
	    $config['reuse_query_string'] = TRUE;
	    $config['attributes']   = array('class' => 'page-link');
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_links();

        // Лимиты и мета
        $start = (preg_int ($this->uri->segment($this->segment + 1))) ? $this->uri->segment($this->segment + 1) : 0;
	    $description = $this->Cms_page->get_description();
	    $this->Cms_page->set_description(($description != '') ? $description : $this->Cms_page->get_title() . ' | '.$this->lang->line('news_page').' '.(ceil(($start/$limit)+1)).' / '.ceil($count_rows/$limit));
        if($start) $this->Cms_page->add_title(' - '.$this->lang->line('news_page').' '.(ceil(($start/$limit)+1)));
        $this->_set_next_prev($start, $limit, $count_rows);
	    $this->Cms_page->set_canonical('');

        // Новости
        $this->db->select('w_news.news_id, news_name, news_date, news_cut, news_url');
        $this->db->from('w_news_categories_cross');
        $this->db->join('w_news', 'w_news.news_id = w_news_categories_cross.news_id', 'left');
        $this->db->where('news_cat_id', $id);
        $this->db->where_in('news_active', $statuses);
        $this->db->where('news_date <=', date('Y-m-d H:i:00'));
        $this->db->where('news_lang_id', LANG);
	    if(is_array($tag_items) && count($tag_items)) $this->db->where_in('w_news.news_id', $tag_items);
        $this->db->order_by('news_date', 'desc');
        $this->db->limit($limit,$start);
        $query = $this->db->get();
        //----------------------------------------------------------------------------------
        $this->db->cache_on();

        if ($query->num_rows() > 0)
        {
            $this->load->helper(array('date','text'));
            $news   = array();

            foreach ($query->result() as $row)
            {
	            $image = $this->Cms_page->get_img($row->news_id, $row->news_name, $this->config->item('cms_news_images'), $this->config->item('cms_news_dir'), 'img-fluid' );
            	$news[] = array(
                    'news_id'   => $row->news_id,
                    'news_name' => $row->news_name,
                    'news_url'  => $page_url.'/'.$row->news_url,
                    'news_date' => date_format_rus ( $row->news_date, 'date' ),
                    'news_cut'  => $row->news_cut,
                    'news_img'  => $image['_big']['img'],
	                'news_tags' => $this->Cms_tags->get_tags_by_item($row->news_id,'news')
                );
            }

            $data = array(
                'news_list_cat'   => $cat_name,
                'news_list'       => $news,
                'news_list_url'   => $page_url,
                'news_list_pages' => $pages,
	            'tag_name'        => $tag_name
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
        $this->db->select('w_news.news_id, news_name, news_date, news_url, news_meta_title, news_meta_keywords, news_meta_description, news_cat_id');
        $this->db->from('w_news');
	    $this->db->join('w_news_categories_cross', 'w_news.news_id = w_news_categories_cross.news_id', 'left');
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

	        $news_page = $this->Cms_news->get_news_page($row->news_cat_id);
	        $news_cat  = $this->Cms_news->get_cat_params($row->news_cat_id);

            if($row->news_meta_title != '') $this->Cms_page->set_title($row->news_meta_title);
            if($row->news_meta_keywords != '') $this->Cms_page->set_keywords($row->news_meta_keywords);
            if($row->news_meta_description != '') $this->Cms_page->set_description($row->news_meta_description);
	        $this->Cms_page->set_name($this->lang->line('news_name'));
	        if($row->news_name != '') $this->Cms_page->add_crumbs(Array(
		        'page_id' => $row->news_id,
		        'page_pid' => 0,
		        'page_name' => $row->news_name,
		        'page_url' => $news_page.'/'.$row->news_url,
	        ));
	        $this->Cms_page->set_canonical(substr($news_page, 1).'/'.$news);

            // Тексты
            $articles = $this->Cms_articles->get_articles($row->news_id, 'news');
            if(isset($articles) && is_array($articles) && isset($articles[1])){
                $this->Cms_page->set_articles(array(0 => array(), 1 => $articles[1]));
            }

	        $image = $this->Cms_page->get_img($row->news_id, $row->news_name, $this->config->item('cms_news_images'), $this->config->item('cms_news_dir'), 'img-fluid' );

            $data = array(
                'news_id'       => $row->news_id,
                'news_name'     => $row->news_name,
                'news_date'     => date_format_rus ( $row->news_date, 'date' ),
                'news_articles' => $articles,
                'news_img'      => $image['_big']['img'],
                'news_tags'     => $this->Cms_tags->get_tags_by_item($row->news_id,'news'),
	            'news_list_url' => $news_page
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
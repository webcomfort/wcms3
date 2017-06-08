<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Контроллер для страниц */

class Page extends CI_Controller {

    function index()
    {
        // Профилирование
        if ($this->config->item('cms_site_profiling')) $this->output->enable_profiler(TRUE);

        // Функции работы с пользователями и сессии
        $this->load->model('cms_user');
        $this->load->library('session');

        // ------------------------------------------------------------------------

        // Страница
        if(!$this->uri->segment(1) || $this->uri->segment(1) == '-')
        {
            $this->_build_page($this->_get_page_params());
        }
        else
        {
            if(preg_ext_string ($this->uri->segment(1)))
            {
                $this->_build_page($this->_get_page_params($this->uri->segment(1)));
            }
            else
            {
                $this->_build_page($this->_e_404());
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
	 * Получаем параметры страницы
	 *
	 * @access	private
     * @param   string
	 * @return	string
	 */
    function _get_page_params($url = false)
    {
        if($url)
        {
            $statuses = array(1, 2);

            $this->db->where('page_url', $url);
            $this->db->where_in('page_status', $statuses);
            $query = $this->db->get('w_pages');

            if ($query->num_rows() > 0)
            {
                $row = $query->row();
                return $row;
            }
            else
            {
                return $this->_e_404();
            }
        }
        else
        {
            $langs      = $this->config->item('cms_lang');
            $lang_id    = current(array_keys($langs));

			$statuses = array(1, 2);

            $this->db->where('page_lang_id', $lang_id);
			$this->db->where('page_menu_id', 1);
            $this->db->where_in('page_status', $statuses);
			$this->db->order_by('page_sort', 'asc');
            $query = $this->db->get('w_pages', 1);

            if ($query->num_rows() > 0)
    		{
            	$row = $query->row();
                return $row;
            }
            else
            {
                show_error('This site has no index page!', 404);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * 404
     *
     * @access  private
     * @param   string
     * @return  string
     */
    function _e_404()
    {
        $this->db->where('page_url', '404');
        $query = $this->db->get('w_pages');

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            header("HTTP/1.0 404 Not Found");
            return $row;
        }
        else
        {
            show_404();
        }
    }

    // ------------------------------------------------------------------------

    /**
	 * Строим страницу
	 *
	 * @access	private
     * @param   array
	 * @return	int
	 */
    function _build_page($params)
    {
        // Редирект
        if($params->page_redirect) { header("Location: ".$params->page_redirect); exit; }

        // Вспомогательные функции
        $this->load->model('Cms_page');
        $this->load->model('Cms_inclusions');
        $this->load->model('Cms_articles');

        // Установка id активной страницы
        define('PAGE_ID', $params->page_id);
        // Установка id активного меню
        define('MENU_ID', $params->page_menu_id);
		// Установка url
		define('PAGE_URL', $params->page_url);
        // Установка имени
        define('PAGE_NAME', $params->page_name);

        // Установка языка
        $langs = $this->config->item('cms_lang');

        define('LANG', $params->page_lang_id);
        define('LANGF', $langs[$params->page_lang_id]['folder']);

        $this->lang->load('cms', $langs[$params->page_lang_id]['folder']);

        // Макеты
        $views      = $this->config->item('cms_site_views');
        $view       = $views[$params->page_view_id];
		
		// Параметры страницы
        $this->Cms_page->set_title($params->page_meta_title);
		$this->Cms_page->set_link_title($params->page_link_title);
        $this->Cms_page->set_keywords($params->page_meta_keywords);
        $this->Cms_page->set_description($params->page_meta_description);
        $this->Cms_page->set_head($params->page_meta_additional);
		$this->Cms_page->set_foot($params->page_footer_additional);
		$this->Cms_page->set_canonical($this->uri->uri_string());

        $this->db->cache_on();

        $data['page_name'] = $params->page_name;

        // Тексты
        $data['page_articles'] = $this->Cms_articles->get_articles($params->page_id, 'pages');

        // Подключения
        $data = array_merge_recursive((array)$data, (array)$this->Cms_inclusions->get_inclusions($params->page_id, 'pages'));

        $data['page_title']        = $this->Cms_page->get_title();
		$data['page_link_title']   = $this->Cms_page->get_link_title();
        $data['page_keywords']     = $this->Cms_page->get_keywords();
        $data['page_description']  = $this->Cms_page->get_description();
        $data['page_head']         = $this->Cms_page->get_head();
		$data['page_foot']         = $this->Cms_page->get_foot();
		$data['page_canonical']    = $this->Cms_page->get_canonical();

        $this->load->view('site/'.$view['header'], $data);
        $this->load->view('site/'.$view['file'], $data);
        $this->load->view('site/'.$view['footer'], $data);

        $this->db->cache_off();
    }
}
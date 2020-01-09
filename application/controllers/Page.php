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
	    $this->load->model('Cms_page');

        // ------------------------------------------------------------------------

        // Страница
        if(!$this->uri->segment(1) || $this->uri->segment(1) == '-')
        {
            $this->_build_page($this->_get_page_params());
        }
        else
        {
	        $segment = $this->Cms_page->page_segment();

	        if(preg_ext_string ($this->uri->segment($segment)))
            {
                $this->_build_page($this->_get_page_params($this->uri->segment($segment)));
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
            $statuses = array(1, 2, 4);

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

			$statuses = array(1, 2, 4);

            $this->db->where('page_lang_id', $lang_id);
			$this->db->where('page_menu_id', 1);
            $this->db->where('page_pid', 0);
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
	 * Форма авторизации
	 *
	 * @access	private
	 * @return	void
	 */
	function _get_login_form($mode,$error=0,$hash='',$view)
	{
		$data = array(
			'mode'  => $mode,
			'error' => $error,
			'hash'  => $hash
		);

		$time = time();
		header("Expires: " . gmdate("D, d M Y H:i:s", $time) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $time) . " GMT");
		header("Cache-Control: no-cache, must-revalidate");

		$this->load->view('site/'.$view['header'], $data);
		$this->load->view('site/page_login', $data);
		$this->load->view('site/'.$view['footer'], $data);
	}

	/**
	 * Проверяем капчу
	 *
	 * @access	private
	 * @param   string
	 * @return	bool
	 */
	function recaptcha($str='')
	{
		if($this->cms_user->recaptcha($str))
		{
			return true;
		}
		else
		{
			$this->form_validation->set_message('recaptcha', lang('cms_user_error_16'));
			return false;
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
	    $segs = $this->uri->segment_array();
	    if (in_array("exit", $segs)) {
	    	$this->cms_user->quit();
	    	$loc = ($this->uri->segment( 1 ) != '-') ? '/'.$this->uri->segment( 1 ) : '/';
		    header( 'Location: ' . $loc );
	    }

    	// Редирект
        if($params->page_redirect) { header("Location: ".$params->page_redirect); exit; }

	    // Установка языка
	    $langs = $this->config->item('cms_lang');
	    define('LANG', $params->page_lang_id);
	    define('LANGF', $langs[$params->page_lang_id]['folder']);
	    $this->lang->load('cms', $langs[$params->page_lang_id]['folder']);

	    // Макеты
	    $views      = $this->config->item('cms_site_views');
	    $view       = $views[$params->page_view_id];

	    // Установка id активной страницы
	    define('PAGE_ID', $params->page_id);
	    // Установка id активного меню
	    define('MENU_ID', $params->page_menu_id);
	    // Установка url
	    ($this->uri->segment(1) != '' && $this->uri->segment(1) != '-') ? define('PAGE_URL', $params->page_url) : define('PAGE_URL', '-');
	    // Установка имени
	    define('PAGE_NAME', $params->page_name);

	    // Авторизация или страница
        if($params->page_status == 4 && !$this->cms_user->get_user_id()){

	        // Форма авторизации
	        if (!in_array("remember", $segs) && !in_array("change", $segs)) {
		        $this->load->library( 'form_validation' );

		        $this->form_validation->set_rules( 'w_login', lang( 'cms_user_form_1' ), 'trim|required|valid_email' );
		        $this->form_validation->set_rules( 'w_pass', lang( 'cms_user_form_2' ), 'trim|required|alpha_dash' );
		        $this->form_validation->set_rules( 'g-recaptcha-response', lang( 'cms_user_form_12' ), 'callback_recaptcha' );

		        $this->form_validation->set_message( 'required', lang( 'cms_user_error_9' ) );
		        $this->form_validation->set_message( 'valid_email', lang( 'cms_user_error_2' ) );
		        $this->form_validation->set_message( 'alpha_dash', lang( 'cms_user_error_3' ) );

		        if ( $this->form_validation->run() == false ) {
			        $this->_get_login_form( 1, 0, '', $view );
		        } else {
			        ( $this->input->post( 'w_remember' ) == 1 ) ? $remember = true : $remember = false;
			        $this->cms_user->login( $this->input->post( 'w_login' ), $this->input->post( 'w_pass' ), $remember );

			        if ( $this->cms_user->get_user_id() ) {
				        $loc = ($this->uri->segment( 1 ) != '-') ? '/'.$this->uri->segment( 1 ) : '/';
				        header( 'Location: ' . $loc );
			        } else {
				        $this->_get_login_form( 1, 1, '', $view );
			        }
		        }
	        }

	        // ------------------------------------------------------------------------

	        // Вспомнить пароль
	        if (in_array("remember", $segs))
	        {
		        if($this->uri->segment(3) && preg_hash($this->uri->segment(3)))
		        {
			        if($this->cms_user->check_hash($this->uri->segment(3)))
			        {
				        $this->_get_login_form(4,0, $this->uri->segment(3), $view);
			        }
			        else
			        {
				        $this->_get_login_form(2,11, '', $view);
			        }
		        }

		        if(!$this->uri->segment(3))
		        {
			        $this->load->library('form_validation');

			        $this->form_validation->set_rules('w_email', lang('cms_user_form_1'), 'trim|required|valid_email');
			        $this->form_validation->set_rules('g-recaptcha-response', lang('cms_user_form_12'),'callback_recaptcha');

			        $this->form_validation->set_message('required', lang('cms_user_error_9'));
			        $this->form_validation->set_message('valid_email', lang('cms_user_error_2'));

			        if ($this->form_validation->run() == FALSE)
			        {
				        $this->_get_login_form(2, 0, '', $view);
			        }
			        else
			        {
			        	if($this->cms_user->remember_confirmation($this->input->post('w_email'), $this->uri->segment( 1 )))
				        {
					        $this->_get_login_form(3,10, '', $view);
				        }
				        else
				        {
					        $this->_get_login_form(2,6, '', $view);
				        }
			        }
		        }
	        }

	        // ------------------------------------------------------------------------

	        // Изменить пароль
	        if (in_array("change", $segs))
	        {
		        $this->load->library('form_validation');

		        $this->form_validation->set_rules('w_pass_new', lang('cms_user_form_2'), 'trim|required|alpha_dash|min_length[6]',
			        array(
				        'required'      => lang('cms_user_error_9'),
				        'alpha_dash'    => lang('cms_user_error_3'),
				        'min_length'    => lang('cms_user_error_14')
			        ));
		        $this->form_validation->set_rules('w_pass_confirm', lang('cms_user_form_2'), 'trim|matches[w_pass_new]',
			        array(
				        'matches'       => lang('cms_user_error_13')
			        ));
		        $this->form_validation->set_rules('g-recaptcha-response', lang('cms_user_form_12'),'callback_recaptcha');

		        if ($this->form_validation->run() == FALSE)
		        {
			        if($this->input->post('w_hash') && preg_hash($this->input->post('w_hash')) && $this->cms_user->check_hash($this->input->post('w_hash'))) $this->_get_login_form(4,0, $this->input->post('w_hash'), $view);
			        else {
				        $loc = ($this->uri->segment( 1 ) != '-') ? '/'.$this->uri->segment( 1 ) : '/';
				        header( 'Location: ' . $loc );
			        }
		        }
		        else
		        {
			        if($this->cms_user->password_change($this->input->post('w_hash'), $this->input->post('w_pass_new'))) $this->_get_login_form(3,12, '', $view);
			        else $this->_get_login_form(4,15, $this->input->post('w_hash'), $view);
		        }
	        }

        } else {

	        // Вспомогательные функции
	        $this->load->model('Cms_inclusions');
	        $this->load->model('Cms_articles');

	        // Параметры страницы
	        $this->Cms_page->set_crumbs($this->config->item('cms_site_crumbs'));
	        $this->Cms_page->set_name($params->page_name);
	        $this->Cms_page->set_title($params->page_meta_title);
	        $this->Cms_page->set_link_title($params->page_link_title);
	        $this->Cms_page->set_keywords($params->page_meta_keywords);
	        $this->Cms_page->set_description($params->page_meta_description);
	        $this->Cms_page->set_head($params->page_meta_additional);
	        $this->Cms_page->set_foot($params->page_footer_additional);
	        $this->Cms_page->set_canonical($this->uri->uri_string());
	        $this->Cms_page->set_articles($this->Cms_articles->get_articles($params->page_id, 'pages', $params->page_view_id));

	        $this->db->cache_on();

	        $data = array();

	        // Подключения
	        $data = array_merge_recursive((array)$data, (array)$this->Cms_inclusions->get_inclusions($params->page_id, 'pages'));
	        $data['page_crumbs']       = $this->load->view('site/menu_crumbs', array('crumbs_array'=>$this->Cms_page->get_crumbs()), true);
	        $data['page_base_url']     = $this->Cms_page->get_base_url();
	        $data['page_name']         = $this->Cms_page->get_name();
	        $data['page_title']        = $this->Cms_page->get_title();
	        $data['page_link_title']   = $this->Cms_page->get_link_title();
	        $data['page_keywords']     = $this->Cms_page->get_keywords();
	        $data['page_description']  = $this->Cms_page->get_description();
	        $data['page_head']         = $this->Cms_page->get_head();
	        $data['page_foot']         = $this->Cms_page->get_foot();
	        $data['page_canonical']    = $this->Cms_page->get_canonical();
	        $data['page_articles']     = $this->Cms_page->get_articles();

	        $this->load->view('site/'.$view['header'], $data);
	        $this->load->view('site/'.$view['file'], $data);
	        $this->load->view('site/'.$view['footer'], $data);

	        $this->db->cache_off();

        }
    }
}

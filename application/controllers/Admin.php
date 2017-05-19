<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Контроллер администрирования */

class Admin extends CI_Controller {

    private $lang_array = array();

    function index()
    {
        // Миграция
        if($this->uri->segment(2) == 'migrate')
        {
            $this->load->library('migration');

            if ($this->migration->current() === FALSE)
            {
                show_error($this->migration->error_string());
            }
        }

        // Функции работы с пользователями и сессии
        $this->load->model('cms_user');
        $this->load->library('session');

        // Профилирование
        if ($this->config->item('cms_admin_profiling')) $this->output->enable_profiler(TRUE);

        // Языки cайта
        $this->lang_array = $this->config->item('cms_lang');

        // Язык для модуля cms_user (берем системный)
        $this->lang->load('cms');

        // ------------------------------------------------------------------------

        // Форма авторизации
        if(!$this->uri->segment(2) || $this->uri->segment(2) == 'exit')
        {
            if($this->uri->segment(2) == 'exit') $this->cms_user->quit();

            $this->load->library('form_validation');

            $this->form_validation->set_rules('w_login', lang('cms_user_form_1'), 'trim|required|valid_email');
            $this->form_validation->set_rules('w_pass', lang('cms_user_form_2'), 'trim|required|alpha_dash');

            $this->form_validation->set_message('required', lang('cms_user_error_9'));
            $this->form_validation->set_message('valid_email', lang('cms_user_error_2'));
            $this->form_validation->set_message('alpha_dash', lang('cms_user_error_3'));

            if ($this->form_validation->run() == FALSE)
            {
                $this->_get_login_form(1);
            }
            else
            {
                ($this->input->post('w_remember') == 1) ? $remember = true : $remember = false;

                $this->cms_user->login($this->input->post('w_login'), $this->input->post('w_pass'), $remember);

                if($this->cms_user->get_user_id())
                {
                    if ($this->cms_user->get_user_rights()) header ('Location: /admin/'.$this->_get_start_page_id(0));
                    else $this->_get_login_form(1,4);
                }
                else
                {
                    $this->_get_login_form(1,1);
                }
            }
        }

        // ------------------------------------------------------------------------

        // Вспомнить пароль
        if($this->uri->segment(2) == 'remember')
        {
            if($this->uri->segment(3) && preg_hash($this->uri->segment(3)))
            {
                if($this->cms_user->password_reset($this->uri->segment(3)))
                {
                    $this->_get_login_form(3,8);
                }
                else
                {
                    $this->_get_login_form(2,11);
                }
            }

            if(!$this->uri->segment(3))
            {
                $this->load->library('form_validation');

                $this->form_validation->set_rules('w_email', lang('cms_user_form_1'), 'trim|required|valid_email');

                $this->form_validation->set_message('required', lang('cms_user_error_9'));
                $this->form_validation->set_message('valid_email', lang('cms_user_error_2'));

                if ($this->form_validation->run() == FALSE)
                {
                    $this->_get_login_form(2);
                }
                else
                {
                    if($this->cms_user->remember_confirmation($this->input->post('w_email')))
                    {
                        $this->_get_login_form(3,10);
                    }
                    else
                    {
                        $this->_get_login_form(2,6);
                    }
                }
            }
        }

        // ------------------------------------------------------------------------

        // Если пользователь авторизован и находится в группе с администраторскими правами, но не
        // передан id страницы
        if ($this->cms_user->get_user_id() && !$this->uri->segment(2) && $this->cms_user->get_group_admin() && $this->cms_user->get_user_rights())
        {
            header ('Location: /admin/'.$this->_get_start_page_id(0));
        }

        // ------------------------------------------------------------------------

        // Если пользователь авторизован, находится в группе с администраторскими правами и
        // передан id страницы
        if ($this->cms_user->get_user_id() && preg_int($this->uri->segment(2)) && $this->cms_user->get_group_admin() && $this->cms_user->get_user_rights())
        {
            // Если языковая сессия отсутствует - ищем язык по умолчанию
            if (!$this->session->userdata('w_alang')) $this->_set_default_admin_lang();

            // Если смена языка
            if (
                $this->session->userdata('w_alang') &&
                $this->uri->segment(3) == 'lang' &&
                preg_int ($this->uri->segment(4)) &&
                $this->_is_lang($this->uri->segment(4))
            )
            {
                $this->_change_admin_lang($this->uri->segment(4));
            }

            // Если передан параметр для очистки кэша
            if($this->uri->segment(3) && $this->uri->segment(3) == 'clear') $this->db->cache_delete_all();

            $this->_get_page($this->uri->segment(2));
        }

        // ------------------------------------------------------------------------

        // Если ничего из вышеуказанного не подходит
        if (!preg_int($this->uri->segment(2)) && $this->uri->segment(2) != 'exit' && $this->uri->segment(2) != 'remember')
        {
            if($this->cms_user->get_user_id() && $this->cms_user->get_group_admin() && $this->cms_user->get_user_rights())
            {
                header ('Location: /admin/'.$this->_get_start_page_id(0));
            }
            else
            {
                header ('Location: /admin/');
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
	 * Отдаем страницу
	 *
	 * @access	private
     * @param   int
	 * @return	string
	 */
    function _get_page($id)
    {
        $this->db->select('cms_page_name, cms_page_status, cms_page_model_id, cms_page_view_id, module_file, module_name');
        $this->db->from('w_cms_pages');
        $this->db->join('w_cms_modules', 'w_cms_modules.module_id = w_cms_pages.cms_page_model_id');
        $this->db->where('cms_page_id =', $id);
        $this->db->where('cms_page_status !=', 0);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
		{
        	$row = $query->row();

            $views  = $this->config->item('cms_admin_views');

            $model_file = $row->module_file;
            $model      = basename($row->module_file, ".php");
            $view       = @$views[$row->cms_page_view_id]['file'];

            $user_rights = $this->cms_user->get_user_rights();
            $user_rights = @$user_rights[$model_file];

            if(is_file(APPPATH.'models/'.$model_file) && ($user_rights['view'] == 1 || $user_rights['add'] == 1 || $user_rights['edit'] == 1 || $user_rights['copy'] == 1 || $user_rights['delete'] == 1))
            {
                $this->load->model($model);

                $data = array(
                    'user_name'         => $this->cms_user->get_user_name(),
                    'admin_page_id'     => $id,
                    'admin_meta'        => $this->$model->get_meta(),
                    'admin_name'        => $row->module_name,
                    'admin_filters'     => $this->$model->get_filters(),
                    'admin_interface'   => $this->$model->get_output(),
                    'admin_langs'       => $this->lang_array,
                    'admin_active_lang' => $this->session->userdata('w_alang')
                );

                $this->load->view('admin/page_header', $data);
                $this->load->view('admin/'.$view, $data);
                $this->load->view('admin/page_footer', $data);
            }
            else
            {
                header ('Location: /admin/'.$this->_get_start_page_id(0));
            }
        }
        else
        {
            header ('Location: /admin/'.$this->_get_start_page_id(0));
        }
    }

    // ------------------------------------------------------------------------

    /**
	 * Ищем стартовую страницу
	 *
	 * @access	private
     * @param   int
	 * @return	int
	 */
    function _get_start_page_id($pid)
    {
        $this->db->select('cms_page_id, cms_page_status');
        $this->db->from('w_cms_pages');
        $this->db->join('w_user_rules', 'w_cms_pages.cms_page_model_id = w_user_rules.rule_model_id');
        if ($pid) $this->db->where('cms_page_pid =', $pid);
        $this->db->where('cms_page_status !=', 0);
        $this->db->where('rule_user_id', $this->cms_user->get_user_id());
        $this->db->where('(rule_view = 1 OR rule_add = 1 OR rule_edit = 1 OR rule_copy = 1 OR rule_delete = 1)');
        $this->db->order_by('cms_page_pid', 'asc');
        $this->db->order_by('cms_page_sort', 'asc');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
		{
        	$row = $query->row();

            // Если эта страница является переходом на уровень ниже
            if ($row->cms_page_status == 3)
            {
                return $this->_get_start_page_id($row->cms_page_id);
            }
            else
            {
                return $row->cms_page_id;
            }
        }
        else
        {
            $this->cms_user->quit();
            header ('Location: /admin');
        }
    }

    // ------------------------------------------------------------------------

    /**
	 * Форма авторизации
	 *
	 * @access	private
	 * @return	void
	 */
    function _get_login_form($mode,$error=0)
    {
        $data = array(
            'mode' => $mode,
            'error' => $error
        );

        $this->load->view('admin/page_header', $data);
        $this->load->view('admin/login', $data);
        $this->load->view('admin/page_footer', $data);
    }

    // ------------------------------------------------------------------------

    /**
     * Язык по умолчанию
     *
     * @access	private
     * @return	void
     */
    function _set_default_admin_lang()
    {
        if(is_array($this->lang_array)) {
            reset($this->lang_array);
            $key = @array_shift(@array_keys($this->lang_array));

            $data = array(
                'w_alang'   => $key,
                'w_alang_f' => $this->lang_array[$key]['folder']
            );

            $this->session->set_userdata($data);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Смена языка
     *
     * @access	private
     * @param   int
     * @return	void
     */
    function _change_admin_lang($id)
    {
        reset($this->lang_array);
        $key = $id;

        $data = array(
            'w_alang'   => $key,
            'w_alang_f' => $this->lang_array[$key]['folder']
        );

        $this->session->set_userdata($data);
    }

    // ------------------------------------------------------------------------

    /**
     * Проверяем существование языка
     *
     * @access	private
     * @param   int
     * @return	bool
     */
    function _is_lang($id)
    {
        reset($this->lang_array);
        if (array_key_exists($id, $this->lang_array)) return true;
        else return false;
    }
}
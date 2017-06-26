<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Операции авторизации и проверки пользователя
 */

class Cms_user extends CI_Model {

    private $user_id            = false;
    private $user_email         = false;
    private $user_name          = false;
    private $user_nic           = false;
    private $user_name_pref     = false;
    private $user_rights        = false;
    private $user_modules       = false;
    private $user_myedit_rights = false;
    private $group_id           = false;
    private $group_admin        = false;

    function __construct()
    {
        parent::__construct();

        // Если обнаружена кука
        if (get_cookie('w_user')) $this->_check_user();
    }

    // ------------------------------------------------------------------------

    /**
     * Проверяем куку и находим по ней все данные о пользователе
     *
     * @access	private
     * @return	void|bool
     */
    function _check_user()
    {
        $this->load->library('user_agent');

        if (preg_hash(get_cookie('w_user', TRUE)))
        {
            $this->db->select('user_id, user_email, user_group_id, user_name, user_second_name, user_surname, user_nic, user_name_pref');
            $this->db->from('w_user');
            $this->db->where('user_hash', get_cookie('w_user', TRUE));
            if($this->config->item('cms_user_ip')) $this->db->where('user_ip', $this->input->ip_address());
            if($this->config->item('cms_user_agent')) $this->db->where('user_agent', $this->agent->agent_string());
            $this->db->where('user_active', 1);
            $query = $this->db->get();

            $groups = $this->config->item('cms_user_groups');

            if ($query->num_rows() > 0)
            {
                $row = $query->row();

                if ($groups[$row->user_group_id]['active'])
                {
                    if ($groups[$row->user_group_id]['admin']) $this->group_admin = true;

                    $second_name = ($row->user_second_name != '') ? $row->user_second_name . ' ' : '';

                    $this->user_id           = $row->user_id;
                    $this->user_email        = $row->user_email;
                    $this->user_name         = $row->user_name . ' ' . $second_name . $row->user_surname;
                    $this->user_nic          = ($row->user_nic != '') ? $row->user_nic : false;
                    $this->user_name_pref    = $row->user_name_pref;
                    $this->group_id          = $row->user_group_id;

                    // Получаем права для этого пользователя
                    $this->_get_user_rights();
                }
                else
                {
                    $this->quit();
                }
            }
            else
            {
                $this->quit();
            }
        }
        else
        {
            $this->quit();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Авторизуем пользователя
     *
     * @access	public
     * @param   string
     * @param   string
     * @param   int
     * @return	bool
     */
    function login($login, $pass, $remember)
    {
        $this->load->library('user_agent');

        $this->db->select('user_id, user_group_id, user_name, user_second_name, user_surname, user_nic, user_name_pref');
        $this->db->from('w_user');
        $this->db->where('user_email', $login);
        $this->db->where('user_pass', md5(md5($pass)));
        $this->db->where('user_active', 1);
        $query = $this->db->get();

        $groups = $this->config->item('cms_user_groups');

        if ($query->num_rows() > 0)
		{
        	$row = $query->row();

			if ($groups[$row->user_group_id]['active'])
            {
                if ($groups[$row->user_group_id]['admin']) $this->group_admin = true;

                $this->user_id           = $row->user_id;
                $this->user_email        = $login;
                $this->user_name         = $row->user_name . ' ' . ($row->user_second_name != '') ? $row->user_second_name : '' . $row->user_surname;
                $this->user_nic          = ($row->user_nic != '') ? $row->user_nic : false;
                $this->user_name_pref    = $row->user_name_pref;
                $this->group_id          = $row->user_group_id;

                // Добавляем хэш, ip, agent и создаем куку
                $hash = md5(uniqid().time());

                $data['user_hash'] = $hash;
                if($this->config->item('cms_user_ip'))    $data['user_ip'] = $this->input->ip_address();
                if($this->config->item('cms_user_agent')) $data['user_agent'] = $this->agent->agent_string();

                $this->db->where('user_id', $this->user_id);
                $this->db->update('w_user', $data);

                if ($remember) $expire = 2592000; // месяц
                else $expire = 0;

                $cookie = array(
                   'name'   => 'w_user',
                   'value'  => $hash,
                   'expire' => $expire
                );
                set_cookie($cookie);

                // Получаем права для этого пользователя
                $this->_get_user_rights();

                return true;
            }
            else
            {
                return false;
            }
        }
		else
		{
        	return false;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Выход
     *
     * @access	public
     * @return	void
     */
    function quit()
    {
        delete_cookie("w_user");
    }

    // ------------------------------------------------------------------------

    /**
     * Получаем права пользователя
     *
     * @access	private
     * @return	void
     */
    function _get_user_rights()
    {
        if ($this->group_admin)
        {
            $this->db->select('rule_model_id, rule_view, rule_add, rule_edit, rule_copy, rule_delete, rule_active, module_file');
            $this->db->from('w_user_rules');
            $this->db->join('w_cms_modules', 'w_cms_modules.module_id = w_user_rules.rule_model_id');
            $this->db->where('rule_user_id', $this->user_id);
            $query = $this->db->get();

            if ($query->num_rows() > 0)
            {
                $this->user_rights = array();
                $this->user_modules = array();
                $this->user_myedit_rights = array();

                foreach ($query->result() as $row)
                {
                    if ($row->rule_view == 1 || $row->rule_add == 1 || $row->rule_edit == 1 || $row->rule_copy == 1 || $row->rule_delete == 1 || $row->rule_active == 1)
                    {
                        $model = $row->module_file;

                        $this->user_rights[$model] = array(
                            'view'     => $row->rule_view,
                            'add'      => $row->rule_add,
                            'edit'     => $row->rule_edit,
                            'copy'     => $row->rule_copy,
                            'delete'   => $row->rule_delete,
                            'active'   => $row->rule_active
                        );

                        $this->user_modules[$row->rule_model_id] = $model;

                        // Права специального вида для phpmyedit
                        $rights = '';
                        $rights .= ($row->rule_view)   ? 'V' : '';
                        $rights .= ($row->rule_add)    ? 'A' : '';
                        $rights .= ($row->rule_edit)   ? 'C' : '';
                        $rights .= ($row->rule_copy)   ? 'P' : '';
                        $rights .= ($row->rule_delete) ? 'D' : '';
                        $rights .= 'F';

                        $this->user_myedit_rights[$model] = $rights;
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Отсылает письмо с просьбой подтвердить восстановление пароля
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    function remember_confirmation($email)
    {
        $this->db->select('user_id');
        $this->db->from('w_user');
        $this->db->where('user_email', $email);
        $this->db->where('user_active', 1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
		{
	    	$this->load->library('email');

			$this->email->from('postmaster@'.$_SERVER["HTTP_HOST"], $_SERVER["HTTP_HOST"].' postmaster');
			$this->email->to($email);
			$this->email->subject(lang('cms_user_rem_conf_subj'));

            $hash = md5(uniqid().time());
			$message = sprintf(lang('cms_user_pass_conf_mess'), 'admin', $hash);
			$this->email->message($message);

			if ($this->email->send())
			{
				$data['user_restore_hash'] = $hash;
                $data['user_restore_time'] = date('Y-m-d');

                $this->db->where('user_email', $email);
                $this->db->update('w_user', $data);

                return true;
			}
	    	else
			{
	    		return false;
	    	}
	    }
		else
		{
	    	return false;
	    }
    }

    // ------------------------------------------------------------------------

    /**
     * Проверяет валидность хэша
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    function check_hash($hash)
    {
        $this->db->select('user_id');
        $this->db->from('w_user');
        $this->db->where('user_restore_hash', $hash);
        $this->db->where('user_restore_time', date('Y-m-d'));
        $this->db->where('user_active', 1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) return true;
        else return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Меняет пароль
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    function password_change($hash, $pass)
    {
        if ($this->check_hash($hash))
        {
            $data['user_pass'] = md5(md5($pass));
            $data['user_restore_hash'] = '';
            $data['user_restore_time'] = '0000-00-00';

            $this->db->where('user_restore_hash', $hash);
            $this->db->update('w_user', $data);

            return true;
        }
        else
        {
            return false;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Отсылает письмо с уведомлением об удалении учетной записи
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    function send_delete_message($email)
    {
        $this->load->library('email');

        $this->email->from('postmaster@'.$_SERVER["HTTP_HOST"], $_SERVER["HTTP_HOST"].' postmaster');
        $this->email->to($email);
        $this->email->subject(lang('cms_user_del_subj'));
        $this->email->message(lang('cms_user_del_mess'));

        if ($this->email->send())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает id пользователя
     *
     * @access	public
     * @return	int
     */
    function get_user_id()
    {
        return $this->user_id;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает имя пользователя
     *
     * @access	public
     * @return	string
     */
    function get_user_name()
    {
        if($this->user_name_pref || $this->user_nic === false) return $this->user_name;
        else return $this->user_nic;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает настоящее имя пользователя
     *
     * @access	public
     * @return	string
     */
    function get_real_user_name()
    {
        return $this->user_name;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает ник пользователя
     *
     * @access	public
     * @return	string
     */
    function get_user_nic()
    {
        return $this->user_nic;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает email
     *
     * @access	public
     * @return	string
     */
    function get_user_email()
    {
        return $this->user_email;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает массив прав или FALSE, если прав нет
     *
     * @access	public
     * @return	array|bool
     */
    function get_user_rights()
    {
        return $this->user_rights;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает массив моделей, на которые у пользователя есть права
     *
     * @access	public
     * @return	array|bool
     */
    function get_user_modules()
    {
        return $this->user_modules;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает массив прав для myedit или FALSE, если прав нет
     *
     * @access	public
     * @return	array|bool
     */
    function get_user_myedit_rights()
    {
        return $this->user_myedit_rights;
    }

    // ------------------------------------------------------------------------

    /**
     * Отдает id группы
     *
     * @access	public
     * @return	int
     */
    function get_group_id()
    {
        return $this->group_id;
    }

    // ------------------------------------------------------------------------

    /**
     * Есть ли у группы права администрирования
     *
     * @access	public
     * @return	bool
     */
    function get_group_admin()
    {
        return $this->group_admin;
    }

    // ------------------------------------------------------------------------

    /**
	 * Внешняя функция, проверяющая email
	 *
	 * @access	public
	 * @return	void
	 */
    function p_check_email()
    {
        if(preg_email($this->input->post('email_check', TRUE)))
        {
            $query = $this->db->get_where('w_user', array('user_email' => $this->input->post('email_check', TRUE)));

            if ($query->num_rows() > 0) echo '0';
            else echo '1';
        }
        else
        {
            echo '0';
        }
    }

    // ------------------------------------------------------------------------

    /**
	 * Внешняя функция, проверяющая ник
	 *
	 * @access	public
	 * @return	void
	 */
    function p_check_nic()
    {
        if(preg_match('/^[-a-zA-ZА-Яа-яЁё0-9_\s]*$/u', $this->input->post('nic_check', TRUE)))
        {
            $query = $this->db->get_where('w_user', array('user_nic' => $this->input->post('nic_check', TRUE)));

            if ($query->num_rows() > 0) echo '0';
            else echo '1';
        }
        else
        {
            echo '0';
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция для проверки капчи
     *
     * @access	public
     * @param   string
     * @return	bool
     */
    function recaptcha($response='')
    {
        $google_url = "https://www.google.com/recaptcha/api/siteverify";
        $secret = $this->config->item('cms_recaptcha_secret');
        $ip = $this->input->ip_address();
        $url = $google_url."?secret=".$secret."&response=".$response."&remoteip=".$ip;

        // POST
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $res = curl_exec($curl);
        curl_close($curl);

        $res= json_decode($res, true);
        if($res['success'])
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}
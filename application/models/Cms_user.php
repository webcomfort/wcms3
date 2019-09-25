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
	private $group_files        = false;

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
	                if ($groups[$row->user_group_id]['files']) $this->group_files = true;

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
	            if ($groups[$row->user_group_id]['files']) $this->group_files = true;

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
    function remember_confirmation($email, $place = 'admin')
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
			$message = sprintf(lang('cms_user_pass_conf_mess'), $place, $hash);
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
	 * Есть ли у группы права видеть все файлы
	 *
	 * @access	public
	 * @return	bool
	 */
	function get_group_files()
	{
		return $this->group_files;
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

	// ------------------------------------------------------------------------

	/**
	 * Функция, отдающая административное поле
	 *
	 * @access	public
	 * @param   int - id для выборки
	 * @param   string - тип элемента
	 * @return	array
	 */
	function get_users_field($id, $value)
	{
		$opts = array();
		$users_select = $this->_get_users_select($id, $value);
		$opts['fdd'][$value.'_users'] = array(
			'name'     => 'Пользователи',
			'nodb'     => true,
			'select'   => 'M',
			'options'  => 'ACP',
			'add_display'   => $users_select,
			'change_display'=> $users_select,
			'cell_func' => array(
				'model' => 'cms_user',
				'func'  => 'get_users_select'
			),
			'required' => false,
			'sort'     => false,
			'help'     => 'Выберите из списка пользователей, которые имеют право редактировать этот элемент.'
		);
		return $opts;
	}

	/**
	 * Функция, список выбора для пересечений с пользователями
	 *
	 * @access	private
	 * @param   int - id для выборки
	 * @param   string - тип элемента
	 * @return	string
	 */
	function _get_users_select($key, $value)
	{
		// Получаем массивы с данными для формирования селекта
		$select_array = $this->_item_users($key, $value);
		// Строим селект
		$this->load->helper('form');
		$opts = 'class="select2"';
		return form_multiselect($value.'_users_'.$key.'[]', $select_array['values'], $select_array['defaults'], $opts);
	}

	/**
	 * Функция, отдающая массивы пересечений элементов и пользователей для формирования селекта с дефолтными значениями
	 *
	 * @access	private
	 * @param   int - id для выборки
	 * @param   string - тип элемента
	 * @return	array
	 */
	function _item_users($key, $value)
	{
		$val_arr = $this->_get_item_users();
		$val_arr_active = array();
		$groups = $this->_get_item_groups();

		// No id, new item
		if($key == 0 && in_array($this->get_group_id(), $groups)) {
			$val_arr_active[] = $this->get_user_id();
		}

		// Active
		$this->db->select('wur_user_id AS id')
		         ->from('w_user_rights')
		         ->where('wur_item_id', $key)
		         ->where('wur_type', $value);

		$query  = $this->db->get();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$val_arr_active[] = $row->id;
			}

			$data = array(
				'values'    => $val_arr,
				'defaults'  => $val_arr_active
			);

			return $data;
		}
		else {
			$data = array(
				'values'    => $val_arr,
				'defaults'  => $val_arr_active
			);
			return $data;
		}
	}

	/**
	 * Функция, отдающая массив пользователей, которым нужны права для работы с элементами
	 *
	 * @access  public
	 * @return  array
	 */
	function _get_item_users()
	{
		$this->db->select('user_id AS id, user_name, user_second_name, user_surname')
		         ->from('w_user')
				 ->where_in('user_group_id', $this->_get_item_groups());

		$query  = $this->db->get();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$val_arr[$row->id] = (($row->user_name != '')?$row->user_name:'').(($row->user_second_name != '')?' '.$row->user_second_name:'').(($row->user_surname != '')?' '.$row->user_surname:'');
			}
		}

		if(isset($val_arr) && is_array($val_arr))
		{
			return $val_arr;
		}
	}

	/**
	 * Функция, отдающая массив групп, которым нужны права для работы с элементами
	 *
	 * @access  public
	 * @return  array
	 */
	function _get_item_groups()
	{
		$groups = $this->config->item('cms_user_groups');
		$result = array();
		foreach ($groups as $key => $value) {
			if($groups[$key]['admin'] === true && $groups[$key]['items'] === false) $result[] = $key;
		}
		return $result;
	}

	/**
	 * Функция, проверки групп, которым нужны права для работы с элементами
	 *
	 * @access  public
	 * @param   string - тип элемента
	 * @return  array | bool
	 */
	function get_right_items($type)
	{
		$groups = $this->_get_item_groups();
		if(in_array($this->get_group_id(), $groups)) {
			$user_items = array();
			$this->db->select('wur_item_id AS id')
			         ->from('w_user_rights')
			         ->where('wur_user_id', $this->get_user_id())
					 ->where('wur_type', $type);
			$query  = $this->db->get();
			if ($query->num_rows() > 0) {
				foreach ( $query->result() as $row ) {
					$user_items[] = $row->id;
				}
			}
			return $user_items;
		} else {
			return false;
		}
	}

	/**
	 * Заносим данные в таблицу пересечений
	 *
	 * @access  public
	 * @param   int     - id записи
	 * @param   string  - тип записи
	 * @return  void
	 */
	function insert_item_rights($id, $type)
	{
		// $id родителя записи при операции копирования
		if($this->input->post('PME_sys_savecopy', TRUE)) {
			$field_base = $type.'_users';
			foreach ($this->input->post() as $k => $v) {
				if (preg_match_all('/^'.$field_base.'_([0-9]*)$/', $k, $matches)) {
					$pid = $matches[1][0];
				}
			}
		}
		else $pid = 0;

		if(is_array($this->input->post($type.'_users_'.$pid, TRUE))) {
			foreach ($this->input->post($type.'_users_'.$pid, TRUE) as $value) {
				$data = array(
					'wur_id'		=> '',
					'wur_user_id'	=> trim($value),
					'wur_item_id' 	=> $id,
					'wur_type'      => $type
				);
				$this->db->insert('w_user_rights', $data);
			}
		}
	}

	/**
	 * Заносим данные в таблицу пересечений
	 *
	 * @access  public
	 * @param   string - имя поля с id
	 * @param   string - таблица
	 * @param   string  - тип записи
	 * @return  void
	 */
	function insert_default_rights($id_name, $table, $type)
	{
		$groups = $this->_get_item_groups();
		if(in_array($this->get_group_id(), $groups)) {
			$this->db->select_max( $id_name, 'id' );
			$query = $this->db->get( $table );
			$row   = $query->row();
			$id    = $row->id;

			$data = array(
				'wur_id'      => '',
				'wur_user_id' => $this->get_user_id(),
				'wur_item_id' => $id,
				'wur_type'    => $type
			);
			$this->db->insert( 'w_user_rights', $data );
		}
	}

	/**
	 * Обновляем данные в таблице пересечений
	 *
	 * @access  public
	 * @param   int     - id записи
	 * @param   string  - тип записи
	 * @return  void
	 */
	function update_item_rights($id, $type)
	{
		// Удаляем старые записи
		$this->db->delete('w_user_rights', array('wur_item_id' => $id, 'wur_type' => $type));

		// Вносим новые записи
		if(is_array($this->input->post($type.'_users_'.$id, TRUE))) {
			foreach ($this->input->post($type.'_users_'.$id, TRUE) as $value) {
				$data = array(
					'wur_id'		=> '',
					'wur_user_id'	=> trim($value),
					'wur_item_id' 	=> $id,
					'wur_type'      => $type
				);
				$this->db->insert('w_user_rights', $data);
			}
		}
	}

	/**
	 * Удаляем данные в таблице пересечений
	 *
	 * @access  public
	 * @param   int     - id записи
	 * @param   string  - тип записи
	 * @return  void
	 */
	function delete_item_rights($id, $type)
	{
		$query = $this->db->get_where('w_user_rights', array('wur_item_id' => $id, 'wur_type' => $type));

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$this->trigger->delete_relative($row->wur_id, $this->trigger->get_last_basket_element(), 'w_user_rights', 'wur_id', 'Пересечение', '');
			}
		}
	}
}

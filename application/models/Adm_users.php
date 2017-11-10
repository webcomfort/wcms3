<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление пользователями
 */

class Adm_users extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
	 * Функция, отдающая дополнительные параметры в <head>
	 *
	 * @access	public
	 * @return	string
	 */
    function get_meta()
    {
        $meta = '
        <script type="text/javascript">

            jQuery(document).ready(function() {
                $(\'#PME_data_user_email\').keyup(function(){
                    checkAvailability();
                });
                $(\'#PME_data_user_nic\').keyup(function(){
                    checkAvailability2();
                });
                
                $(\'.select_all\').change(function() {
                var checkboxes = $(this).closest(\'form\').find(\':checkbox\');
                if($(this).is(\':checked\')) {
                    checkboxes.prop(\'checked\', true);
                } else {
                    checkboxes.prop(\'checked\', false);
                }
            });
            });

            function checkAvailability() {
                var email = $(\'#PME_data_user_email\').val();

                if(validateEmail(email)){
                    $(\'#PME_data_user_email_help\').html(\'Проверка...\');
                    $.post(\'/cms_user/p_check_email\', { email_check: email, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                        if(result == 1){
                            $(\'#PME_data_user_email_group\').removeClass(\'error warning success info\').addClass(\'success\');
                            $(\'#PME_data_user_email_help\').html(\'Допустимый адрес!\');
                        }else{
                            $(\'#PME_data_user_email_group\').removeClass(\'error warning success info\').addClass(\'warning\');
                            $(\'#PME_data_user_email_help\').html(\'Пользователь с таким адресом уже зарегистрирован!\');
                        }
                    });
                }
                else{
                    $(\'#PME_data_user_email_group\').removeClass(\'error warning success info\').addClass(\'error\');
                    $(\'#PME_data_user_email_help\').html(\'Неверный email!\');
                }
            }

            function checkAvailability2() {
                var nic = $(\'#PME_data_user_nic\').val();

                if(validateNic(nic)){
                    $(\'#PME_data_user_nic_help\').html(\'Проверка...\');
                    $.post(\'/cms_user/p_check_nic\', { nic_check: nic, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                        if(result == 1){
                            $(\'#PME_data_user_nic_group\').removeClass(\'error warning success info\').addClass(\'success\');
                            $(\'#PME_data_user_nic_help\').html(\'Допустимый ник!\');
                        }else{
                            $(\'#PME_data_user_nic_group\').removeClass(\'error warning success info\').addClass(\'warning\');
                            $(\'#PME_data_user_nic_help\').html(\'Пользователь с таким ником уже зарегистрирован!\');
                        }
                    });
                }
                else{
                    $(\'#PME_data_user_nic_group\').removeClass(\'error warning success info\').addClass(\'error\');
                    $(\'#PME_data_user_nic_help\').html(\'Допустимы латинские символы, цифры, пробел, минус, знак подчеркивания! Допустимо использовать от 3 до 30 символов.\');
                }
            }

            function validateEmail(email) {
                var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }

            function validateNic(nic) {
                var re = /^[-a-zA-ZА-Яа-яЁё0-9_\s]{3,30}$/;
                return re.test(nic);
            }

        </script>
        ';

        return $meta;
    }

    // ------------------------------------------------------------------------

    /**
	 * Функция, отдающая фильтры
	 *
	 * @access	public
	 * @return	string
	 */
    function get_filters()
    {
        // Получаем данные
        $filter_init = $this->config->item('cms_user_groups');

		foreach ($filter_init as $key => $value)
        {
            if($value['active']) $filter_values[$key] = $value['name'];
        }

        // Сессия
        if (!$this->session->userdata('user_filter'))
        {
            $this->session->set_userdata(array('user_filter' => current(array_keys($filter_values))));
        }

        if($this->input->post('user_filter', true) && preg_int($this->input->post('user_filter', true)))
        {
            $this->session->set_userdata(array('user_filter' => $this->input->post('user_filter', true)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите группу пользователей',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'user_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('user_filter'),
            'filter_values' => $filter_values
        );

        $filters = '<div class="row">
            <div class="col-xs-12"><div class="p20 ui-block">'.
				$this->load->view('admin/filter_default', $data, true)
			.'</div>
        </div></div>';

        return $filters;
    }

    // ------------------------------------------------------------------------

    /**
	 * Функция, отдающая основной интерфейс
	 *
	 * @access	public
	 * @return	string
	 */
    function get_output()
    {
        $this->load->library('myedit', $this->_get_crud_model());
        return $this->myedit->get_output();
    }

    // ------------------------------------------------------------------------

    /**
	 * Список полей с правами
	 *
	 * @access	private
	 * @return	array
	 */

    function _get_rights()
    {
        $opts   = array();
        $rights = array();
        $groups = $this->config->item('cms_user_groups');

        if ($groups[$this->session->userdata('user_filter')]['admin'])
        {
            // Если это операции с существующим пользователем
            if($this->input->get('PME_sys_rec', TRUE))
            {
                $query = $this->db->get_where('w_user_rules', array('rule_user_id' => $this->input->get('PME_sys_rec', TRUE)));

                foreach ($query->result() as $row)
                {
                    $right = '';
                    $right .= ($row->rule_view)   ? 'V,' : '';
                    $right .= ($row->rule_add)    ? 'A,' : '';
                    $right .= ($row->rule_edit)   ? 'C,' : '';
                    $right .= ($row->rule_copy)   ? 'P,' : '';
                    $right .= ($row->rule_delete) ? 'D,' : '';
					$right .= ($row->rule_active) ? 'Y' : '';

                    $rights[$row->rule_model_id] = $right;					
                }
            }

            $opts['fdd']['check'] = array(
                'name'          => 'Все',
                'nodb'          => true,
                'addcss'        => 'select_all',
                'select'        => 'C',
                'options'       => 'ACP',
                'values2'       => array (
                    '1'         => 'Выделить все'
                ),
                'default'       => '1',
                'tab'           => 'Права пользователя',
                'help'          => 'Возможность быстро снять выделение со всех'
            );

            // Получаем данные по модулям
            $this->db->select('module_id, module_name');
            $this->db->from('w_cms_modules');
            $this->db->where('module_active', 1);
            $this->db->where('module_type', 2);
            $this->db->order_by('module_sort', 'asc');
            $query = $this->db->get();

            if ($query->num_rows() > 0)
            {
                foreach ($query->result() as $row)
                {
                    $opts['fdd']['mod'.$row->module_id] = array(
                        'name'          => $row->module_name,
                        'nodb'          => true,
                        'select'        => 'C',
                        'options'       => 'ACP',
                        'values2'       => array (
                            'V'         => 'Право на просмотр',
                            'A'         => 'Право на добавление',
                            'C'         => 'Право на изменение',
                            'P'         => 'Право на копирование',
                            'D'         => 'Право на удаление',
							'Y'         => 'Право на публикацию'
                        ),
                        'default'       => 'V,A,C,P,D,Y',
                        'help'          => 'Проставьте права для модуля &laquo;'.$row->module_name.'&raquo;'
                    );

                    if(count($rights) && isset($rights[$row->module_id])) $opts['fdd']['mod'.$row->module_id]['fdefault'] = $rights[$row->module_id];
                }
            }
        }

        return $opts;
    }

    // ------------------------------------------------------------------------

	/**
	 * Параметры phpMyEdit
	 *
	 * @access	private
	 * @return	array
	 */
	function _get_crud_model ()
	{
        // Массив переменных из урла
        $uri_assoc_array = $this->uri->uri_to_assoc(1);

        // Получаем базовые настройки
        $this->load->model('Cms_myedit');
        $opts = $this->Cms_myedit->get_base_opts();
		
		// Переопределяем кнопки
		$opts['buttons']['L']['up'] = array('add','save','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_user';

        // Ключ
        $opts['key'] = 'user_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('user_surname');

        // Кол-во записей для вывода на экран
        $opts['inc'] = 100;

        // Имя файла модуля, передаем для последующей проверки прав на него
        $opts['module'] = basename(__FILE__);

        // Права пользователя, получаем из модуля cms_user:
        // A - добавление,  C - изменение, P - копирование, V - просмотр, D - удаление,
        // F - фильтры (всегда активно), I - начальная сортировка (всегда активно)
        $rights = $this->cms_user->get_user_myedit_rights();
        $opts['options'] = $rights[basename(__FILE__)];

        // Фильтрация вывода
        $opts['filters'] = array (
            " 	user_group_id = '" . $this->session->userdata('user_filter') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/users_insert_after.php';
		$opts['triggers']['update']['after']  = APPPATH.'triggers/users_update_after.php';
        $opts['triggers']['delete']['after']  = APPPATH.'triggers/users_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Пользователь';
        $opts['logtable_field'] = 'user_surname';

        // ------------------------------------------------------------------------
        // Опции полей (об этих и других опциях читайте в справке по phpMyEdit):
        // ------------------------------------------------------------------------
        // ['name'] (string) - название поля
        // ------------------------------------------------------------------------
        // ['nodb'] (bool) - есть в базе, или же поле системное
        // ------------------------------------------------------------------------
        // ['cell_display'] (string) - замещение содержимого для вывода в списке
        // ------------------------------------------------------------------------
        // ['add_display'] (string) - замещение содержимого при создании
        // ------------------------------------------------------------------------
        // ['change_display'] (string) - замещение содержимого при редактировании
        // ------------------------------------------------------------------------
        // ['sort'] (bool) - можно ли сортировать по этому полю в списке
        // ------------------------------------------------------------------------
        // ['escape'] (bool) - htmlspecialchars() для значения
        // ------------------------------------------------------------------------
        // ['strip_tags'] (bool) - strip_tags() для значения в списке
        // ------------------------------------------------------------------------
        // ['css'] (array) - дополнительные параметры для css-классов ячейки
        // ------------------------------------------------------------------------
        // ['addcss'] (string) - дополнительные классы для поля
        // ------------------------------------------------------------------------
        // ['default'] (string) - значение поля по умолчанию
        // ------------------------------------------------------------------------
        // ['fdefault'] (string) - замена стандартного значения поля на заданное
        // ------------------------------------------------------------------------
        // ['required'] (bool) - является ли поле обязательным для заполнения
        // ------------------------------------------------------------------------
        // ['tab'] (array, string) - в какой вкладке находится поле
        // ------------------------------------------------------------------------
        // ['file'] (array) - опции поля для загрузки файла
        // ['file']['tn'] (string) - префикс для файла-иконки (для изображений)
        // ['file']['url'] (string) - путь к директории с файлами
        // ['file']['multiple'] (bool) - мультизагрузка файлов
        // ------------------------------------------------------------------------
        // ['textarea'] (array) - <textarea> поле
        // ['textarea']['rows'] (int) - ряды
        // ['textarea']['cols'] (int) - колонки
        // ------------------------------------------------------------------------
        // ['help'] (string) - подсказка к полю
        // ------------------------------------------------------------------------
        // ['select'] (string) - тип поля
        // T - текстовое поле
        // N - поле для ввода числа (влияет на тип фильтра)
        // D - выпадающий список
        // M - список множественного выбора
        // O - радио-кнопки
        // C - чекбоксы
        // ------------------------------------------------------------------------
        // ['options'] (string) - опции показа
        // A - присутствует при добавлении записей
        // C - присутствует при изменении записей
        // P - присутствует при копировании записей
        // V - присутствует при просмотре записей
        // D - присутствует при удалении записей
        // L - присутствует при выводе списка записей
        // F - присутствует в фильтрах
        // ------------------------------------------------------------------------

        $opts['fdd']['user_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['user_group_id'] = array(
            'name'          => 'Группа',
            'select'        => 'T',
            'options'       => 'ACPH',
            'default'       => $this->session->userdata('user_filter'),
            'sort'          => false
        );
        $opts['fdd']['user_surname'] = array(
            'name'          => 'Фамилия',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'tab'		    => array (
                'name'      => 'Основные параметры',
                'default'   => true,
            ),
            'help'          => 'Введите фамилию пользователя.'
        );
        $opts['fdd']['user_name'] = array(
            'name'          => 'Имя',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите имя пользователя.'
        );
        $opts['fdd']['user_second_name'] = array(
            'name'          => 'Отчество',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'help'          => 'Введите отчество пользователя, если необходимо.'
        );
        $opts['fdd']['user_nic'] = array(
            'name'          => 'Ник',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'help'          => 'Введите отображаемое имя пользователя (если надо скрыть на сайте настоящее имя). При вводе будет осуществлена проверка допустимости введенного значения.'
        );
        $opts['fdd']['user_email'] = array(
            'name'          => 'Email',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'URL'           => 'mailto:$value',
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите email/логин пользователя. При вводе будет осуществлена проверка допустимости введенного значения. Будьте внимательны, при смене эл. почты автоматически будет сменен пароль - он будет выслан на новый адрес.'
        );
        $opts['fdd']['user_name_pref'] = array(
            'name'          => 'Предпочтения',
            'select'        => 'D',
            'options'       => 'ACPDV',
            'values2'       => array (
                '1'         => 'Выводить на сайт имя',
                '0'         => 'Выводить на сайт ник'
            ),
            'default'       => 0,
            'help'          => 'Что будет показано на сайте: ник или полное имя'
        );
        $opts['fdd']['user_active'] = array(
            'name'          => 'Статус',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '1'         => 'Активен',
                '0'         => 'Неактивен'
            ),
			'save'          => true,
            'default'       => 1,
            'help'          => 'Сделав пользователя неактивным, вы заблокируете ему возможность входа на сайт'
        );
        $opts = array_merge_recursive((array)$opts, (array)$this->_get_rights());

        // ------------------------------------------------------------------------

		return $opts;
	}
}
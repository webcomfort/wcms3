<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление
 */

class Adm_widgets extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        parent::__construct();
		$this->load->model('Cms_utils');
		$this->load->model('Cms_sidebar');
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
        $meta = '';
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
		$this->db->select('sidebar_id AS id, sidebar_name AS name')
			->from('w_sidebar');

		$query  = $this->db->get();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$filter_values[$row->id] = $row->name;
			}
		}

		asort($filter_values);

		// Сессия
		if (!$this->session->userdata('sidebar_filter'))
		{
			$this->session->set_userdata(array('sidebar_filter' => current(array_keys($filter_values))));
		}

		if($this->input->post('sidebar_filter', true) && preg_int($this->input->post('sidebar_filter', true)))
		{
			$this->session->set_userdata(array('sidebar_filter' => $this->input->post('sidebar_filter', true)));
		}

		// Отображение
		$data = array(
			'filter_name'   => 'Выберите сайдбар',
			'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
			'filter_field'  => 'sidebar_filter',
			'filter_class'  => ' select2',
			'filter_active' => $this->session->userdata('sidebar_filter'),
			'filter_values' => $filter_values
		);

		$filters = '
        <div class="row">
            <div class="col-xs-12"><div class="p20 ui-block">'.
			$this->load->view('admin/filter_default', $data, true)
			.'</div></div>
        </div>
        ';

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
	 * Функция, отдающая массив типов
	 *
	 * @access  public
	 * @return  array
	 */
	function _get_widget_types()
	{
		// Получаем данные
		$this->db->select('module_id AS id, module_name AS name')
			->from('w_cms_modules')
			->where('module_type', 3)
			->where('module_active', 1)
			->order_by("module_sort", "asc");

		$query  = $this->db->get();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$val_arr[$row->id] = $row->name;
			}
		}

		if(isset($val_arr) && is_array($val_arr))
		{
			return $val_arr;
		}
	}

	/**
	 * Функция, генерирующая поля
	 *
	 * @access  public
	 * @return  string
	 */
	function get_fields()
	{
		$rights = $this->cms_user->get_user_rights();

		if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
		{
			if($this->input->get('PME_sys_rec', TRUE)) $id = $this->input->get('PME_sys_rec', TRUE);
			elseif($this->input->post('PME_sys_rec', TRUE)) $id = $this->input->post('PME_sys_rec', TRUE);
			else $id = 0;

			return $this->_get_fields_html($id);
		}
	}

	/**
	 * Функция, генерирующая текстовые поля с возможностью редактирования
	 *
	 * @access  public
	 * @param   int - id родителя
	 * @param   string - тип родителя
	 * @return  string
	 */
	function _get_fields_html($id)
	{
		return '<div id="widget_params">'.$id.'</div>';

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
		// $id текущей записи
		if($this->input->get('PME_sys_rec', TRUE)) $id = $this->input->get('PME_sys_rec', TRUE);
		elseif($this->input->post('PME_sys_rec', TRUE)) $id = $this->input->post('PME_sys_rec', TRUE);
		else $id = 0;

		// Массив переменных из урла
        $uri_assoc_array = $this->uri->uri_to_assoc(1);

        // Получаем базовые настройки
        $this->load->model('Cms_myedit');
        $opts = $this->Cms_myedit->get_base_opts();
        //echo '<pre>'.print_r($opts, true).'</pre>';
		
		// Переопределяем кнопки
		$opts['buttons']['L']['up'] = array('add','save','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_sidebar_widgets';

        // Ключ
        $opts['key'] = 'widget_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('widget_sort');
		$opts['ui_sort_field'] = 'widget_sort';

        // Кол-во записей для вывода на экран
        $opts['inc'] = 100;

        // Имя файла модуля, передаем для последующей проверки прав на него
        $opts['module'] = basename(__FILE__);

        // Права пользователя, получаем из модуля cms_user:
        // A - добавление,  C - изменение, P - копирование, V - просмотр, D - удаление,
        // F - фильтры (всегда активно), I - начальная сортировка (всегда активно)
        $publish = $this->cms_user->get_user_rights();
		$publish = $publish[basename(__FILE__)]['active'];
		$rights = $this->cms_user->get_user_myedit_rights();
        $opts['options'] = $rights[basename(__FILE__)];
		$opts['options'] = str_replace("P", "", $opts['options']);

        // Фильтрация вывода
        $opts['filters'] = array (
            "widget_lang_id = '" . $this->session->userdata('w_alang') . "'",
			"sidebar_id = '" . $this->session->userdata('sidebar_filter') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
		//$opts['triggers']['insert']['after']  = APPPATH.'triggers/widget_insert_after.php';
		//$opts['triggers']['update']['after']  = APPPATH.'triggers/widget_update_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Виджеты';
        $opts['logtable_field'] = 'sidebar_name';

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

		$opts['fdd']['go'] = array(
			'name'          => '',
			'css'           => array('postfix'=>'nav'),
			'nodb'          => true,
			'options'       => 'L',
			'cell_display'   => '<div class="mr20"><a href="'.$opts['page_name'].'/move/up/id/$key" class="btn btn-sm btn-default mr2" rel="tooltip" title="Сдвинуть вверх"><i class="glyphicon glyphicon-chevron-up"></i></a><a href="'.$opts['page_name'].'/move/down/id/$key" class="btn btn-sm btn-default" rel="tooltip" title="Сдвинуть вниз"><i class="glyphicon glyphicon-chevron-down"></i></a></div>',
			'sort'          => false,
		);

        $opts['fdd']['widget_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
		$opts['fdd']['widget_type'] = array(
			'name'     => 'Тип виджета',
			'select'   => 'D',
			'options'  => 'LAPDV',
			'values2'  => $this->_get_widget_types(),
			'required' => false,
			'sort'     => false,
			'help'     => 'Выберите из списка тип виджета.'
		);
		$opts['fdd']['widget_name'] = array(
			'name'          => 'Имя виджета',
			'options'       => 'LACPDV',
			'select'        => 'T',
			'maxlen'        => 65535,
			'required'      => true,
			'sort'          => true,
			'help'          => 'Введите имя виджета.'
		);

		// ------------------------------------------------------------------------

		$opts = array_merge_recursive((array)$opts, (array)$this->Cms_sidebar->get_fields($id));

		// ------------------------------------------------------------------------

		$opts['fdd']['sidebar_id'] = array(
			'name'          => 'Сайдбар',
			'select'        => 'T',
			'options'       => 'ACPH',
			'maxlen'        => 3,
			'default'       => $this->session->userdata('sidebar_filter'),
			'sort'          => false
		);
		$where = array(
			'field' => 'sidebar_id',
			'value' => $this->session->userdata('sidebar_filter')
		);
		$opts['fdd']['widget_sort'] = array(
			'name'          => 'Сортировка',
			'select'        => 'T',
			'options'       => 'LACPD',
			'default'       => $this->Cms_utils->get_max_sort('widget_sort', 'w_sidebar_widgets', $where),
			'save'          => true,
			'sort'          => false
		);
        $opts['fdd']['widget_lang_id'] = array(
            'name'          => 'Язык',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('w_alang'),
            'sort'          => false
        );
		$opts['fdd']['created_at'] = array(
			'name'          => 'Дата создания',
			'select'        => 'T',
			'options'       => 'ACPH',
			'default'       => date('Y-m-d G:i:s'),
			'sort'          => false
		);


        // ------------------------------------------------------------------------

		return $opts;
	}
}

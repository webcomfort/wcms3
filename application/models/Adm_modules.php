<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление модулями
 */

class Adm_modules extends CI_Model {

    private $modules_array = array();

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
        $filter_init = array(
            '1' => 'Модули сайта',
			'2' => 'Модули администрирования'
        );

        foreach ($filter_init as $key => $value)
        {
            $filter_values[$key] = $value;
        }

        // Сессия
        if (!$this->session->userdata('modules_filter'))
        {
            $this->session->set_userdata(array('modules_filter' => current(array_keys($filter_values))));
        }

        if($this->input->post('modules_filter', true) && preg_int($this->input->post('modules_filter', true)))
        {
            $this->session->set_userdata(array('modules_filter' => $this->input->post('modules_filter', true)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите тип модуля',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'modules_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('modules_filter'),
            'filter_values' => $filter_values
        );

        $filters = '
        <div class="row">
            <div class="col-xs-12"><div class="p20 ui-block">'.
                $this->load->view('admin/filter_default', $data, true)
            .'</div>
        </div></div>
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

    /**
     * Файлы модулей
     *
     * @access  private
     * @return  void
     */

    function _find_modules($dir)
    {
        $handle = opendir($dir);
        while($f = readdir($handle))
        {
            if ($f != '.' && $f != '..')
            {
                if (is_dir($dir.$f))
                {
                    $this->_find_modules($dir.$f.'/');
                }
                else if (preg_match('/[0-9A-Za-z_]+\.php/',$f))
                {
                    $this->modules_array[$f] = $f;
                }
            }
        }
        asort($this->modules_array);
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
        // Получаем базовые настройки
        $this->load->model('Cms_myedit');
        $opts = $this->Cms_myedit->get_base_opts();

        // Таблица
        $opts['tb'] = 'w_cms_modules';

        // Ключ
        $opts['key'] = 'module_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('module_sort');
        $opts['ui_sort_field'] = 'module_sort';

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
            "module_type = '" . $this->session->userdata('modules_filter') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Модуль';
        $opts['logtable_field'] = 'module_name';

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

        // ------------------------------------------------------------------------

        $opts['fdd']['module_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['module_name'] = array(
            'name'          => 'Название блока',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название блока.'
        );
        $this->_find_modules(APPPATH."models/");
        $opts['fdd']['module_file'] = array(
            'name'          => 'Файл модуля',
            'options'       => 'ACPDV',
            'select'        => 'D',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'values2'       => $this->modules_array,
            'help'          => 'Выберите модуль.'
        );
        $opts['fdd']['module_active'] = array(
            'name'          => 'Статус',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '1'         => 'Активен',
                '0'         => 'Неактивен'
            ),
            'default'       => 1,
            'help'          => 'Сделайте модуль неактивным, чтобы его скрыть'
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['module_type'] = array(
            'name'          => 'Тип',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('modules_filter'),
            'sort'          => false
        );
        $opts['fdd']['module_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'ACPH',
            'default'       => time(),
            'fdefault'      => time(),
            'sort'          => false
        );

        // ------------------------------------------------------------------------

		return $opts;
	}
}
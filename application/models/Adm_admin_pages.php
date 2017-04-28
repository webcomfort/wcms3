<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление администраторскими страницами
 */

class Adm_admin_pages extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
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
        $filters = '';
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
	 * Возврат id родительской страницы
	 *
	 * @access	private
	 * @return	int
	 */

    function _get_parent()
    {
        if ($this->session->userdata('w_cms_pages_parent') != 0)
        {
            $this->db->select('cms_page_pid AS pid')
                ->from('w_cms_pages')
                ->where('cms_page_id', $this->session->userdata('w_cms_pages_parent'));

            $query  = $this->db->get();
            $row    = $query->row();
            return $row->pid;
        }
    }

    // ------------------------------------------------------------------------

    /**
	 * Массив страниц для формирования выпадающего списка
	 *
	 * @access	private
	 * @return	array
	 */

    function _get_parent_list()
    {
        $val_arr[0] = 'Верхний уровень';

        $this->db->select('cms_page_id AS id, cms_page_name AS name')
            ->order_by('cms_page_pid, cms_page_sort');

        $query = $this->db->get('w_cms_pages');

		foreach ($query->result() as $row)
        {
            $val_arr[$row->id] = $row->name;
        }

        return $val_arr;
    }

    // ------------------------------------------------------------------------

    /**
	 * Массив модулей для формирования выпадающего списка
	 *
	 * @access	private
	 * @return	array
	 */

    function _get_model_list()
    {
        $val_arr[0] = 'Не выбрано';

        // Получаем данные
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
                $val_arr[$row->module_id] = $row->module_name;
            }
        }

        return $val_arr;
    }

    // ------------------------------------------------------------------------

    /**
	 * Массив макетов для формирования выпадающего списка
	 *
	 * @access	private
	 * @return	array
	 */

    function _get_view_list()
    {
        $views  = $this->config->item('cms_admin_views');

        foreach ($views as $key => $value)
        {
            $val_arr[$key] = $value['name'];
        }

        return $val_arr;
    }

    // ------------------------------------------------------------------------

    /**
     * Вывод дочерних элементов
     *
     * @access	public
     * @param   int
     * @param   string
     * @return	string
     */

    function get_child_pages($key, $value)
    {
        $this->load->helper('html');

        $this->db->select('cms_page_id, cms_page_pid, cms_page_name')
            ->order_by('cms_page_pid, cms_page_name');
        $query = $this->db->get('w_cms_pages');

        if ($query->num_rows() > 0) {
            $forest = $this->tree->get_tree('cms_page_id', 'cms_page_pid', $query->result_array(), $key);
        }

        return $value . $this->_reformat_forest($forest);
    }

    // ------------------------------------------------------------------------

    /**
     * Переформатирование дочерних элементов под вывод списка
     *
     * @access	private
     * @param   array
     * @param   array
     * @return	array
     */

    function _reformat_forest ($forest, $menu = '')
    {
        $menu .= '<ul>';
        foreach ($forest as $tree)
        {
            $menu .= '<li>';
            $menu .= $tree['cms_page_name'];
            if (isset($tree['nodes'])) $menu = $this->_reformat_forest($tree['nodes'], $menu);
            $menu .= '</li>';

        }
        $menu .= '</ul>';

        return $menu;
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

        // Таблица
        $opts['tb'] = 'w_cms_pages';

        // Ключ
        $opts['key'] = 'cms_page_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('cms_page_sort');
        $opts['ui_sort_field'] = 'cms_page_sort';

        // Кол-во записей для вывода на экран
        $opts['inc'] = 100;

        // Имя файла модуля, передаем для последующей проверки прав на него
        $opts['module'] = basename(__FILE__);

        // Права пользователя, получаем из модуля cms_user:
        // A - добавление,  C - изменение, P - копирование, V - просмотр, D - удаление,
        // F - фильтры (всегда активно), I - начальная сортировка (всегда активно)
        $rights = $this->cms_user->get_user_myedit_rights();
        $opts['options'] = $rights[basename(__FILE__)];

        // Активизируем родительский режим и управляем сессиями
        if(isset($uri_assoc_array['parent'])){
            $this->session->set_userdata('w_cms_pages_parent', $uri_assoc_array['parent']);
        }
        if(!$this->session->userdata('w_cms_pages_parent')) {
            $this->session->set_userdata('w_cms_pages_parent', 0);
        }
        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_cms_pages_parent');

        // Фильтрация вывода
        $opts['filters'] = array (
            "cms_page_pid = '" . $this->session->userdata('w_cms_pages_parent') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
		$opts['triggers']['delete']['after']  = APPPATH.'triggers/admin_pages_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Страница администрирования';
        $opts['logtable_field'] = 'cms_page_name';

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
            'cell_display'   => '<a href="'.$opts['page_name'].'/parent/$key" class="btn btn-sm btn-warning" rel="tooltip" title="Подуровни"><i class="glyphicon glyphicon-folder-open icon-white"></i></a>',
            'sort'          => false,
        );
        $opts['fdd']['go2'] = array(
            'name'          => '',
            'css'           => array('postfix'=>'nav'),
            'nodb'          => true,
            'options'       => 'L',
            'cell_display'   => '<div class="mr20"><a href="'.$opts['page_name'].'/move/up/id/$key" class="btn btn-sm btn-default mr2" rel="tooltip" title="Сдвинуть вверх"><i class="glyphicon glyphicon-chevron-up"></i></a><a href="'.$opts['page_name'].'/move/down/id/$key" class="btn btn-sm btn-default" rel="tooltip" title="Сдвинуть вниз"><i class="glyphicon glyphicon-chevron-down"></i></a></div>',
            'sort'          => false,
        );
        $opts['fdd']['cms_page_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['cms_page_pid'] = array(
            'name'          => 'Родительский раздел',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->_get_parent_list(),
            'default'       => $this->session->userdata('w_cms_pages_parent'),
            'required'      => true,
            'sort'          => true,
            'help'          => 'Проставляется автоматически при заведении страницы. Можно использовать, когда требуется перенести страницу в другой раздел.'
        );
        $opts['fdd']['cms_page_name'] = array(
            'name'          => 'Название страницы',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'cell_func' => array(
                'model' => 'adm_admin_pages',
                'func'  => 'get_child_pages'
            ),
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название страницы. Это название будет использовано при выводе в меню.'
        );
        $opts['fdd']['cms_page_model_id'] = array(
            'name'          => 'Модуль',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->_get_model_list(),
            'default'       => 0,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Выберите из списка модуль администрирования'
        );
        $opts['fdd']['cms_page_view_id'] = array(
            'name'          => 'Макет',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->_get_view_list(),
            'default'       => 0,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Выберите из списка макет для отображения этой страницы'
        );
        $opts['fdd']['cms_page_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'ACPH',
            'default'       => time(),
            'fdefault'      => time(),
            'sort'          => false
        );
        $opts['fdd']['cms_page_status'] = array(
            'name'          => 'Статус',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '1'         => 'Активна и открыта',
                '2'         => 'Активна и невидима',
                '0'         => 'Неактивна',
                '3'         => 'Переход на уровень ниже'
            ),
            'default'       => 1,
            'help'          => 'Поведение страницы'
        );

        // ------------------------------------------------------------------------

		return $opts;
	}
}
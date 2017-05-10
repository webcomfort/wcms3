<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостными рубриками
 */

class Adm_tree_objects_and_tasks extends CI_Model {

    private $forest = array();
    private $forest2 = array();
    private $items_list = array();
    private $items_list2 = array();
    private $crumbs = array();

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
        $filter_values = array();
        $filter_values[0] = 'Не подключены к проектам';

        // Получаем данные
        $this->db->select('project_id AS id, project_name AS name')
            ->from('w_tree_projects');

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

        if (!$this->session->userdata('oat_project_filter') && $this->session->userdata('oat_project_filter') != 0)
        {
            $this->session->set_userdata(array('oat_project_filter' => 0));
        }

        if(($this->input->post('oat_project_filter', true) || $this->input->post('oat_project_filter', true) == '0') && preg_int($this->input->post('oat_project_filter', true)))
        {
            $this->session->set_userdata(array('oat_project_filter' => $this->input->post('oat_project_filter', true)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите проект',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'oat_project_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('oat_project_filter'),
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
     * Возврат id родителя
     *
     * @access	private
     * @return	int
     */

    function _get_parent()
    {
        if ($this->session->userdata('w_oat_parent') != 0)
        {
            $this->db->select('oat_pid AS pid')
                ->from('w_tree_objects_and_tasks')
                ->where('oat_id', $this->session->userdata('w_oat_parent'));

            $query  = $this->db->get();
            $row    = $query->row();
            return $row->pid;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функции, формирующая массив родителей
     *
     * @access  private
     * @return  array
     */
    function _get_parent_list()
    {
        $this->items_list[0] = 'Верхний уровень';

        // Получаем данные
        $this->db->select('oat_id, oat_pid, oat_name')
            ->from('w_tree_objects_and_tasks')
            ->order_by("oat_pid", "asc")
            ->order_by("oat_name", "asc")
            ->where('oat_lang_id', $this->session->userdata('w_alang'));

        $query  = $this->db->get();

        if ($query->num_rows() > 0) @$this->forest =& $this->tree->get_tree('oat_id', 'oat_pid', $query->result_array(), 0);
        $this->_get_items_array ($this->forest, 'oat_id', 'oat_pid', 'oat_name', '');
    }

    function _get_items_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            $this->items_list[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $this->_get_items_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
        }
    }

    /**
     * Функция, формирующая массив типов
     *
     * @access  private
     * @return  array
     */
    function _get_type_list()
    {
        $this->items_list2[0] = 'Тип не выбран';

        // Получаем данные
        $this->db->select('type_id, type_pid, type_name')
            ->from('w_tree_types')
            ->order_by("type_pid", "asc")
            ->order_by("type_name", "asc")
            ->where('type_lang_id', $this->session->userdata('w_alang'));

        $query  = $this->db->get();

        if ($query->num_rows() > 0) @$this->forest2 =& $this->tree->get_tree('type_id', 'type_pid', $query->result_array(), 0);
        $this->_get_types_array ($this->forest2, 'type_id', 'type_pid', 'type_name', '');
    }

    function _get_types_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            $this->items_list2[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $this->_get_types_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
        }
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

    function get_childs($key, $value)
    {
        $this->load->helper('html');
        $plus = false;

        $this->db->select('oat_id, oat_pid, oat_name')
            ->where('oat_lang_id', $this->session->userdata('w_alang'))
            ->order_by('oat_pid, oat_name');
        $query = $this->db->get('w_tree_objects_and_tasks');

        if ($query->num_rows() > 0) {
            $forest = $this->tree->get_tree('oat_id', 'oat_pid', $query->result_array(), $key);
            if (count($forest) > 0) $plus = true;
        }

        if ($plus) return '<a data-toggle="collapse" href="#collapseExample'.$key.'" aria-expanded="false" aria-controls="collapseExample'.$key.'">'.$value.' [+]'.'</a><div class="collapse" id="collapseExample'.$key.'">'.$this->_reformat_forest($forest).'</div>';
        else return $value;
    }

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
            $menu .= $tree['oat_name'];
            if (isset($tree['nodes'])) $menu = $this->_reformat_forest($tree['nodes'], $menu);
            $menu .= '</li>';

        }
        $menu .= '</ul>';

        return $menu;
    }

    /**
     * Переформатирование дочерних элементов под вывод списка
     *
     * @access	private
     * @param   array
     * @param   array
     * @return	array
     */

    function _get_crumbs ()
    {
        $crumbs = '<small>';
        $this->set_crumbs($this->forest, 'oat_id', 'oat_pid', 'oat_name', '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/', $this->session->userdata('w_oat_parent'));
        $this->tree->get_crumbs();
        $this->crumbs = array_reverse($this->crumbs);
        foreach ($this->crumbs as $value) $crumbs .= '<a href="'.$value['url'].'">'.$value['oat_name'].'</a> &raquo; ';
        $crumbs .= '</small>';
        return $crumbs;
    }

    /**
     * Формируем массив из связанных страниц
     *
     * @access  public
     * @param   array
     * @param   string
     * @param   string
     * @param   int
     * @return  void
     */
    function set_crumbs ($forest, $id_name, $parent_name, $level_name, $link = '/', $active_id)
    {
        if (is_array($forest))
        {
            foreach ($forest as $tree)
            {
                if ($tree[$id_name] == $active_id)
                {
                    $this->crumbs[$tree[$id_name]][$id_name] = $tree[$id_name];
                    $this->crumbs[$tree[$id_name]][$parent_name] = $tree[$parent_name];
                    $this->crumbs[$tree[$id_name]][$level_name] = $tree[$level_name];
                    $this->crumbs[$tree[$id_name]]['url'] = $link.$tree[$parent_name];

                    if ($tree[$parent_name] != 0) $this->set_crumbs($this->forest, $id_name, $parent_name, $level_name, $link, $tree[$parent_name]);
                }
                else
                {
                    if(isset($tree['nodes'])) $this->set_crumbs($tree['nodes'], $id_name, $parent_name, $level_name, $link, $active_id);
                }
            }
        }
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
        $opts['tb'] = 'w_tree_objects_and_tasks';

        // Ключ
        $opts['key'] = 'oat_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('oat_name');
        $opts['ui_sort_field'] = '';

        // Кол-во записей для вывода на экран
        $opts['inc'] = 20;

        // Имя файла модуля, передаем для последующей проверки прав на него
        $opts['module'] = basename(__FILE__);

        // Права пользователя, получаем из модуля cms_user:
        // A - добавление,  C - изменение, P - копирование, V - просмотр, D - удаление,
        // F - фильтры (всегда активно), I - начальная сортировка (всегда активно)
        $rights = $this->cms_user->get_user_myedit_rights();
        $opts['options'] = $rights[basename(__FILE__)];

        // Активизируем родительский режим и управляем сессиями
        if($this->uri->segment(3) == 'lang' && preg_int ($this->uri->segment(4))) $this->session->unset_userdata('w_oat_parent');

        if(isset($uri_assoc_array['parent'])){
            $this->session->set_userdata('w_oat_parent', $uri_assoc_array['parent']);
        }
        if(!$this->session->userdata('w_oat_parent')) {
            $this->session->set_userdata('w_oat_parent', 0);
        }

        // Фильтрация вывода
        $opts['filters'] = array (
            "oat_pid = '" . $this->session->userdata('w_oat_parent') . "'",
            "oat_project_id = '" . $this->session->userdata('oat_project_filter') . "'",
            "oat_lang_id = '" . $this->session->userdata('w_alang') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Объект';
        $opts['logtable_field'] = 'object_name';

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

        // ------------------------------------------------------------------------

        $opts['fdd']['oat_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $this->_get_parent_list();
        $opts['fdd']['oat_pid'] = array(
            'name'          => 'Родительский раздел',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->items_list,
            'default'       => $this->session->userdata('w_oat_parent'),
            'required'      => true,
            'sort'          => false,
            'help'          => 'Проставляется автоматически при заведении. Можно использовать, когда требуется перенести объект (ветку) в другой раздел.'
        );
        $opts['fdd']['oat_name'] = array(
            'name'          => 'Название',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'cell_func' => array(
                'model' => 'adm_tree_objects_and_tasks',
                'func'  => 'get_childs'
            ),
            'help'          => 'Введите название.'
        );
        $this->_get_type_list();
        $opts['fdd']['oat_type_id'] = array(
            'name'          => 'Тип',
            'select'        => 'D',
            'options'       => 'LACPD',
            'values2'       => $this->items_list2,
            'default'       => 0,
            'required'      => true,
            'sort'          => false,
            'help'          => 'Выберите из списка тип.'
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['oat_project_id'] = array(
            'name'          => 'Проект',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('oat_project_filter'),
            'sort'          => false
        );
        $opts['fdd']['oat_user_id'] = array(
            'name'          => 'Автор',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->cms_user->get_user_id(),
            'sort'          => false
        );
        $opts['fdd']['oat_lang_id'] = array(
            'name'          => 'Язык',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('w_alang'),
            'sort'          => false
        );

        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_oat_parent');
        $opts['parent_crumbs']  = $this->_get_crumbs();

		return $opts;
	}
}
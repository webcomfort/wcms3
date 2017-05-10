<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостными рубриками
 */

class Adm_tree_types extends CI_Model {

    private $forest = array();
    private $items_list = array();

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
     * Возврат id родителя
     *
     * @access	private
     * @return	int
     */

    function _get_parent()
    {
        if ($this->session->userdata('w_type_parent') != 0)
        {
            $this->db->select('type_pid AS pid')
                ->from('w_tree_types')
                ->where('type_id', $this->session->userdata('w_type_parent'));

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
        $this->db->select('type_id, type_pid, type_name')
            ->from('w_tree_types')
            ->order_by("type_pid", "asc")
            ->order_by("type_name", "asc")
            ->where('type_lang_id', $this->session->userdata('w_alang'));

        $query  = $this->db->get();

        if ($query->num_rows() > 0) @$this->forest =& $this->tree->get_tree('type_id', 'type_pid', $query->result_array(), 0);
        $this->_get_items_array ($this->forest, 'type_id', 'type_pid', 'type_name', '');
    }

    function _get_items_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            $this->items_list[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $this->_get_items_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
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

        $this->db->select('type_id, type_pid, type_name')
            ->where('type_lang_id', $this->session->userdata('w_alang'))
            ->order_by('type_pid, type_name');
        $query = $this->db->get('w_tree_types');

        if ($query->num_rows() > 0) {
            $forest = $this->tree->get_tree('type_id', 'type_pid', $query->result_array(), $key);
            if (count($forest) > 0) $plus = true;
        }

        return '<a data-toggle="collapse" href="#collapseExample'.$key.'" aria-expanded="false" aria-controls="collapseExample'.$key.'">'.$value.(($plus) ? ' [+]' : '').'</a><div class="collapse" id="collapseExample'.$key.'">'.$this->_reformat_forest($forest).'</div>';
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
            $menu .= $tree['type_name'];
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
        $opts['tb'] = 'w_tree_types';

        // Ключ
        $opts['key'] = 'type_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('type_name');
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
        if($this->uri->segment(3) == 'lang' && preg_int ($this->uri->segment(4))) $this->session->unset_userdata('w_type_parent');

        if(isset($uri_assoc_array['parent'])){
            $this->session->set_userdata('w_type_parent', $uri_assoc_array['parent']);
        }
        if(!$this->session->userdata('w_type_parent')) {
            $this->session->set_userdata('w_type_parent', 0);
        }
        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_type_parent');

        // Фильтрация вывода
        $opts['filters'] = array (
            "type_pid = '" . $this->session->userdata('w_type_parent') . "'",
            "type_lang_id = '" . $this->session->userdata('w_alang') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Тип';
        $opts['logtable_field'] = 'type_name';

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

        $opts['fdd']['type_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $this->_get_parent_list();
        $opts['fdd']['type_pid'] = array(
            'name'          => 'Родительский раздел',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->items_list,
            'default'       => $this->session->userdata('w_type_parent'),
            'required'      => true,
            'sort'          => false,
            'help'          => 'Проставляется автоматически при заведении. Можно использовать, когда требуется перенести объект (ветку) в другой раздел.'
        );
        $opts['fdd']['type_name'] = array(
            'name'          => 'Название',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'cell_func' => array(
                'model' => 'adm_tree_types',
                'func'  => 'get_childs'
            ),
            'help'          => 'Введите название.'
        );
        $opts['fdd']['type_label'] = array(
            'name'          => 'Метка',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда метку типа на латинице, разные слова разделяются подчеркиванием, например "Региональный музей" = "regional_museum".'
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['type_user_id'] = array(
            'name'          => 'Автор',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->cms_user->get_user_id(),
            'sort'          => false
        );
        $opts['fdd']['type_lang_id'] = array(
            'name'          => 'Язык',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('w_alang'),
            'sort'          => false
        );

		return $opts;
	}
}
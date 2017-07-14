<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостными рубриками
 */

class Adm_tree_types extends CI_Model {

    private $forest = array();
    private $items_list = array();
    private $crumbs = array();

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
        $this->load->helper( array('string') );
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
        $meta = '<script>
        jQuery(document).ready(function() {

            var label           = \'#PME_data_type_label\';
            var name            = \'#PME_data_type_name\';
            var generate_func   = \'/adm_tree_types/p_label_generate\';
            var check_func      = \'/adm_tree_types/p_check_label\';
            var check           = $(label).val();

            $(name).keyup(function(){
                if (check === \'\') { label_generate(label, name, generate_func, check_func); }
            });

            $(label).keyup(function(){
                check_availability(label, name, generate_func, check_func);
            });
        });

        function label_generate(label, name, generate_func, check_func){
            $.post(generate_func, { url: $(name).val(), '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(label).val(result);
                check_availability(label, name, generate_func, check_func);
            });
        }

        function check_availability(label, name, generate_func, check_func){
            if($(label).val().length < 3){
                $(label+\'_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                $(label+\'_help\').html(\'Должно быть не менее трех символов\');
            }
            else{
                $(label+\'_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\');
                $(label+\'_help\').html(\'Проверка...\');
                $.post(check_func, { name: $(label).val(), '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                    if(result == 1){
                        $(label+\'_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-success\');
                        $(label+\'_help\').html(\'<strong>\' + $(label).val() + \'</strong> свободно\');
                    }
                    if(result == 2){
                        $(label+\'_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(label+\'_help\').html(\'URL содержит недопустимые символы\');
                    }
                    if(result == 0){
                        $(label+\'_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(label+\'_help\').html(\'<strong>\' + $(label).val() + \'</strong> занято\');
                    }
                });
            }
        }

        </script>';
        return $meta;
    }

    // ------------------------------------------------------------------------

    /**
     * Функция, генерирующая метку (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_label_generate()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            echo ru2lat($this->input->post('url', TRUE), 0);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция, проверяющая метку (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_check_label()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            if(preg_ext_string($this->input->post('name', TRUE)))
            {
                $this->db->select('type_id');
                $this->db->where('type_label', $this->input->post('name', TRUE));
                $query = $this->db->get('w_tree_types');

                if ($query->num_rows() > 0) echo 0;
                else echo 1;
            }
            else echo 2;
        }
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

    function reformat_childs($forest, $id_name, $parent_values)
    {
        foreach ($forest as $tree)
        {
            foreach ($parent_values as $value) {

                $this->db->select('tf_id')
                    ->from('w_tree_types_fields')
                    ->where('type_id', $tree[$id_name])
                    ->where('field_id', $value);

                $query  = $this->db->get();

                if ($query->num_rows() == 0) {
                    $data = array(
                        'tf_id' => '',
                        'type_id' => $tree[$id_name],
                        'field_id' => trim($value)
                    );
                    $this->db->insert('w_tree_types_fields', $data);
                }
            }

            if (isset($tree['nodes'])) {
                $this->reformat_childs($tree['nodes'], $id_name,  $parent_values);
            }
        }
    }

    /**
     * Возврат id родителя
     *
     * @access	private
     * @return	int
     */

    function reformat_childs_stages($forest, $id_name, $parent_values)
    {
        foreach ($forest as $tree)
        {
            foreach ($parent_values as $value) {

                $this->db->select('ts_id')
                    ->from('w_tree_types_stages')
                    ->where('type_id', $tree[$id_name])
                    ->where('stage_id', $value);

                $query  = $this->db->get();

                if ($query->num_rows() == 0) {
                    $data = array(
                        'ts_id' => '',
                        'type_id' => $tree[$id_name],
                        'stage_id' => trim($value)
                    );
                    $this->db->insert('w_tree_types_stages', $data);
                }
            }

            if (isset($tree['nodes'])) {
                $this->reformat_childs_stages($tree['nodes'], $id_name,  $parent_values);
            }
        }
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

        if ($plus) return '<a data-toggle="collapse" href="#collapseExample'.$key.'" aria-expanded="false" aria-controls="collapseExample'.$key.'">'.$value.' [+]'.'</a><div class="collapse" id="collapseExample'.$key.'"><div class="jstree">'.$this->_reformat_forest($forest).'</div></div>';
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
            $menu .= '<a href="/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/'.'?PME_sys_fl=0&PME_sys_fm=0&PME_sys_sfn[0]=8&PME_sys_operation=PME_op_Change&PME_sys_rec='.$tree['type_id'].'">';
            $menu .= $tree['type_name'];
            $menu .= '</a>';
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
        $this->set_crumbs($this->forest, 'type_id', 'type_pid', 'type_name', '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/', $this->session->userdata('w_type_parent'));
        $this->tree->get_crumbs();
        $this->crumbs = array_reverse($this->crumbs);
        foreach ($this->crumbs as $value) $crumbs .= '<a href="'.$value['url'].'">'.$value['type_name'].'</a> &raquo; ';
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

    // ------------------------------- ЭТАПЫ -----------------------------------------

    /**
     * Функция
     *
     * @access	public
     * @param   int - id для выборки
     * @param   mixed - значение поля (если требуется)
     * @param   string - вид выборки
     * @return	string
     */

    function get_stages($key, $value='', $mode='change')
    {
        $form = '<div id="PME_data_type_fields_group" class="control-group">';

        // Получаем массивы с данными для формирования селекта
        $checkbox_data = $this->_get_stages_array($key, $mode);

        // Строим селект
        $this->load->helper('form');

        foreach ($checkbox_data['values'] AS $id => $value) {
            $form  .= '<div class="checkbox"><label>';
            $cheked = (in_array($id, $checkbox_data['defaults'])) ? true : false;
            $form  .= form_checkbox('type_stages_' . $key . '[]', $id, $cheked);
            $form  .= $value;
            $form  .= '</label></div>';
        }

        $form .= '</div>';

        // Компануем и выводим все это ячейку таблицы списка
        return $form;
    }

    /**
     * Функция
     *
     * @access	private
     * @param   int - id для выборки
     * @param   string - вид выборки
     * @return	array
     */
    function _get_stages_array($key, $mode='change')
    {
        $val_arr = array();
        $val_arr_active = array();
        $total = 0;

        $query_default = $this->db->query('SELECT stage_id AS id, stage_name AS name FROM w_tree_stages ORDER BY stage_sort');
        if ($query_default->num_rows() > 0) {
            foreach ($query_default->result() as $row_default) {
                $val_arr[$row_default->id] = $row_default->name;
            }
        }

        $this->db->select('ts_id, type_id, w_tree_types_stages.stage_id, stage_name')
            ->from('w_tree_types_stages')
            ->join('w_tree_stages', 'w_tree_types_stages.stage_id = w_tree_stages.stage_id')
            ->where('type_id', $key);

        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $val_arr_active[] = $row->stage_id;
            }

            $total = $query->num_rows();
        }

        $data = array(
            'values'    => $val_arr,
            'defaults'  => $val_arr_active,
            'total'     => $total
        );

        return $data;
    }

    // ------------------------------- ПОЛЯ -----------------------------------------

    /**
     * Функция, список выбора для пересечений с событиями
     *
     * @access	public
     * @param   int - id для выборки
     * @param   mixed - значение поля (если требуется)
     * @param   string - вид выборки
     * @return	string
     */

    function get_fields($key, $value='', $mode='change')
    {
        $form = '<div id="PME_data_type_fields_group" class="control-group">';

        // Получаем массивы с данными для формирования селекта
        $checkbox_data = $this->_get_fields_array($key, $mode);

        // Строим селект
        $this->load->helper('form');

        foreach ($checkbox_data['values'] AS $id => $value) {
            $form  .= '<div class="checkbox"><label>';
            $cheked = (in_array($id, $checkbox_data['defaults'])) ? true : false;
            $form  .= form_checkbox('type_fields_' . $key . '[]', $id, $cheked);
            $form  .= $value;
            $form  .= '</label></div>';
        }

        $form .= '</div>';

        // Компануем и выводим все это ячейку таблицы списка
        return $form;
    }

    /**
     * Функция, отдающая массивы пересечений ФОТОК и СОБЫТИЙ для формирования селекта с дефолтными значениями
     *
     * @access	private
     * @param   int - id для выборки
     * @param   string - вид выборки
     * @return	array
     */
    function _get_fields_array($key, $mode='change')
    {
        $val_arr = array();
        $val_arr_active = array();
        $total = 0;

        $query_default = $this->db->query('SELECT field_id AS id, field_name AS name FROM w_tree_fields ORDER BY field_sort');
        if ($query_default->num_rows() > 0) {
            foreach ($query_default->result() as $row_default) {
                $val_arr[$row_default->id] = $row_default->name;
            }
        }

        $this->db->select('tf_id, type_id, w_tree_types_fields.field_id, field_name')
            ->from('w_tree_types_fields')
            ->join('w_tree_fields', 'w_tree_types_fields.field_id = w_tree_fields.field_id')
            ->where('type_id', $key);

        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $val_arr_active[] = $row->field_id;
            }

            $total = $query->num_rows();
        }

        $data = array(
            'values'    => $val_arr,
            'defaults'  => $val_arr_active,
            'total'     => $total
        );

        return $data;
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

        // Таблица
        $opts['tb'] = 'w_tree_types';

        // Ключ
        $opts['key'] = 'type_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('type_name');
        $opts['ui_sort_field'] = 'type_name';

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

        // Фильтрация вывода
        $opts['filters'] = array (
            "type_pid = '" . $this->session->userdata('w_type_parent') . "'",
            "type_lang_id = '" . $this->session->userdata('w_alang') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/tree_type_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/tree_type_update_after.php';
        $opts['triggers']['delete']['after']  = APPPATH.'triggers/tree_type_delete_after.php';

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
        $opts['fdd']['type_fields'] = array(
            'name'     => 'Поля',
            'nodb'     => true,
            'select'   => 'C',
            'options'  => 'ACP',
            'add_display'   => $this->get_fields($id, '', 'add'),
            'change_display'=> $this->get_fields($id, '', 'change'),
            'required' => false,
            'sort'     => false,
            'help'     => 'Выберите из списка поля, характерные для этого типа.'
        );
        $opts['fdd']['type_stages'] = array(
            'name'     => 'Этапы',
            'nodb'     => true,
            'select'   => 'C',
            'options'  => 'ACP',
            'add_display'   => $this->get_stages($id, '', 'add'),
            'change_display'=> $this->get_stages($id, '', 'change'),
            'required' => false,
            'sort'     => false,
            'help'     => 'Выберите из списка этапы, характерные для этого типа.'
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

        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_type_parent');
        $opts['parent_crumbs']  = $this->_get_crumbs();

		return $opts;
	}
}
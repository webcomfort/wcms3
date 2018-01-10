<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление
 */

class Adm_shop_item extends CI_Model {

    private $forest     = array(); // древовидный массив товаров
    private $cat_forest = array(); // древовидный массив категорий
    private $categories = array(); // массив для вывода фильтра
    private $crumbs     = array(); // крошки, для интерфейса

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        parent::__construct();
        $this->load->model('Cms_shop');
        $this->load->model('Cms_utils');
        $this->load->model('Cms_inclusions');
        $this->load->model('Cms_myedit');
        $this->load->model('Cms_articles');
	    $this->load->model('Cms_tags');
        $this->load->helper( array('string') );
        $this->_categories(); // наполнит $this->categories и $this->cat_forest для фильтра
        $this->_get_parent_list(); // наполнит $this->forest для UI крошек
        // Сработает при наличи в POST полей item_cats
        $this->Cms_myedit->mass_save('item_cats', 'item_id', 'cat_id', 'sic_id', 'w_shop_items_cats');
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
        if($this->input->get('PME_sys_rec', TRUE)) $id = $this->input->get('PME_sys_rec', TRUE);
        else $id = 0;

        $meta = '<script>
        jQuery(document).ready(function() {

            var urlcheck = $(\'#PME_data_item_url\').val();
            var titlecheck = $(\'#PME_data_item_meta_title\').val();
            var type = $(\'#PME_data_item_type_id\').val();

            $(\'#PME_data_item_name\').keyup(function(){
                if (urlcheck == \'\') { url_generate(); }
                if (titlecheck == \'\') { $(\'#PME_data_item_meta_title\').val($(\'#PME_data_item_name\').val()); }
            });

            $(\'#PME_data_item_url\').keyup(function(){
                check_availability();
            });
            
            $(\'#PME_data_item_type_id\').change(function() { get_page_fields($(\'#PME_data_item_type_id\').val()); });
            
            get_page_fields(type);
        });
            
        function get_page_fields(type){
            $.post(\'/adm_shop_item/p_item_fields\', { type: type, id: "'.$id.'", '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(\'#item_fields_area\').empty().append(result);
            });
        }

        function url_generate(){
            var url = $(\'#PME_data_item_name\').val();
            $.post(\'/adm_shop_item/p_url_generate\', { url: url, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(\'#PME_data_item_url\').val(result);
                check_availability();
            });
        }

        function check_availability(){
            var name = $(\'#PME_data_item_url\').val();

            if($(\'#PME_data_item_url\').val().length < 3){
                $(\'#PME_data_item_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                $(\'#PME_data_item_url_help\').html(\'Должно быть не менее трех символов\');
            }
            else{
                $(\'#PME_data_item_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\');
                $(\'#PME_data_item_url_help\').html(\'Проверка...\');
                $.post(\'/adm_shop_item/p_check_url\', { name: name, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                    if(result == 1){
                        $(\'#PME_data_item_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-success\');
                        $(\'#PME_data_item_url_help\').html(\'<strong>\' + name + \'</strong> свободно\');
                    }
                    if(result == 2){
                        $(\'#PME_data_item_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_item_url_help\').html(\'URL содержит недопустимые символы\');
                    }
                    if(result == 0){
                        $(\'#PME_data_item_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_item_url_help\').html(\'<strong>\' + name + \'</strong> занято\');
                    }
                });
            }
        }

        </script>';

	    // JS функции для модуля select2 - простой список
	    $meta .= $this->Cms_myedit->get_ajax_default_format();

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
        $filter_values = $this->categories;
        $filter_values[999999999] = 'Не подключены';

        // Сессия
        if (!$this->session->userdata('sitem_filter'))
        {
            $this->session->set_userdata(array('sitem_filter' => current(array_keys($filter_values))));
        }

        if($this->input->post('sitem_filter', true) && preg_int($this->input->post('sitem_filter', true)))
        {
            $this->session->set_userdata(array('sitem_filter' => $this->input->post('sitem_filter', true)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите товарную категорию',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'sitem_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('sitem_filter'),
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
     * Функция, генерирующая URL (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_url_generate()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            echo ru2lat($this->input->post('url', TRUE), 0);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция, проверяющая URL (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_check_url()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            if(preg_ext_string($this->input->post('name', TRUE)))
            {
                $this->db->select('item_id');
                $this->db->where('item_url', $this->input->post('name', TRUE));
                $query = $this->db->get('w_shop_items');

                if ($query->num_rows() > 0) echo 0;
                else echo 1;
            }
            else echo 2;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Возврат id родительской страницы
     *
     * @access  private
     * @return  int
     */

    function _get_parent()
    {
        if ($this->session->userdata('w_sitem_parent') != 0)
        {
            $this->db->select('item_pid AS pid')
                ->from('w_shop_items')
                ->where('item_id', $this->session->userdata('w_sitem_parent'));

            $query  = $this->db->get();
            $row    = $query->row();
            return $row->pid;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Формирование хлебных крошек для UI
     *
     * @access	private
     * @return	string
     */

    function _get_crumbs ()
    {
        $crumbs = '<small>';
        $this->set_crumbs($this->forest, 'item_id', 'item_pid', 'item_name', '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/', $this->session->userdata('w_sitem_parent'));
        $this->crumbs = array_reverse($this->crumbs);
        foreach ($this->crumbs as $value) $crumbs .= '<a href="'.$value['url'].'">'.$value['item_name'].'</a> &raquo; ';
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
     * Функция, отдающая массив производителей
     *
     * @access  public
     * @return  array
     */
    function _get_vendors()
    {
        $val_arr[0] = 'Не выбрано';

        // Получаем данные
        $this->db->select('vendor_id AS id, vendor_name AS name')
            ->from('w_shop_vendors')
            ->order_by("vendor_name", "asc");

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
     * Функция, отдающая массив производителей
     *
     * @access  public
     * @return  array
     */
    function _get_types()
    {
        $val_arr[0] = 'Не выбрано';

        // Получаем данные
        $this->db->select('type_id AS id, type_name AS name')
            ->from('w_shop_types')
            ->order_by("type_name", "asc");

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

    // ------------------------------------------------------------------------

    /**
     * Функция, формирующая массив категорий
     *
     * @access  public
     * @return  array
     */
    function _categories()
    {
        // Получаем данные
        $this->db->select('cat_id, cat_pid, cat_name, cat_url')
            ->from('w_shop_categories')
            ->order_by("cat_pid", "asc")
            ->order_by("cat_sort", "asc")
            ->where('cat_lang_id', $this->session->userdata('w_alang'));

        $query  = $this->db->get();

        if ($query->num_rows() > 0) @$this->cat_forest =& $this->tree->get_tree('cat_id', 'cat_pid', $query->result_array(), 0);
        $this->_get_categories_array ($this->cat_forest, 'cat_id', 'cat_pid', 'cat_name', '');
    }

    /**
     * Функция, формирующая массив категорий
     *
     * @access  public
     * @return  array
     */
    function _get_categories_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            $this->categories[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $menu = $this->_get_categories_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
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
        $this->db->select('item_id, item_pid, item_name')
            ->where('item_lang_id', $this->session->userdata('w_alang'))
            ->order_by('item_pid, item_sort');
        $query = $this->db->get('w_shop_items');
        if ($query->num_rows() > 0) $this->forest = $this->tree->get_tree('item_id', 'item_pid', $query->result_array(), 0);
    }

    // ------------------------------- РУБРИКИ -----------------------------------------

    /**
     * Функция, отдающая массив категорий
     *
     * @access  public
     * @return  array
     */
    function _get_cats()
    {
        // Получаем данные
        $this->db->select('cat_id AS id, cat_name AS name')
            ->from('w_shop_categories');

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
     * Функция, список выбора для пересечений с событиями
     *
     * @access	private
     * @return	string
     */

    function get_cats_select($key, $value='')
    {
        // Получаем массивы с данными для формирования селекта
        $select_array = $this->_item_cats($key);

        // Строим селект
        $this->load->helper('form');
        $opts = 'class="js-data-ajax-cats-'.$key.' select2"';
        $form = form_multiselect('item_cats_'.$key.'[]', $select_array['values'], $select_array['defaults'], $opts);

        // Получаем js-код для этого поля
        $script = $this->Cms_myedit->get_ajax($key, '/adm_shop_item/p_cat_generate', 'cats', 1);

        // Компануем и выводим все это ячейку таблицы списка
        return $form.$script;
    }

    /**
     * Функция, генерирующая список событий для <select> (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_cat_generate()
    {
        $rights = $this->cms_user->get_user_rights();
        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            echo $this->Cms_myedit->get_ajax_query ('cat_id', 'cat_name', 'w_shop_categories', 1, false, false);
        }
    }

    /**
     * Функция, отдающая массивы пересечений новостей и категорий для формирования селекта с дефолтными значениями
     *
     * @access	private
     * @param   int - id для выборки
     * @return	array
     */
    function _item_cats($key)
    {
        $val_arr = array();
        $val_arr_active = array();

        if($key == 0 && $this->session->userdata('sitem_filter') != 999999999) {
            $query_active = $this->db->query('SELECT cat_id AS id, cat_name AS name FROM w_shop_categories WHERE cat_id = "'.$this->session->userdata('sitem_filter').'"');
            $row_active = $query_active->row();
            $val_arr[$row_active->id] = $row_active->name;
            $val_arr_active[] = $row_active->id;
        }

        // Active
        $this->db->select('w_shop_items_cats.cat_id AS id, cat_name AS name')
            ->from('w_shop_items_cats')
            ->join('w_shop_categories', 'w_shop_categories.cat_id = w_shop_items_cats.cat_id')
            ->where('item_id', $key);

        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $val_arr[$row->id] = $row->name;
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

    // ------------------------------------------------------------------------

    /**
     * Функция, генерирующая текстовые поля (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function get_articles()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            if($this->input->get('PME_sys_rec', TRUE)) $id = $this->input->get('PME_sys_rec', TRUE);
            elseif($this->input->post('PME_sys_rec', TRUE)) $id = $this->input->post('PME_sys_rec', TRUE);
            else $id = 0;

            return $this->Cms_articles->get_article_editors($id, 'shop');
        }
    }

    // ------------------------------------ FIELDS ------------------------------------

    function _get_field_value($id, $field_id){
        $query = $this->db->query('SELECT par_value FROM w_shop_items_params WHERE item_id = "'.$id.'" AND field_id = "'.$field_id.'"');
        $row = $query->row();
        if ($query->num_rows() > 0) {
            return $row->par_value;
        } else {
            return false;
        }
    }

    function _get_field($field_id = 0, $id = 0, $field_text = '', $field_values = '', $field_default = '', $field_type = 0){

        $this->load->helper('form');
        $field = '<div class="field-div"><div class="form-group">';
        $field_name = 'item_field_'.$field_id;
        $value = false;
        if($id && $field_id) $value = $this->_get_field_value($id, $field_id);

        if($field_type == 1) {
            if($value === false) $value = $field_default;
            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            $field .= form_input($field_name, $value,'class="form-control"');
        }
        if($field_type == 2) {
            if($value === false) $value = $field_default;
            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            $field .= form_textarea($field_name, $value,'class="form-control"');
        }
        if($field_type == 3) {
            if($value === false) $value = $field_default;
            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            $field .= form_textarea($field_name, $value,'class="form-control htmleditor"');
            $field .= '<script>CKEDITOR.replace(\''.$field_name.'\');</script>';
        }
        if($field_type == 4) {
            $field_values = $this->_get_values_array($field_values);
            $key = array_search($field_default, $field_values);
            if($value === false) $value = ($key) ? $key : '';
            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            $field .= form_dropdown($field_name, $field_values, $value,'class="form-control"');
        }
        if($field_type == 5) {
            $field_values   = $this->_get_values_array($field_values);
            $field_default  = $this->_get_values_array($field_default);
            $selected_default = array();
            for($i=0; $i < count($field_default); $i++){
                $key = array_search($field_default[$i], $field_values);
                if($key || $key == 0) $selected_default[] = $key;
            }
            if($value === false) $selected = $selected_default;
            else $selected = $this->_get_values_array($value);
            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            $field .= form_multiselect($field_name.'[]', $field_values, $selected,'class="form-control"');
        }
        if($field_type == 6) {
            $field_values   = $this->_get_values_array($field_values);
            $field_default  = $this->_get_values_array($field_default);
            $selected_default = array();
            for($i=0; $i < count($field_default); $i++){
                $key = array_search($field_default[$i], $field_values);
                if($key || $key == 0) $selected_default[] = $key;
            }
            if($value === false) $selected = $selected_default;
            else $selected = $this->_get_values_array($value);

            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            foreach ($field_values AS $key1 => $value1) {
                $checked = (in_array($key1, $selected)) ? true : false;
                $field .= '<div class="checkbox"><label>';
                $field .= form_checkbox($field_name.'[]', $key1, $checked,'');
                $field .= ' ' . $value1 . '</label></div>';
            }
        }
        if($field_type == 7) {
            $field_values = $this->_get_values_array($field_values);
            $key = array_search($field_default, $field_values);
            if($value === false) $value = ($key) ? $key : '';

            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            foreach ($field_values AS $key1 => $value1) {
                $checked = ($value == $key1) ? true : false;
                $field .= '<div class="checkbox"><label>';
                $field .= form_radio($field_name, $key1, $checked,'');
                $field .= ' ' . $value1 . '</label></div>';
            }
        }
        if($field_type == 8) {
            if($value === false) $value = ($field_default != '') ? $field_default : '0000-00-00';
            $field .= '<label for="'.$field_name.'">'.$field_text.'</label>';
            $field .= $this->date_form_rus($value, $field_name);
        }

        $field .= '</div></div>';
        return $field;
    }

    function _get_values_array($string){
        if($string != '') {
            $pieces = explode(",", $string);
            array_walk($pieces, 'trim');
        } else {
            $pieces = array();
        }
        return $pieces;
    }

    function date_form_rus($date = '0000-00-00', $prefix = '')
    {
        $month = array(
            '00' => 'Не выбрано',
            '01' => 'Январь',
            '02' => 'Февраль',
            '03' => 'Март',
            '04' => 'Апрель',
            '05' => 'Май',
            '06' => 'Июнь',
            '07' => 'Июль',
            '08' => 'Август',
            '09' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь'
        );

        $day = substr($date, 8, 2);
        $mon = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        return '<div class="form-inline"><div class="form-group"><label for="'.$prefix.'_day">День</label>&nbsp;<input type="text" class="form-control mr10" name="'.$prefix.'_day" size="2" maxlength="2" value="' . $day . '"><label for="'.$prefix.'_mon">Месяц</label>&nbsp;' . form_dropdown($prefix.'_mon', $month, $mon, 'class="form-control mr10"') . '<label for="'.$prefix.'_year">Год</label>&nbsp;<input type="text" name="'.$prefix.'_year" size="4" class="form-control" maxlength="4" value="' . $year . '"></div></div>';
    }

    /**
     * Функция, генерирующая поля для ввода параметров (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_item_fields()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            $item_id  = $this->input->post('id', TRUE);
            $type_id  = $this->input->post('type', TRUE);
            $fields = '';

            $this->db->select('tf.field_id, f.field_type_back, f.field_name, tf.field_values, tf.field_default_values, f.field_label')
                ->from('w_shop_types_fields AS tf')
                ->join('w_shop_fields AS f', 'tf.field_id = f.field_id')
                ->where('tf.type_id', $type_id)
                ->order_by('tf.field_order');

            $query  = $this->db->get();

            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $fields .= $this->_get_field($row->field_id, $item_id, $row->field_name, $row->field_values, $row->field_default_values, $row->field_type_back);
                }
            }

            echo $fields;
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
        // $id текущей записи
        if($this->input->get('PME_sys_rec', TRUE)) $id = $this->input->get('PME_sys_rec', TRUE);
        elseif($this->input->post('PME_sys_rec', TRUE)) $id = $this->input->post('PME_sys_rec', TRUE);
        else $id = 0;

	    // Массив переменных из урла
        $uri_assoc_array = $this->uri->uri_to_assoc(1);

        // Получаем базовые настройки
        $opts = $this->Cms_myedit->get_base_opts();
		
		// Переопределяем кнопки
		$opts['buttons']['L']['up'] = array('add','save','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_shop_items';

        // Ключ
        $opts['key'] = 'item_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('item_sort');
        $opts['ui_sort_field'] = 'item_sort';

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

        // Активизируем родительский режим и управляем сессиями
        if($this->uri->segment(3) == 'lang' && preg_int ($this->uri->segment(4))) $this->session->unset_userdata('w_sitem_parent');

        if(isset($uri_assoc_array['parent'])){
            $this->session->set_userdata('w_sitem_parent', $uri_assoc_array['parent']);
        }
        if(!$this->session->userdata('w_sitem_parent')) {
            $this->session->set_userdata('w_sitem_parent', 0);
        }

        // ------------------------------------------------------------------------
        // Фильтрация вывода
        $opts['filters'] = array();

        // Фильтр по товарным категориям
        $cat_ids = array();
        if ($this->session->userdata('sitem_filter')) {
            if ($this->session->userdata('sitem_filter') != 999999999) {
                $this->db->select('item_id AS id');
                $this->db->from('w_shop_items_cats');
                $this->db->where('w_shop_items_cats.cat_id', $this->session->userdata('sitem_filter'));
                $query = $this->db->get();
                if ($query->num_rows() > 0)
                {
                    foreach ($query->result() as $row)
                    {
                        $cat_ids[] = $row->id;
                    }
                }
            } else {
                $this->db->distinct();
                $this->db->select('item_id AS id');
                $this->db->from('w_shop_items_cats');
                $query_a = $this->db->get();

                $n_ids = 0;
                if ($query_a->num_rows() > 0)
                {
                    foreach ($query_a->result() as $row_a)
                    {
                        $a_ids[] = $row_a->id;
                    }

                    $n_ids = join(',',$a_ids);
                }

                if($n_ids) {
                    $this->db->select('item_id AS id');
                    $this->db->from('w_shop_items');
                    $this->db->where('item_id NOT IN (' . $n_ids . ')');
                    $query = $this->db->get();

                    if ($query->num_rows() > 0) {
                        foreach ($query->result() as $row) {
                            $cat_ids[] = $row->id;
                        }
                    }
                }
            }
        }

        if (count($cat_ids) > 0){
            $ids = join(',',$cat_ids);
            $opts['filters'][] = "item_id IN (".$ids.")";
        } else {
            if($this->session->userdata('sitem_filter')) $opts['filters'][] = "item_id IN (0)";
        }

        // Фильтр по родителям
        $opts['filters'][] = "item_pid = '" . $this->session->userdata('w_sitem_parent') . "'";

        // Фильтр по языкам
        $opts['filters'][] = "item_lang_id = '" . $this->session->userdata('w_alang') . "'";

        // Триггеры
        // $this->opts['triggers']['insert']['after'] = '';
        // $this->opts['triggers']['update']['after'] = '';
        // $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/shop_item_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/shop_item_update_after.php';
        $opts['triggers']['delete']['after']  = APPPATH.'triggers/shop_item_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Товар';
        $opts['logtable_field'] = 'item_name';

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

        // ------------------------------------------------------------------------

        $opts['fdd']['item_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['item_pid'] = array(
            'name'          => 'Родитель',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 11,
            'default'       => $this->session->userdata('w_sitem_parent'),
            'sort'          => false
        );
        $opts['fdd']['item_name'] = array(
            'name'          => 'Название товара',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'tab'           => array (
                'name'      => 'Основные параметры',
                'default'   => true,
            ),
            'help'          => 'Введите название товара.'
        );
        $shop_page = $this->Cms_shop->get_shop_page();
        $opts['fdd']['item_url'] = array(
            'name'          => 'URL',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'URL'           => '/'.$shop_page.'/item/$value',
            'URLdisp'       => 'На сайте',
            'URLtarget'     => '_blank',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда слово на английском, которое будет выведено в URL. Разрешены латинские буквы, цифры, минус и символ подчеркивания. Во время ввода будет проведена автоматическая проверка данных.'
        );
        $opts['fdd']['item_article'] = array(
            'name'          => 'Артикул',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'help'          => 'Введите артикул.'
        );
        $cats_select = $this->get_cats_select($id);
        $opts['fdd']['item_cat'] = array(
            'name'     => 'Категории',
            'nodb'     => true,
            'select'   => 'M',
            'options'  => 'ACPL',
            'add_display'   => $cats_select,
            'change_display'=> $cats_select,
            'cell_func' => array(
                'model' => 'adm_shop_item',
                'func'  => 'get_cats_select'
            ),
            'required' => false,
            'sort'     => false,
            'help'     => 'Выберите из списка категории, в которых будет располагаться товар. Один и тот же товар может быть размещен в разных категориях.'
        );
        $opts['fdd']['item_vendor_id'] = array(
          'name'     => 'Производитель',
          'select'   => 'D',
          'options'  => 'ACPDV',
          'values2'  => $this->_get_vendors(),
          'required' => false,
          'sort'     => false,
          'help'     => 'Выберите из списка производителя.'
        );
        $opts['fdd']['item_cut'] = array(
            'name'          => 'Краткий текст',
            'select'        => 'T',
            'addcss'        => 'htmleditor',
            'options'       => 'ACPDV',
            'maxlen'        => 65535,
            'textarea'      => array(
                'rows'      => 5,
                'cols'      => 66
            ),
            'required'      => false,
            'sort'          => false,
            'escape'        => false,
            'help'          => 'Введите в это поле краткий текст для вывода в списке.'
        );
        $opts['fdd']['item_articles'] = array(
            'name'          => 'Тексты',
            'nodb'          => true,
            'options'       => 'ACP',
            'add_display'   => $this->get_articles(),
            'change_display'=> $this->get_articles(),
            'sort'          => false,
            'help'          => 'Заполните поля требуемыми текстами.'
        );
        $opts['fdd']['pic'] = array(
            'name'          => 'Обложка',
            'required'      => false,
            'sort'          => false,
            'size'          => '50',
            'nodb'          => true,
            'file'          => array (
                'tn'        => '_thumb',
                'url'       => $this->config->item('cms_shop_dir'),
                'multiple'  => false
            ),
            'help'          => 'Выберите картинку на своем компьютере для загрузки. Удаление картинки из режима редактирования приводит к ее безвозвратному удалению.'
        );

        // ------------------------------------------------------------------------

        $opts = array_merge_recursive((array)$opts, (array)$this->Cms_inclusions->get_admin_inclusions('shop'));

        // ------------------------------------------------------------------------

        $opts['fdd']['item_price'] = array(
            'name'          => 'Цена',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'save'          => true,
            'help'          => 'Введите цену. Дробная часть отделяется точкой.'
        );
        $opts['fdd']['item_price_old'] = array(
            'name'          => 'Старая цена',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'help'          => 'Введите цену. Дробная часть отделяется точкой.'
        );
        $opts['fdd']['item_price_curr'] = array(
            'name'          => 'Валюта',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                'RUB'         => 'Рубли',
                'USD'         => 'Доллары США',
                'EUR'         => 'Евро'
            ),
            'default'       => 'RUB',
            'help'          => 'Стоимость может быть указана в любой валюте, а на сайт будет выведена цена в рублях, пересчитанная по курсу ЦБ РФ.'
        );
        $opts['fdd']['item_avail'] = array(
            'name'          => 'Наличие',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '1'         => 'В наличии',
                '0'         => 'Не в наличии'
            ),
            'save'          => true,
            'default'       => 0,
            'help'          => 'Товар, которого нет в наличии нельзя положить в корзину, но он присутствует в каталоге.'
        );
        $opts['fdd']['item_label'] = array(
            'name'          => 'Акции',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '0'         => 'Не выбрано',
                '1'         => 'Хит продаж',
                '2'         => 'Новинка',
                '3'         => 'Акция'
            ),
            'save'          => true,
            'default'       => 0,
            'help'          => 'Выберите дополнительную маркировку товара.'
        );
		// Tags
		$opts = array_merge_recursive((array)$opts, (array)$this->Cms_tags->get_admin_opts($id, 'shop'));
        if($publish)
        {
            $opts['fdd']['item_active'] = array(
                'name'          => 'Статус',
                'select'        => 'D',
                'options'       => 'LACPDV',
                'values2'       => array (
                    '1'         => 'Активен',
                    '0'         => 'Неактивен'
                ),
                'save'          => true,
                'default'       => 0,
                'help'          => 'Статус на сайте. Если вы хотите, чтобы товар не было видно на сайте - сделайте его неактивным, т.е. совсем не обязательно удалять товар, чтобы его скрыть.'
            );
        }
        $opts['fdd']['item_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'LACPD',
            'default'       => $this->Cms_utils->get_max_sort('item_sort', 'w_shop_items'),
            'save'          => true,
            'sort'          => false
        );

        // ------------------------------------------------------------------------

		$opts['fdd']['item_type_id'] = array(
			'name'     => 'Тип',
			'select'   => 'D',
			'options'  => 'ACPDV',
			'values2'  => $this->_get_types(),
			'tab'      => 'Параметры',
			'required' => false,
			'sort'     => false,
			'help'     => 'Выберите из списка тип, он определит набор характеристик товара.'
		);
		$opts['fdd']['item_fields'] = array(
			'name'          => 'Данные',
			'nodb'          => true,
			'options'       => 'ACP',
			'add_display'   => '<div id="item_fields_area"></div>',
			'change_display'=> '<div id="item_fields_area"></div>',
			'sort'          => false,
			'help'          => 'Заполните поля требуемыми значениями.'
		);

        // ------------------------------------------------------------------------

        $opts['fdd']['item_meta_title'] = array(
            'name'          => 'Заголовок страницы',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'tab'           => 'Мета-информация',
            'help'          => 'Введите сюда заголовок страницы - заголовок окна браузера &lt;title&gt;.'
        );
        $opts['fdd']['item_meta_keywords'] = array(
            'name'          => 'Ключевые слова',
            'select'        => 'T',
            'options'       => 'ACPDV',
            'maxlen'        => 65535,
            'textarea'      => array(
                'rows'      => 5,
                'cols'      => 66
            ),
            'required'      => false,
            'sort'          => true,
            'escape'        => false,
            'help'          => 'Ключевые слова через запятую. Для поисковых систем. Это поле используется при продвижении сайта!'
        );
        $opts['fdd']['item_meta_description'] = array(
            'name'          => 'Описание',
            'select'        => 'T',
            'options'       => 'ACPDV',
            'maxlen'        => 65535,
            'textarea'      => array(
                'rows'      => 5,
                'cols'      => 66
            ),
            'required'      => false,
            'sort'          => true,
            'escape'        => false,
            'help'          => 'Одно-два предложения, описывающих содержимое страницы. Для поисковых систем. Это поле используется при продвижении сайта!'
        );
        $opts['fdd']['item_seo'] = array(
            'name'          => 'SEO текст',
            'select'        => 'T',
            'options'       => 'ACPDV',
            'maxlen'        => 65535,
            'addcss'        => 'htmleditor',
            'textarea'      => array(
                'rows'      => 5,
                'cols'      => 66
            ),
            'required'      => false,
            'sort'          => true,
            'escape'        => false,
            'help'          => 'Дополнительный текст для продвижении сайта!'
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['item_lang_id'] = array(
            'name'          => 'Язык',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('w_alang'),
            'sort'          => false
        );


        // ------------------------------------------------------------------------

        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_sitem_parent');
        $opts['parent_crumbs']  = $this->_get_crumbs();

		return $opts;
	}
}
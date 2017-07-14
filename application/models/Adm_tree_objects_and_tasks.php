<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостными рубриками
 */

class Adm_tree_objects_and_tasks extends CI_Model {

    private $forest_full = array();         // выборка объектов, преобразованная в древовидный массив
    private $oat_parent_list = array();     // выборка объектов, преобразованная в древовидный массив с минусами, для селекта
    private $type_list_dashed = array();    // выборка типов, преобразованная в древовидный массив с минусами, для селекта
    private $cont_list_dashed = array();    // выборка исполнителей, преобразованная в древовидный массив с минусами, для селекта
    private $link_list = array();           // выборка объектов, для селекта ссылок
    private $crumbs = array();              // UI крошки

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
        $this->load->model('Cms_myedit');

        // Сработает при наличи в POST полей photo_objects
        $this->Cms_myedit->mass_save('oats_conts', 'oat_id', 'cont_id', 'oc_id', 'w_tree_oat_contractors');

        // Функции по работе с данными объектов
        $this->load->model('Cms_tree_objects_and_tasks');

        parent::__construct();
    }

    // ----------------------------------- META -------------------------------------

    /**
	 * Функция, отдающая дополнительные параметры в <head>
	 *
	 * @access	public
	 * @return	string
	 */
    function get_meta()
    {
        if($this->input->get('PME_sys_rec', TRUE)) $id = $this->input->get('PME_sys_rec', TRUE);
        elseif($this->input->post('PME_sys_rec', TRUE)) $id = $this->input->post('PME_sys_rec', TRUE);
        else $id = 0;

        $meta = '<script>
        $(document).ready(function() {          
            var type = $(\'#PME_data_oat_type_id\').val();
            get_page_fields(type);
            get_page_stages(type);
            $(\'#PME_data_oat_type_id\').change(function() { get_page_fields(type); get_page_stages(type); });
        });

        function get_page_fields(type){
            $.post(\'/adm_tree_objects_and_tasks/p_oat_fields\', { type: type, id: "'.$id.'", '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(\'#oat_fields_area\').empty();
                $(\'#oat_fields_area\').append(result);
            });
        }
        
        function get_page_stages(type){
            $.post(\'/adm_tree_objects_and_tasks/p_oat_stages\', { type: type, id: "'.$id.'", '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(\'#oat_stages_area\').empty();
                $(\'#oat_stages_area\').append(result);
            });
        }

        </script>';

        $meta .= $this->Cms_myedit->get_ajax_default_format();

        return $meta;
    }

    // ------------------------------------ FIELDS ------------------------------------

    function _get_field_value($oat_id, $field_id){
        $query = $this->db->query('SELECT par_value FROM w_tree_parameters WHERE par_pid = "'.$oat_id.'" AND par_field_id = "'.$field_id.'" AND par_type = "oat"');
        $row = $query->row();
        if ($query->num_rows() > 0) {
            return $row->par_value;
        } else {
            return false;
        }
    }

    function _get_field($field_id = 0, $oat_id = 0, $field_name = '', $field_text = '', $field_values = '', $field_default = '', $field_type = 0){

        $this->load->helper('form');
        $field = '<div class="form-group">';
        $field_name = 'oat_field_'.$field_id;
        $value = false;
        if($oat_id && $field_id) $value = $this->_get_field_value($oat_id, $field_id);

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

        $field .= '</div>';
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
     * Функция, генерирующая текстовые поля (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_oat_fields()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            $oat_id  = $this->input->post('id', TRUE);
            $type_id = $this->input->post('type', TRUE);
            $fields = '';

            $this->db->select('w_tree_types_fields.field_id, field_type, field_name, field_values, field_default_values, field_label')
                ->from('w_tree_types_fields')
                ->join('w_tree_fields', 'w_tree_types_fields.field_id = w_tree_fields.field_id')
                ->where('type_id', $type_id);

            $query  = $this->db->get();

            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $fields .= $this->_get_field($row->field_id, $oat_id, $row->field_label, $row->field_name, $row->field_values, $row->field_default_values, $row->field_type);
                }
            }

            echo $fields;
        }
    }

    // ----------------------------------- STAGES -------------------------------------

    /**
     * Функция, генерирующая текстовые поля (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_oat_stages()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            $oat_id  = $this->input->post('id', TRUE);
            $type_id = $this->input->post('type', TRUE);
            $project_id = $this->session->userdata('oat_project_filter');
            $fields = '';

            $this->db->select('w_tree_types_stages.stage_id, stage_name')
                ->from('w_tree_types_stages')
                ->join('w_tree_stages', 'w_tree_types_stages.stage_id = w_tree_stages.stage_id')
                ->where('type_id', $type_id)
                ->order_by("stage_sort", "asc");

            $query  = $this->db->get();

            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $fields .= $this->_get_stage_field($row->stage_id, $oat_id, $row->stage_name, $project_id);
                }
            }

            echo $fields;
        }
    }

    function _get_stage_field_value($oat_id, $stage_id, $project_id){
        $query = $this->db->query('SELECT stage_status, stage_date FROM w_tree_oat_stages WHERE oat_id = "'.$oat_id.'" AND stage_id = "'.$stage_id.'" AND project_id = "'.$project_id.'"');
        $row = $query->row();
        if ($query->num_rows() > 0) {
            return array(
                'status' => $row->stage_status,
                'date'   => $row->stage_date
            );
        } else {
            return false;
        }
    }

    function _get_stage_field($stage_id = 0, $oat_id = 0, $stage_name = '', $project_id = 0){
        $this->load->helper('form');
        $field_name_status = 'oat_stage_status_'.$stage_id;
        $field_name_date = 'oat_stage_date_'.$stage_id;
        $field_name_all = 'oat_stage_all_'.$stage_id;
        $value = false;
        if($oat_id && $stage_id) $value = $this->_get_stage_field_value($oat_id, $stage_id, $project_id);
        if($value === false) {
            $value_status = false;
            $value_date = '0000-00-00';
        } else {
            $value_status = ($value['status']) ? true : false;
            $value_date = $value['date'];
        }
        $field  = '<div class="form-inline py5" style="border-bottom: #ccc 1px solid;">';
        $field .= '<p><strong>'.$stage_name.'</strong></p>';

        // Status
        $field .= '<div class="checkbox px10">';
        $field .= '<label>';
        $field .= form_checkbox($field_name_status, 1, $value_status,'');
        $field .= 'Выполнено?';
        $field .= '</label>';
        $field .= '</div>';

        // Calendar
        $field .= '<div class="form-group">';
        $field .= '<label for="'.$field_name_date.'" class="pr5">Дата</label>';
        $field .= form_input($field_name_date, $value_date,'class="form-control stage-datepicker-'.$stage_id.'"');
        $field .= '<script>
        $( \'.stage-datepicker-'.$stage_id.'\' ).datepicker( { changeMonth: true, changeYear: true } );
        $( \'.stage-datepicker-'.$stage_id.'\' ).datepicker( \'option\', \'dateFormat\', \'yy-mm-dd\' );
        $( \'.stage-datepicker-'.$stage_id.'\' ).datepicker( $.datepicker.regional[ \'ru\' ] );
        $( \'.stage-datepicker-'.$stage_id.'\' ).val(\''.$value_date.'\');
</script>';
        $field .= '</div>';

        // All
        $field .= '<div class="checkbox px10">';
        $field .= '<label>';
        $field .= form_checkbox($field_name_all, 1, false,'');
        $field .= 'Применить для всех?';
        $field .= '</label>';
        $field .= '</div>';

        $field .= '</div>';
        return $field;
    }

    // ----------------------------------- PATTERNS -------------------------------------

    /**
     * Функция, импортирующая паттерн
     *
     * @access	private
     * @return	void
     */
    function _import_pattern ($id, $project_id = 0, $parent_id = 0){
        $this->db->select('*')
            ->from('w_tree_objects_and_tasks_patterns')
            ->where('oatp_id', $id)
            ->order_by("oatp_pid", "asc")
            ->order_by("oatp_sort", "asc");
        $query_parent  = $this->db->get();

        if ($query_parent->num_rows() > 0)
        {
            $row = $query_parent->row();
            $parent_id = $this->_object_insert ($parent_id, $row->oatp_type_id, $row->oatp_name, $row->oatp_desc, $row->oatp_sort, $row->oatp_id, $project_id);
        }

        $this->db->select('*')
            ->from('w_tree_objects_and_tasks_patterns')
            ->order_by("oatp_pid", "asc")
            ->order_by("oatp_sort", "asc");
        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            @$forest =& $this->tree->get_tree('oatp_id', 'oatp_pid', $query->result_array(), $id);
            $this->_mkobject ($forest, $parent_id, $project_id);
        }
    }

    /**
     * Обход дерева всех требуемых объектов шаблона и внесение их в шаблоны
     *
     * @access  private
     * @return  void
     */
    function _mkobject ($forest, $pid = 0, $project_id = 0)
    {
        foreach ($forest as $tree)
        {
            $p_id = $this->_object_insert ($pid, $tree['oatp_type_id'], $tree['oatp_name'], $tree['oatp_desc'], $tree['oatp_sort'], $tree['oatp_id'], $project_id);

            if (isset($tree['nodes'])) {
                $this->_mkobject($tree['nodes'], $p_id, $project_id);
            }
        }
    }

    /**
     * Внесение данных в объекты
     *
     * @access  private
     * @return  int
     */
    function _object_insert ($pid, $type_id, $name, $desc, $sort, $pattern_id, $project_id = 0)
    {
        // Вносим шаблон, в объекты
        $data = array(
            'oat_id'            => '',
            'oat_pid'           => $pid,
            'oat_type_id'       => $type_id,
            'oat_project_id'    => $project_id,
            'oat_name'          => $name,
            'oat_desc'          => $desc,
            'oat_sort'          => $sort,
            'oat_user_id'       => $this->cms_user->get_user_id(),
            'oat_lang_id'       => $this->session->userdata('w_alang')
        );
        $this->db->insert('w_tree_objects_and_tasks', $data);
        $id = $this->db->insert_id();

        // Вносим данные
        $query = $this->db->get_where('w_tree_parameters', array('par_pid' => $pattern_id, 'par_type' => 'oatp'));

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                // Вносим параметры
                $data = array(
                    'par_id'        => '',
                    'par_pid'       => $id,
                    'par_type'      => 'oat',
                    'par_field_id'  => $row->par_field_id,
                    'par_value'     => $row->par_value
                );
                $this->db->insert('w_tree_parameters', $data);
            }
        }

        return $id;
    }

    /**
     * Функция, отдающая фильтр шаблонов
     *
     * @access	private
     * @return	string
     */
    function _get_pattern_filter (){

        if($this->input->post('oat_pattern', true) !== FALSE && preg_int($this->input->post('oat_pattern', true)) && $this->input->post('oat_pattern', true) != 0)
        {
            if($this->session->userdata('oat_project_filter')) $project = $this->session->userdata('oat_project_filter');
            else $project = 0;
            $this->_import_pattern ($this->input->post('oat_pattern', true), $project, $this->session->userdata('w_oat_parent'));
        }

        // Получаем данные
        $this->db->select('oatp_id, oatp_name, oatp_group_name, w_tree_objects_and_tasks_patterns.oatp_group_id')
            ->from('w_tree_objects_and_tasks_patterns')
            ->join('w_tree_objects_and_tasks_patterns_groups', 'w_tree_objects_and_tasks_patterns.oatp_group_id = w_tree_objects_and_tasks_patterns_groups.oatp_group_id', 'left')
            ->where('oatp_pid', 0)
            ->order_by("w_tree_objects_and_tasks_patterns.oatp_group_id", "asc");

        $query  = $this->db->get();

        $filter  = '<h6 class="m0 mb10">Выберите шаблон</h6>';
        $filter .= form_open('/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/', array('class' => 'm0'));
        $filter .= '<div class="row"><div class="col-xs-9">';
        $filter .= '<select name="oat_pattern" class="span12 m0 form-control select2">';
        $filter .= '<option value="0">Не выбрано</option>';

        if ($query->num_rows() > 0) {
            $optgroup = '0';
            foreach ($query->result() as $row)
            {
                if ($row->oatp_group_id == 0) $row->oatp_group_name = 'Вне групп';

                if($optgroup != $row->oatp_group_name) {
                    if($optgroup != '0') $filter .= '</optgroup>';
                    $filter .= '<optgroup label="'.$row->oatp_group_name.'">';
                    $optgroup = $row->oatp_group_name;
                }

                $filter .= '<option value="'.$row->oatp_id.'">'.$row->oatp_name.'</option>';
            }
        }

        $filter .= '</optgroup>';
        $filter .= '</select>';
        $filter .= '</div><div class="col-xs-3">';
        $filter .= '<button type="submit" class="btn btn-info">Импортировать</button>';
        $filter .= '</div></div>';
        $filter .= '</form>';

        return $filter;
    }

    // ----------------------------------- PROJECT FILTER -------------------------------------

    /**
     * Функция, отдающая фильтр проектов
     *
     * @access	private
     * @return	array
     */
    function _get_project_filter (){
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

        if (!$this->session->userdata('oat_project_filter'))
        {
            $this->session->set_userdata(array('oat_project_filter' => 0));
        }

        if(($this->input->post('oat_project_filter', true) || $this->input->post('oat_project_filter', true) == '0') && preg_int($this->input->post('oat_project_filter', true)))
        {
            $this->session->set_userdata(array('oat_project_filter' => $this->input->post('oat_project_filter', true)));
        }

        if($this->input->get('PME_sys_rec', TRUE))$this->_check_project($this->input->get('PME_sys_rec', TRUE));

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите проект',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'oat_project_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('oat_project_filter'),
            'filter_values' => $filter_values
        );

        return $data;
    }

    // ----------------------------------- FILTERS -------------------------------------

    /**
	 * Функция, отдающая фильтры
	 *
	 * @access	public
	 * @return	string
	 */
    function get_filters()
    {
        $filters = '
        <div class="row">
            <div class="col-xs-12"><div class="p20 ui-block">
            <div class="row mt10">
                <div class="col-xs-6">'
            .$this->load->view('admin/filter_default', $this->_get_project_filter(), true).
            '</div>
                <div class="col-xs-6">'
            .$this->_get_pattern_filter().
            '</div>                 
            </div>
            </div></div>
        </div>
        ';

        return $filters;
    }

    // ----------------------------------- OUTPUT -------------------------------------

    /**
	 * Функция, отдающая основной интерфейс
	 *
	 * @access	public
	 * @return	string
	 */
    function get_output()
    {
        $this->Cms_tree_objects_and_tasks->init($this->session->userdata('oat_project_filter'));
        $this->forest_full = $this->Cms_tree_objects_and_tasks->get_initial_forest();
        $this->load->library('myedit', $this->_get_crud_model());
        return $this->myedit->get_output();
    }

    /**
     * Функция, проверяющая, соответствует ли сессия проекта с номером проекта у редактируемого объекта
     *
     * @access	private
     * @param   integer
     * @return	void
     */
    function _check_project($id){
        $this->db->select('oat_project_id AS oid')
            ->from('w_tree_objects_and_tasks')
            ->where('oat_id', $id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            if($this->session->userdata('oat_project_filter') != $row->oid) $this->session->set_userdata(array('oat_project_filter' => $row->oid));
        }
    }

    // ------------------------------------- ДРЕВОВИДНЫЕ СЕЛЕКТЫ -----------------------------------

    /**
     * Функции, формирующая массив объектов для ссылок
     *
     * @access  private
     * @return  array
     */
    function _get_link_list()
    {
        $this->link_list[0] = 'Реальный объект (не ссылка)';

        foreach ($this->Cms_tree_objects_and_tasks->get_initial_total_oats() as $key => $value){
            $this->link_list[$key] = $value['name'];
        }
    }

    /**
     * Функции, формирующая массив родителей
     *
     * @access  private
     * @return  array
     */
    function _get_parent_list()
    {
        $this->oat_parent_list[0] = 'Верхний уровень';
        $this->_get_parent_dashed_array ($this->Cms_tree_objects_and_tasks->get_full_forest(), 'oat_id', 'oat_pid', 'oat_name', '');
    }

    function _get_parent_dashed_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            if($tree[$level_name] != '') $this->oat_parent_list[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $this->_get_parent_dashed_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
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
        $this->type_list_dashed[0] = 'Тип не выбран';
        $this->_get_type_dashed_array ($this->Cms_tree_objects_and_tasks->get_forest_types(), 'type_id', 'type_pid', 'type_name', '');
    }

    function _get_type_dashed_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            $this->type_list_dashed[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $this->_get_type_dashed_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
        }
    }

    /**
     * Функция, формирующая массив исполнителей
     *
     * @access  private
     * @return  array
     */
    function _get_cont_list()
    {
        $this->cont_list_dashed[0] = 'Исполнитель не выбран';
        $this->_get_cont_dashed_array ($this->Cms_tree_objects_and_tasks->get_forest_conts(), 'cont_id', 'cont_pid', 'cont_name', '');
    }

    function _get_cont_dashed_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            $this->cont_list_dashed[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $this->_get_cont_dashed_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
        }
    }

    // ----------------------------------- JSTREE ДЕРЕВЬЯ -------------------------------------

    function _get_link($id)
    {
        foreach ($this->Cms_tree_objects_and_tasks->get_forest_query() as $row)
        {
            if($row['oat_id'] == $id){
                $result = array(
                    'obj_link'  => $row['oat_link_id'],
                    'type_link' => $row['oat_type_link_id'],
                    'cont_link' => $row['oat_cont_link_id']
                );
            }
        }

        if(is_array($result)) return $result;
        else return false;
    }

    function _get_name($id)
    {
        foreach ($this->Cms_tree_objects_and_tasks->get_forest_query() as $row)
        {
            if($row['oat_id'] == $id){
                $name = $row['oat_name'];
            }
        }
        if(isset($name)) return $name;
        else return false;
    }

    function _get_cont_name($id)
    {
        foreach ($this->Cms_tree_objects_and_tasks->get_forest_conts_query() as $row)
        {
            if($row['cont_id'] == $id){
                $name = $row['cont_name'];
            }
        }
        if(isset($name)) return $name;
        else return false;
    }

    function _get_type_name($id)
    {
        foreach ($this->Cms_tree_objects_and_tasks->get_forest_types_query() as $row)
        {
            if($row['type_id'] == $id){
                $name = $row['type_name'];
            }
        }
        if(isset($name)) return $name;
        else return false;
    }

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


        if($links = $this->_get_link($key)) {

            $link_obj  = $links['obj_link'];
            $link_type = $links['type_link'];
            $link_cont = $links['cont_link'];

            if(($link_obj) || (!$link_obj && !$link_type && !$link_cont)) {
                $key = ($link_obj) ? $link_obj : $key;
                $display = '';

                // Формируем нужную ветку
                $this->Cms_tree_objects_and_tasks->unset_tree_array();
                $this->Cms_tree_objects_and_tasks->get_tree($this->Cms_tree_objects_and_tasks->get_initial_forest(), $key);
                $forest = $this->Cms_tree_objects_and_tasks->get_tree_array();

                //echo '<pre>'.print_r($forest, true).'</pre>';

                if ($link_obj) $display .= '<div class="link_area">';
                $display .= '<div class="jstree">' . $this->_reformat_forest($forest) . '</div>';
                if ($link_obj) $display .= '</div>';
            }

            if($link_type) {

                $display = '<div class="type_area">';

                $this->Cms_tree_objects_and_tasks->unset_tree_array();
                $this->Cms_tree_objects_and_tasks->get_tree($this->Cms_tree_objects_and_tasks->get_forest_types(), $link_type, 'type_id');
                $forest = $this->Cms_tree_objects_and_tasks->get_tree_array();

                $this->Cms_tree_objects_and_tasks->set_crumb_keys ($forest, 'type_id', 'type_pid');
                $report_crumbs = $this->Cms_tree_objects_and_tasks->get_report_crumbs();

                foreach ($this->Cms_tree_objects_and_tasks->get_total_types() AS $key => $value){
                    if (in_array($key, $report_crumbs)) {
                        $display .= '<p><strong>' . $value['name'] . ' (' . $value['count'] . ')</strong></p>';
                        foreach ($value['oats'] AS $oat_key => $oat_value) {
                            $count = ' (' . $oat_value['count'] . ')';

                            // Формируем нужную ветку
                            $this->Cms_tree_objects_and_tasks->unset_tree_array();
                            $this->Cms_tree_objects_and_tasks->get_tree($this->Cms_tree_objects_and_tasks->get_initial_forest(), $oat_key);
                            $forest = $this->Cms_tree_objects_and_tasks->get_tree_array();

                            $display .= '<div class="jstree">'.$this->_reformat_forest($forest, '', $count).'</div>';
                        }
                        $display .= '<br>';
                    }
                }
                $display .= '</div>';
            }

            if($link_cont) {

                $display = '<div class="cont_area">';

                $this->Cms_tree_objects_and_tasks->unset_tree_array();
                $this->Cms_tree_objects_and_tasks->get_tree($this->Cms_tree_objects_and_tasks->get_forest_conts(), $link_cont, 'cont_id');
                $forest = $this->Cms_tree_objects_and_tasks->get_tree_array();

                $this->Cms_tree_objects_and_tasks->set_crumb_keys ($forest, 'cont_id', 'cont_pid');
                $report_crumbs = $this->Cms_tree_objects_and_tasks->get_report_crumbs();

                $total_oats = $this->Cms_tree_objects_and_tasks->get_total_oats();

                foreach ($this->Cms_tree_objects_and_tasks->get_forest_conts_query() AS $value){
                    if (in_array($value['cont_id'], $report_crumbs)) {
                        $chains = '';
                        $total = 0;
                        $cont_name = $this->_get_cont_name($value['cont_id']);

                        $this->db->select('w_tree_oat_contractors.oat_id')
                            ->from('w_tree_oat_contractors')
                            ->join('w_tree_objects_and_tasks', 'w_tree_oat_contractors.oat_id = w_tree_objects_and_tasks.oat_id')
                            ->where('w_tree_oat_contractors.cont_id', $value['cont_id'])
                            ->where('oat_project_id', $this->session->userdata('oat_project_filter'));
                        $query = $this->db->get();

                        if ($query->num_rows() > 0) {
                            foreach ($query->result() as $row) {
                                $count = ' (' . $total_oats[$row->oat_id]['count'] . ')';

                                // Формируем нужную ветку
                                $this->Cms_tree_objects_and_tasks->unset_tree_array();
                                $this->Cms_tree_objects_and_tasks->get_tree($this->Cms_tree_objects_and_tasks->get_initial_forest(), $row->oat_id);
                                $forest = $this->Cms_tree_objects_and_tasks->get_tree_array();

                                $chains .= '<div class="jstree">' . $this->_reformat_forest($forest, '', $count) . '</div>';
                                $total = $total + $total_oats[$row->oat_id]['count'];
                            }
                        }
                        $display .= '<p><strong>' . $cont_name . ' (' . $total . ')</strong></p>';
                        $display .= $chains;
                        $display .= '<br>';
                    }
                }

                $display .= '</div>';
            }

            return $display;
        }
    }

    /**
     * Переформатирование объектов в ul-li список для JSTREE
     *
     * @access	private
     * @param   array
     * @param   string
     * @return	string
     */

    function _reformat_forest ($forest, $menu = '', $count = '')
    {
        $menu .= '<ul>';
        foreach ($forest as $tree)
        {
            $menu .= '<li>';

            if($tree['oat_link_id'] && !$tree['oat_type_link_id'] && !$tree['oat_cont_link_id']){
                $menu .= '<a href="/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/'.'?PME_sys_fl=0&PME_sys_fm=0&PME_sys_sfn[0]=8&PME_sys_operation=PME_op_Change&PME_sys_rec=';
                $menu .= $tree['oat_link_id'];
                $menu .= '">';
                $menu .= '<span class="tree_red_link">'.(($tree['oat_name'] == '') ? $this->_get_name($tree['oat_link_id']) : $tree['oat_name']).'</span>';
                $menu .= '</a>';
            }
            if(!$tree['oat_link_id'] && $tree['oat_type_link_id'] && !$tree['oat_cont_link_id']){
                $menu .= '<a><span class="tree_orange_link">'.(($tree['oat_name'] == '') ? $this->_get_type_name($tree['oat_type_link_id']) : $tree['oat_name']).'</span></a>';
            }
            if(!$tree['oat_link_id'] && !$tree['oat_type_link_id'] && $tree['oat_cont_link_id']){
                $menu .= '<a><span class="tree_blue_link">'.(($tree['oat_name'] == '') ? $this->_get_cont_name($tree['oat_cont_link_id']) : $tree['oat_name']).'</span></a>';
            }
            else {
                $menu .= '<a href="/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/'.'?PME_sys_fl=0&PME_sys_fm=0&PME_sys_sfn[0]=8&PME_sys_operation=PME_op_Change&PME_sys_rec=';
                $menu .= $tree['oat_id'];
                $menu .= '">';
                $menu .= $tree['oat_name'];
                $menu .= '</a>';
            }
            $menu .= $count;
            if (isset($tree['nodes'])) $menu = $this->_reformat_forest($tree['nodes'], $menu);
            $menu .= '</li>';

        }
        $menu .= '</ul>';

        return $menu;
    }

    // -------------------------------- PARENTS AND CRUMBS ----------------------------------------

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
        $this->set_crumbs($this->forest_full, 'oat_id', 'oat_pid', 'oat_name', '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/', $this->session->userdata('w_oat_parent'));
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

                    if ($tree[$parent_name] != 0) $this->set_crumbs($this->forest_full, $id_name, $parent_name, $level_name, $link, $tree[$parent_name]);
                }
                else
                {
                    if(isset($tree['nodes'])) $this->set_crumbs($tree['nodes'], $id_name, $parent_name, $level_name, $link, $active_id);
                }
            }
        }
    }

    // ------------------------------- SORT -----------------------------------------

    /**
     * Извлекаем максимальное значение сортировки
     *
     * @access  private
     * @return  int
     */
    function _get_max_sort() {
        $this->db->select_max('oat_sort', 'oat_sort');
        $this->db->where('oat_pid', $this->session->userdata('w_oat_parent'));
        $query = $this->db->get('w_tree_objects_and_tasks');
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->oat_sort+10;
        } else {
            return 10;
        }
    }

    // ------------------------------- ФОРМА ОБЪЕКТ К ИСПОЛНИТЕЛЯМ -----------------------------------------

    /**
     * Функция, список выбора для пересечений с событиями
     *
     * @access	private
     * @param   int - id для выборки
     * @param   mixed - значение поля (если требуется)
     * @param   string - вид выборки
     * @return	string
     */

    function get_conts_select($key, $value='', $mode='change')
    {
        // Получаем массивы с данными для формирования селекта
        $select_array = $this->_oats_conts($key, $mode);

        // Строим селект
        $this->load->helper('form');
        $opts = 'class="w300 js-data-ajax-conts-'.$key.'"';
        $form = form_multiselect('oats_conts_'.$key.'[]', $select_array['values'], $select_array['defaults'], $opts);

        // Получаем js-код для этого поля
        $script = $this->Cms_myedit->get_ajax($key, '/adm_tree_objects_and_tasks/p_cont_generate', 'conts', 1);

        // Компануем и выводим все это ячейку таблицы списка
        return $form.$script;
    }

    /**
     * Функция, генерирующая список событий для <select> (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_cont_generate()
    {
        $rights = $this->cms_user->get_user_rights();
        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            echo $this->Cms_myedit->get_ajax_query ('cont_id', 'cont_name', 'w_tree_contractors', 1);
        }
    }

    /**
     * Функция, отдающая массивы пересечений ФОТОК и СОБЫТИЙ для формирования селекта с дефолтными значениями
     *
     * @access	private
     * @param   int - id для выборки
     * @param   string - вид выборки
     * @return	array
     */
    function _oats_conts($key, $mode='change')
    {
        $val_arr = array();
        $val_arr_active = array();

        $this->db->select('oc_id, oat_id, w_tree_oat_contractors.cont_id, cont_name')
            ->from('w_tree_oat_contractors')
            ->join('w_tree_contractors', 'w_tree_contractors.cont_id = w_tree_oat_contractors.cont_id')
            ->where('oat_id', $key);

        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $val_arr[$row->cont_id] = $row->cont_name;
                $val_arr_active[] = $row->cont_id;
            }

            $data = array(
                'values'    => $val_arr,
                'defaults'  => $val_arr_active,
                'total'     => $query->num_rows()
            );

            return $data;
        }
        else {
            $data = array(
                'values'    => $val_arr,
                'defaults'  => $val_arr_active,
                'total'     => 0
            );
            return $data;
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
        $opts['tb'] = 'w_tree_objects_and_tasks';

        // Ключ
        $opts['key'] = 'oat_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('oat_sort');
        $opts['ui_sort_field'] = 'oat_sort';

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
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/tree_oat_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/tree_oat_update_after.php';
        $opts['triggers']['delete']['after']  = APPPATH.'triggers/tree_oat_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Объект';
        $opts['logtable_field'] = 'oat_name';

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

        // Заполняем требуемые для интерфейса переменные значениями
        $this->_get_link_list();
        $this->_get_cont_list();
        $this->_get_type_list();
        $this->_get_parent_list();

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

        $opts['fdd']['oat_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['oat_name'] = array(
            'name'          => 'Название',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'tab'           => array (
                'name'      => 'Основные параметры',
                'default'   => true,
            ),
            'cell_func' => array(
                'model' => 'adm_tree_objects_and_tasks',
                'func'  => 'get_childs'
            ),
            'help'          => 'Введите название.'
        );
        $opts['fdd']['oat_desc'] = array(
            'name'          => 'Текст',
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
            'help'          => 'Введите в это поле общее описание пункта.'
        );
        $opts['fdd']['oat_amount'] = array(
            'name'          => 'Кол-во',
            'select'        => 'T',
            'options'       => 'LACPD',
            'default'       => 1,
            'sort'          => false
        );
        $opts['fdd']['oat_conts'] = array(
            'name'     => 'Исполнители',
            'nodb'     => true,
            'select'   => 'M',
            'options'  => 'ACP',
            'add_display'   => $this->get_conts_select($id, '', 'add'),
            'change_display'=> $this->get_conts_select($id, '', 'change'),
            'cell_func' => array(
                'model' => 'adm_tree_objects_and_tasks',
                'func'  => 'get_conts_select'
            ),
            'required' => false,
            'sort'     => false,
            'help'     => 'Выберите из списка исполнителей, ответственных за данный пункт.'
        );
        $opts['fdd']['oat_type_id'] = array(
            'name'          => 'Тип',
            'select'        => 'D',
            'options'       => 'LACPD',
            'addcss'        => 'select2',
            'values2'       => $this->type_list_dashed,
            'default'       => 0,
            'required'      => true,
            'sort'          => false,
            'help'          => 'Выберите из списка тип.'
        );
        $opts['fdd']['oat_fields'] = array(
            'name'          => 'Данные',
            'nodb'          => true,
            'options'       => 'ACP',
            'add_display'   => '<div id="oat_fields_area"></div>',
            'change_display'=> '<div id="oat_fields_area"></div>',
            'sort'          => false,
            'help'          => 'Заполните поля требуемыми значениями.'
        );
        $opts['fdd']['oat_stages'] = array(
            'name'          => 'Этапы',
            'nodb'          => true,
            'options'       => 'ACP',
            'add_display'   => '<div id="oat_stages_area"></div>',
            'change_display'=> '<div id="oat_stages_area"></div>',
            'sort'          => false,
            'help'          => 'Отметьте требуемые этапы и даты.'
        );
        $opts['fdd']['oat_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'LACPD',
            'default'       => $this->_get_max_sort(),
            'save'          => true,
            'sort'          => false
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['oat_link_id'] = array(
            'name'          => 'Ссылка на объект',
            'select'        => 'D',
            'options'       => 'ACPD',
            'addcss'        => 'select2',
            'values2'       => $this->link_list,
            'default'       => '0',
            'tab'           => 'Ссылки и родители',
            'required'      => true,
            'sort'          => false,
            'help'          => 'Будет ли этот пункт реальным объектом или ссылкой на объект'
        );
        $opts['fdd']['oat_type_link_id'] = array(
            'name'          => 'Ссылка на тип',
            'select'        => 'D',
            'options'       => 'ACPD',
            'addcss'        => 'select2',
            'values2'       => $this->type_list_dashed,
            'default'       => 0,
            'required'      => true,
            'sort'          => false,
            'help'          => 'Будет ли этот пункт реальным объектом или ссылкой на отчет по типу.'
        );
        $opts['fdd']['oat_cont_link_id'] = array(
            'name'          => 'Ссылка на исполнителя',
            'select'        => 'D',
            'options'       => 'ACPD',
            'addcss'        => 'select2',
            'values2'       => $this->cont_list_dashed,
            'default'       => 0,
            'required'      => true,
            'sort'          => false,
            'help'          => 'Будет ли этот пункт реальным объектом или ссылкой на отчет по исполнителю.'
        );
        $opts['fdd']['oat_active'] = array(
            'name'          => 'Статус',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '1'         => 'Активен',
                '0'         => 'Неактивен'
            ),
            'default'       => 1,
            'help'          => 'Участвует ли эта ветка в отчетах, нужно ли считать элементы, находящиеся в ней.'
        );
        $opts['fdd']['oat_pid'] = array(
            'name'          => 'Родительский раздел',
            'select'        => 'D',
            'options'       => 'ACPD',
            'addcss'        => 'select2',
            'values2'       => $this->oat_parent_list,
            'default'       => $this->session->userdata('w_oat_parent'),
            'required'      => true,
            'sort'          => false,
            'help'          => 'Проставляется автоматически при заведении. Можно использовать, когда требуется перенести объект (ветку) в другой раздел.'
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['oat_print'] = array(
            'name'     => '&nbsp;',
            'nodb'     => true,
            'options'  => 'L',
            'cell_func' => array(
                'model' => 'adm_tree_objects_and_tasks',
                'func'  => 'get_links'
            ),
            'escape'   => false
        );

        $script = '
                <script>
                $(document).ready(function () {
                $(\'#mkpattern_$key\').click(function(){
                    if(confirm("Вы действительно хотите из этой ветки сделать шаблон?")){
                        $.ajax({
                            type: "POST",
                            url: "/adm_tree_objects_and_tasks_patterns/p_mkpattern",
                            data: { id: "$key", p_id: "'.$this->session->userdata('oat_project_filter').'", '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }
                        }).done(function(result) {
                            if (result == 1) {
                                $(\'#mkpattern_$key\').removeClass(\'btn-success\').addClass(\'btn-default\').html(\'<i class="glyphicon glyphicon-retweet"></i>&nbsp;&nbsp;Шаблон был сделан!\');
                            } else {
                                $(\'#mkpattern_$key\').removeClass(\'btn-success\').addClass(\'btn-danger\').html(\'<i class="glyphicon glyphicon-retweet"></i>&nbsp;&nbsp;Шаблон не сделан!\');
                            }
                        });
                    }
                });
                });
                </script>
            ';
        $opts['fdd']['restore'] = array(
            'name'      => 'Шаблон',
            'select'    => 'T',
            'options'   => 'L',
            'nodb'      => true,
            'cell_display'   => '<div id="mkpattern_$key" class="btn btn-sm btn-success"><i class="glyphicon glyphicon-retweet icon-white"></i>&nbsp;&nbsp;Сделать шаблон</a>'.$script,
            'maxlen'    => 255
        );
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

    function get_links($key, $value)
    {
        $link = '<a href="/-/project/'.$this->session->userdata('oat_project_filter').'/oat/'.$key.'/" target="_blank">Древовидный отчет</a><br>';
        $link .= '<a href="/-/project/'.$this->session->userdata('oat_project_filter').'/oat/'.$key.'/linerep" target="_blank">Линейный отчет</a><br>';
        $link .= '<a href="/-/project/'.$this->session->userdata('oat_project_filter').'/oat/'.$key.'/outlay" target="_blank">Смета</a><br>';
        $link .= '<a href="/-/project/'.$this->session->userdata('oat_project_filter').'/oat/'.$key.'/stages" target="_blank">Этапы</a><br>';
        return $link;
    }
}
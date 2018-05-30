<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление
 */

class Adm_shop_types extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        $this->load->model('Cms_utils');
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
        $i = 1;
        $this->db->select('f.field_id, f.field_type_back, f.field_type_front, f.field_name');
        $this->db->from('w_shop_fields AS f');
        $this->db->where('f.field_active', 1);
	    $this->db->where('f.field_lang_id', $this->session->userdata('w_alang'));
        $this->db->order_by("f.field_name", "asc");
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result = array();
            foreach ($query->result() as $row)
            {
                $this->db->select('field_values, field_default_values, field_filter, field_modification, field_table, field_order');
                $this->db->from('w_shop_types_fields');
                $this->db->where('field_id', $row->field_id);
                $this->db->where('type_id', $id);
                $query2 = $this->db->get();

                if ($query2->num_rows() > 0)
                {
                    $row2 = $query2->row();
                    $result[$row2->field_order] = array(
                        'field_id'          => $row->field_id,
                        'field_name'        => $row->field_name,
                        'type_back'         => $row->field_type_back,
                        'type_front'        => $row->field_type_front,
                        'values'            => $row2->field_values,
                        'default_values'    => $row2->field_default_values,
                        'filter'            => $row2->field_filter,
                        'modification'      => $row2->field_modification,
                        'table'             => $row2->field_table,
                        'checked'           => true
                    );
                } else {
                    $result[$i+1000] = array(
                        'field_id'          => $row->field_id,
                        'field_name'        => $row->field_name,
                        'type_back'         => $row->field_type_back,
                        'type_front'        => $row->field_type_front,
                        'values'            => '',
                        'default_values'    => '',
                        'filter'            => 0,
                        'modification'      => 0,
                        'table'             => 0,
                        'checked'           => false
                    );
                }

                $i++;
            }

            ksort($result);
            $result = array_values($result);
            $fields = $this->_get_field_tpl($result);
        }
        else
        {
            $fields = '';
        }

        return '<div id="fields_area">'.$fields.'</div>';
    }

    /**
     * Функция, отдающая вспомогательный html для редактирования статей
     *
     * @access  public
     * @param   int - номер блока
     * @param   string - статья
     * @param   int - номер фона
     * @param   int - номер вида
     * @return  string
     */
    function _get_field_tpl($data)
    {
    	$response = '';
        foreach ($data AS $key => $value) {

            $id = $key + 1;

            $type = (in_array($value['type_back'], array('4', '5', '6', '7'))) ? 1 : 0;

            $response .= '<div class="field-div" data-id="' . $id . '">';
            $response .= '<div class="field-header">';
            $response .= '<div class="field-title"><label><input class="field_active" data-id="' . $id . '" name="field_' . $value['field_id'] . '" value="' . $value['field_id'] . '" type="checkbox"';
            $response .= ($value['checked']) ? ' checked' : '';
            $response .= '> ' . $value['field_name'] . '</label></div>';
            $response .= '<div class="field-buttons-div">
<button class="btn btn-default btn-xs field-button-move field-button-up" data-id="' . $id . '" title="Наверх"><span class="glyphicon glyphicon-chevron-up"></span></button>
<button class="btn btn-default btn-xs field-button-move field-button-down" data-id="' . $id . '"  title="Вниз"><span class="glyphicon glyphicon-chevron-down"></span></button>
</div>';
            $response .= '</div>';

            $response .= '<div class="field-content';
            $response .= ($value['checked']) ? ' field-content-visible' : ' field-content-hidden';
            $response .= '" id="field-content-' . $id . '">';

            $response .= '<div class="field-form-group">';

            if ($value['type_front'] != 0 && in_array($value['type_back'], array(1, 4, 5, 6, 7, 8))) {
                $response .= '<div class="field-options"><label><input name="field_filter_' . $value['field_id'] . '" value="1" type="checkbox"';
                $response .= ($value['filter']) ? ' checked' : '';
                $response .= '> В фильтрах</label></div>';
            }

            if (in_array($value['type_back'], array(4, 5, 6, 7))) {
                $response .= '<div class="field-options"><label><input name="field_modification_' . $value['field_id'] . '" value="1" type="checkbox"';
                $response .= ($value['modification']) ? ' checked' : '';
                $response .= '> Модификация</label></div>';
            }

            $response .= '<div class="field-options"><label><input name="field_table_' . $value['field_id'] . '" value="1" type="checkbox"';
            $response .= ($value['table']) ? ' checked' : '';
            $response .= '> В таблице</label></div>';

            $response .= '</div>';

            if($type) {
	            $response .= '<div class="field-form-group">';
	            $response .= '<label class="field-label" for="field_values_' . $value['field_id'] . '">Значения для списка</label>';

	            $values1  = ( $value['values'] != '' ) ? explode( ',', $value['values'] ) : array();
	            $values1  = array_combine( $values1, $values1 );
	            $response .= form_multiselect( 'field_values_' . $value['field_id'] . '[]', $values1, array_keys( $values1 ), 'class="field-values field_values_' . $value['field_id'] . '"' );
	            $response .= '<script>
		        $(document).ready(function () {
		            $(".field_values_' . $value['field_id'] . '").select2({
		            tags: true, 
		            tokenSeparators: [","],
		            language: "ru"
		            });
		        });
		        </script>';

	            $response .= '</div>';
            }

            $response .= '<div class="field-form-group">';
            $response .= '<label class="field-label" for="field_default_values_' . $value['field_id'] . '">Значение по умолчанию</label>';

	        if($type) {
		        $values2  = ( $value['default_values'] != '' ) ? explode( ',', $value['default_values'] ) : array();
		        $values2  = array_combine( $values2, $values2 );
		        $response .= form_multiselect( 'field_default_values_' . $value['field_id'] . '[]', $values2, array_keys( $values2 ), 'class="field-values field_default_values_' . $value['field_id'] . '"' );
		        $response .= '<script>
		        $(document).ready(function () {
		            $(".field_default_values_' . $value['field_id'] . '").select2({
		            tags: true, 
		            tokenSeparators: [","],
		            language: "ru"
		            });
		        });
		        </script>';
	        } else {
		        $values2  = ( $value['default_values'] != '' ) ? $value['default_values'] : '';
		        $response .= form_input('field_default_values_' . $value['field_id'], $values2, 'class="pme-input form-control"');
	        }

            $response .= '</div>';

            $response .= '</div>';


            $response .= '<input type="hidden" class="field_order" name="field_order_' . $value['field_id'] . '" value="' . $id . '">';
	        $response .= '<input type="hidden" name="field_type_' . $value['field_id'] . '" value="' . $type . '">';
            $response .= '</div>';
        }

        return $response;
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
		$opts['buttons']['L']['up'] = array('add','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_shop_types';

        // Ключ
        $opts['key'] = 'type_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('type_name');
        $opts['ui_sort_field'] = '';

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

        // Фильтрация вывода
        $opts['filters'] = array ();
		// Фильтр по языкам
		$opts['filters'][] = "type_lang_id = '" . $this->session->userdata('w_alang') . "'";

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
		$opts['triggers']['insert']['after']  = APPPATH.'triggers/shop_type_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/shop_type_update_after.php';
        $opts['triggers']['delete']['after']  = APPPATH.'triggers/shop_type_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Тип товара';
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

        $opts['fdd']['type_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['type_name'] = array(
            'name'          => 'Тип',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите имя типа.'
        );
        $fields = $this->get_fields();
        $opts['fdd']['type_fields'] = array(
            'name' => 'Характеристики',
            'nodb' => true,
            'options' => 'ACP',
            'add_display' => $fields,
            'change_display' => $fields,
            'sort' => false,
            'help' => 'Выберите требуемые поля и параметры для товаров данного типа. Отсортируйте их в нужном порядке.'
        );

		// ------------------------------------------------------------------------

		$opts['fdd']['type_lang_id'] = array(
			'name'          => 'Язык',
			'select'        => 'T',
			'options'       => 'ACPH',
			'maxlen'        => 3,
			'default'       => $this->session->userdata('w_alang'),
			'sort'          => false
		);

        // ------------------------------------------------------------------------

		return $opts;
	}
}
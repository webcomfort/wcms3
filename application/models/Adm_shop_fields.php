<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостными рубриками
 */

class Adm_shop_fields extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
        $this->load->helper( array('string') );
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
        $meta = '<script>
        jQuery(document).ready(function() {

            var label           = \'#PME_data_field_label\';
            var name            = \'#PME_data_field_name\';
            var generate_func   = \'/adm_shop_fields/p_label_generate\';
            var check_func      = \'/adm_shop_fields/p_check_label\';
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
                        $(label+\'_help\').html(\'Содержит недопустимые символы\');
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
                $this->db->select('field_id');
                $this->db->where('field_label', $this->input->post('name', TRUE));
                $query = $this->db->get('w_shop_fields');

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
        $opts['tb'] = 'w_shop_fields';

        // Ключ
        $opts['key'] = 'field_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('field_name');
        $opts['ui_sort_field'] = '';

        // Кол-во записей для вывода на экран
        $opts['inc'] = 20;

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
        $opts['filters'] = array (
            "field_lang_id = '" . $this->session->userdata('w_alang') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Поле';
        $opts['logtable_field'] = 'field_name';

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

        $opts['fdd']['field_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['field_name'] = array(
            'name'          => 'Название',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название.'
        );
        $opts['fdd']['field_type_back'] = array(
            'name'          => 'Тип поля в админе',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '1'         => 'Текст - строка',
                '2'         => 'Статья - большое поле',
                '3'         => 'Статья - большое поле + редактор',
                '4'         => 'Выпадающий список - единственный выбор',
                '5'         => 'Выпадающий список - множественный выбор',
                '6'         => 'Чекбоксы (функционально идентичен "Выпадающий список - множественный выбор")',
                '7'         => 'Радио (функционально идентичен "Выпадающий список - единственный выбор")',
                '8'         => 'Дата'
            ),
            'default'       => 1,
            'help'          => 'Выберите тип поля, наиболее удобный для редактирования данных'
        );
        $opts['fdd']['field_type_front'] = array(
            'name'          => 'Тип поля на сайте',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '0'         => 'Не выбрано',
                '1'         => 'Числовой диапазон',
                '2'         => 'Чекбоксы',
                '3'         => 'Диапазон дат'
            ),
            'default'       => 1,
            'help'          => 'Выберите тип поля, наиболее удобный для подбора товара на сайте (фильтры)'
        );
        $opts['fdd']['field_label'] = array(
            'name'          => 'Метка',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда метку на латинице, разные слова разделяются подчеркиванием, например "Региональный музей" = "regional_museum".'
        );
        if($publish)
        {
            $opts['fdd']['field_active'] = array(
                'name'          => 'Статус',
                'select'        => 'D',
                'options'       => 'LACPDV',
                'values2'       => array (
                    '1'         => 'Активно',
                    '0'         => 'Неактивно'
                ),
                'save'          => true,
                'default'       => 0,
                'help'          => 'Статус на сайте. Если вы хотите, чтобы этот параметр не было видно на сайте - сделайте его неактивным, т.е. совсем не обязательно удалять параметр, чтобы его скрыть.'
            );
        }

        // ------------------------------------------------------------------------

        $opts['fdd']['field_lang_id'] = array(
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
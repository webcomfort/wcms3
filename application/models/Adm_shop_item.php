<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление вендорами
 */

class Adm_shop_item extends CI_Model {

    private $forest = array();
    private $categories = array();

    function __construct()
    {
        parent::__construct();
        $this->config->load('cms_shop');
        $this->load->model('Cms_shop');
        $this->load->model('Cms_inclusions');
        $this->_categories();
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

            $(\'#PME_data_item_name\').keyup(function(){
                if (urlcheck == \'\') { url_generate(); }
                if (titlecheck == \'\') { $(\'#PME_data_item_meta_title\').val($(\'#PME_data_item_name\').val()); }
            });

            $(\'#PME_data_item_url\').keyup(function(){
                check_availability();
            });
        });

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
        $filter_values[99999] = 'Не подключены';

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
     * Функция, отдающая массив производителей
     *
     * @access  public
     * @return  array
     */
    function _get_vendors()
    {
        // Получаем данные
        $this->db->select('vendor_id AS id, vendor_name AS name')
            ->from('w_shop_vendors')
            ->order_by("vendor_name", "asc")
            ->where('vendor_lang_id', $this->session->userdata('w_alang'));

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

        if ($query->num_rows() > 0) $this->forest =& $this->tree->get_tree('cat_id', 'cat_pid', $query->result_array(), 0);
        $this->_get_categories_array ($this->forest, 'cat_id', 'cat_pid', 'cat_name', '');
    }

    // ------------------------------------------------------------------------

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
     * Список полей с характеристиками
     *
     * @access  private
     * @return  array
     */

    function _get_chars()
    {
        $opts   = array();
        $vals   = array();
        $id     = $this->input->get('PME_sys_rec', TRUE);
        $cat_id = $this->session->userdata('sitem_filter');

        // Set
        $query  = $this->db->query("SELECT cat_set_id FROM w_shop_categories WHERE cat_id='".$cat_id."' AND cat_lang_id='".$this->session->userdata('w_alang')."' LIMIT 1");
        $row    = $query->row();
        if ($query->num_rows() > 0)
        {
            $set = $row->cat_set_id;

            // Values
            if($this->input->get('PME_sys_rec', TRUE))
            {            
                $this->db->select('ic_char_id AS id, ic_value AS value')
                    ->from('w_shop_item_char')
                    ->where('ic_item_id', $id);

                $query  = $this->db->get();

                if ($query->num_rows() > 0)
                {
                    foreach ($query->result() as $row)
                    {
                        $vals[$row->id] = $row->value;
                    }
                }
            }

            // Fields
            $this->db->select('w_shop_char.char_id AS id, w_shop_char.char_name AS name, w_shop_char.char_type AS type, w_shop_char.char_values AS vals')
                ->from('w_shop_cat_char')
                ->join('w_shop_char', 'w_shop_cat_char.cc_char_id = w_shop_char.char_id')
                ->order_by("cc_sort", "asc")
                ->where('cc_cat_id', $set)
                ->where('cc_lang_id', $this->session->userdata('w_alang'));

            $query  = $this->db->get();

            if ($query->num_rows() > 0)
            {
                $i = 0;

                foreach ($query->result() as $row)
                {
                    $opts['fdd']['char'.$row->id] = array(
                        'name'          => $row->name,
                        'nodb'          => true,
                        'select'        => 'C',
                        'options'       => 'ACP',                        
                        'default'       => '',
                        'help'          => 'Введите значение характеристики'
                    );

                    if(count($vals) && isset($vals[$row->id])) $opts['fdd']['char'.$row->id]['fdefault'] = $vals[$row->id];
                    if($i == 0) $opts['fdd']['char'.$row->id]['tab'] = 'Характеристики';
                    $opts['fdd']['char'.$row->id]['select'] = ($row->type == 1) ? 'T' : 'D';
                    if($row->type == 2)
                    {
                        if($row->vals != ''){
                            $pieces = explode(",", $row->vals);
                            $pieces = array_map('trim', $pieces);
                            array_unshift($pieces, "Не выбрано");

                            $opts['fdd']['char'.$row->id]['values2'] = $pieces;
                        }
                    }

                    $i++;
                }
            }
        }

        return $opts;
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
        if(isset($uri_assoc_array['parent'])){
            $this->session->set_userdata('w_sitem_parent', $uri_assoc_array['parent']);
        }
        if(!$this->session->userdata('w_sitem_parent')) {
            $this->session->set_userdata('w_sitem_parent', 0);
        }
        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_sitem_parent');

        // Фильтрация вывода
        $opts['filters'] = array (
            "item_pid = '" . $this->session->userdata('w_sitem_parent') . "'",
            "item_cat_id = '".$this->session->userdata('sitem_filter')."'",
            "item_lang_id = '" . $this->session->userdata('w_alang') . "'",
        );

        // Триггеры
        // $this->opts['triggers']['insert']['after'] = '';
        // $this->opts['triggers']['update']['after'] = '';
        // $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = FCPATH.APPPATH.'triggers/shop_item_insert_after.php';
        $opts['triggers']['update']['after']  = FCPATH.APPPATH.'triggers/shop_item_update_after.php';
        $opts['triggers']['delete']['after']  = FCPATH.APPPATH.'triggers/shop_item_delete_after.php';

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
        $opts['fdd']['item_article'] = array(
            'name'          => 'Артикул',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'help'          => 'Введите артикул.'
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
        $shop_page = $this->Cms_shop->get_shop_page();
        $cat_page  = $this->Cms_shop->get_cat_page($this->session->userdata('sitem_filter'));
        $opts['fdd']['item_url'] = array(
            'name'          => 'URL страницы',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'URL'           => '/'.$shop_page.'/'.$cat_page.'/$value',
            'URLdisp'       => 'На сайте',
            'URLtarget'     => '_blank',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда слово на английском, которое будет выведено в URL. Разрешены латинские буквы, цифры, минус и символ подчеркивания. Во время ввода будет проведена автоматическая проверка данных.'
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
        $opts['fdd']['item_content'] = array(
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
            'help'          => 'Введите в это поле основной текст.'
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

        $opts = array_merge_recursive((array)$opts, (array)$this->Cms_inclusions->get_admin_inclusions('items'));

        // ------------------------------------------------------------------------

        $opts['fdd']['item_price'] = array(
            'name'          => 'Цена',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'save'          => true,
            'help'          => 'Введите цену. Дробная часть отделяется точкой.'
        );
        $opts['fdd']['item_price_old'] = array(
            'name'          => 'Старая цена',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите цену. Дробная часть отделяется точкой.'
        );
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
                'help'          => 'Статус на сайте. Если вы хотите, чтобы товар не было видно на сайте - сделайте его неактивной, т.е. совсем не обязательно удалять товар, чтобы его скрыть.'
            );
        }
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

        // ------------------------------------------------------------------------

        $opts = array_merge_recursive((array)$opts, (array)$this->_get_chars());

        // ------------------------------------------------------------------------

        $opts['fdd']['item_cat_id'] = array(
            'name'          => 'Основная категория',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->categories,
            'default'       => $this->session->userdata('sitem_filter'),
            'required'      => true,
            'tab'           => 'Вспомогательные параметры',
            'sort'          => true,
            'help'          => 'Проставляется автоматически при заведении товара. Можно использовать, когда требуется перенести товар в другой раздел.'
        );
        $opts['fdd']['item_cat_add'] = array(
          'name'     => 'Прочие категории',
          'select'   => 'M',
          'options'  => 'ACPDV',
          'values2'  => $this->categories,
          'required' => false,
          'sort'     => false,
          'help'     => 'Выберите из списка категории, в которых будет располагаться товар. Один и та же товар может быть размещен в разных разделах.'
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

        // ------------------------------------------------------------------------

        $opts['fdd']['item_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'ACPH',
            'default'       => time(),
            'fdefault'      => time(),
            'sort'          => false
        );
        $opts['fdd']['item_lang_id'] = array(
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
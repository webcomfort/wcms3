<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление категориями
 */

class Adm_shop_categories extends CI_Model {

    private $forest = array();
    private $categories_list = array();
    private $crumbs = array();

    function __construct()
    {
        /*if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }*/
        parent::__construct();
        $this->load->helper( array('string') );
        $this->load->model('Cms_shop');
        $this->load->model('Cms_utils');
        $this->_get_parent_list();
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
        elseif($this->input->post('PME_sys_rec', TRUE)) $id = $this->input->post('PME_sys_rec', TRUE);
        else $id = 0;

        $meta = '<script>
        jQuery(document).ready(function() {

            var urlcheck = $(\'#PME_data_cat_url\').val();
            var titlecheck = $(\'#PME_data_cat_meta_title\').val();

            $(\'#PME_data_cat_name\').keyup(function(){
                if (urlcheck == \'\') { url_generate(); }
                if (titlecheck == \'\') { $(\'#PME_data_cat_meta_title\').val($(\'#PME_data_cat_name\').val()); }
            });

            $(\'#PME_data_cat_url\').keyup(function(){
                check_availability();
            });
        });

        function url_generate(){
            var url = $(\'#PME_data_cat_name\').val();
            $.post(\'/adm_shop_categories/p_url_generate\', { url: url, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(\'#PME_data_cat_url\').val(result);
                check_availability();
            });
        }

        function check_availability(){
            var name = $(\'#PME_data_cat_url\').val();

            if($(\'#PME_data_cat_url\').val().length < 3){
                $(\'#PME_data_cat_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                $(\'#PME_data_cat_url_help\').html(\'Должно быть не менее трех символов\');
            }
            else{
                $(\'#PME_data_cat_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\');
                $(\'#PME_data_cat_url_help\').html(\'Проверка...\');
                $.post(\'/adm_shop_categories/p_check_url\', { name: name, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                    if(result == 1){
                        $(\'#PME_data_cat_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-success\');
                        $(\'#PME_data_cat_url_help\').html(\'<strong>\' + name + \'</strong> свободно\');
                    }
                    if(result == 2){
                        $(\'#PME_data_cat_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_cat_url_help\').html(\'URL содержит недопустимые символы\');
                    }
                    if(result == 0){
                        $(\'#PME_data_cat_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_cat_url_help\').html(\'<strong>\' + name + \'</strong> занято\');
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
                $this->db->select('cat_id');
                $this->db->where('cat_url', $this->input->post('name', TRUE));
                $query = $this->db->get('w_shop_categories');

                if ($query->num_rows() > 0) echo 0;
                else echo 1;
            }
            else echo 2;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Возврат id родителя
     *
     * @access  private
     * @return  int
     */

    function _get_parent()
    {
        if ($this->session->userdata('w_cat_parent') != 0)
        {
            $this->db->select('cat_pid AS pid')
                ->from('w_shop_categories')
                ->where('cat_id', $this->session->userdata('w_cat_parent'));

            $query  = $this->db->get();
            $row    = $query->row();
            return $row->pid;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Массив категорий для формирования выпадающего списка
     *
     * @access	private
     * @return	void
     */

    function _get_parent_list()
    {
        $this->db->select('cat_id, cat_pid, cat_name')
            ->order_by('cat_pid, cat_sort');

        $query = $this->db->get('w_shop_categories');

        $this->categories_list[0] = 'Верхний уровень';

        if ($query->num_rows() > 0) {
            @$this->forest =& $this->tree->get_tree('cat_id', 'cat_pid', $query->result_array(), 0);
            $this->_get_cats_array ($this->forest, 'cat_id', 'cat_pid', 'cat_name', '');
        }
    }

    /**
     * Преобразование массива в дерево с отступами
     *
     * @access	private
     * @return	void
     */
    function _get_cats_array ($forest, $id_name, $parent_name, $level_name, $dash='')
    {
        foreach ($forest as $tree)
        {
            $this->categories_list[$tree[$id_name]] = $dash.' '.$tree[$level_name];
            if (isset($tree['nodes'])) $this->_get_cats_array($tree['nodes'], $id_name, $parent_name, $level_name, $dash.' -');
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

    function get_child_pages($key, $value)
    {
        $forest = array();
        $this->db->select('cat_id, cat_pid, cat_name')
            ->order_by('cat_pid, cat_name');
        $query = $this->db->get('w_shop_categories');

        if ($query->num_rows() > 0)$forest = $this->tree->get_full_tree('cat_id', 'cat_pid', $query->result_array(), $key);

        return '<div class="jstree">' . $this->_reformat_forest($forest) . '</div>';
    }

    // ------------------------------------------------------------------------

    /**
     * Переформатирование дочерних элементов под вывод списка
     *
     * @access	private
     * @param   array
     * @param   array
     * @return	string
     */

    function _reformat_forest ($forest, $menu = '')
    {
        $menu .= '<ul>';
        foreach ($forest as $tree)
        {
            $menu .= '<li>';
            $menu .= '<a href="/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/'.$tree['cat_id'].'">';
            $menu .= $tree['cat_name'];
            $menu .= '</a>';
            if (isset($tree['nodes'])) $menu = $this->_reformat_forest($tree['nodes'], $menu);
            $menu .= '</li>';

        }
        $menu .= '</ul>';

        return $menu;
    }

    // ------------------------------------------------------------------------

    /**
     * Переформатирование дочерних элементов под вывод списка
     *
     * @access	private
     * @param   array
     * @param   array
     * @return	string
     */

    function _get_crumbs ()
    {
        $crumbs = '<small>';
        $this->set_crumbs($this->forest, 'cat_id', 'cat_pid', 'cat_name', '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/', $this->session->userdata('w_cat_parent'));
        $this->crumbs = array_reverse($this->crumbs);
        foreach ($this->crumbs as $value) $crumbs .= '<a href="'.$value['url'].'">'.$value['cat_name'].'</a> &raquo; ';
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
		
		// Переопределяем кнопки
		$opts['buttons']['L']['up'] = array('add','save','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_shop_categories';

        // Ключ
        $opts['key'] = 'cat_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('cat_sort');
        $opts['ui_sort_field'] = 'cat_sort';

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
            $this->session->set_userdata('w_cat_parent', $uri_assoc_array['parent']);
        }
        if(!$this->session->userdata('w_cat_parent')) {
            $this->session->set_userdata('w_cat_parent', 0);
        }

        // Фильтрация вывода
        $opts['filters'] = array (
            "cat_pid = '" . $this->session->userdata('w_cat_parent') . "'",
            "cat_lang_id = '" . $this->session->userdata('w_alang') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
		$opts['triggers']['insert']['after']  = APPPATH.'triggers/shop_cat_insert_after.php';
		$opts['triggers']['update']['after']  = APPPATH.'triggers/shop_cat_update_after.php';
        $opts['triggers']['delete']['after']  = APPPATH.'triggers/shop_cat_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Категория';
        $opts['logtable_field'] = 'cat_name';

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

        $opts['fdd']['go2'] = array(
            'name'          => '',
            'css'           => array('postfix'=>'nav'),
            'nodb'          => true,
            'options'       => 'L',
            'cell_display'   => '<div class="mr20"><a href="'.$opts['page_name'].'/move/up/id/$key" class="btn btn-sm btn-default mr2" rel="tooltip" title="Сдвинуть вверх"><i class="glyphicon glyphicon-chevron-up"></i></a><a href="'.$opts['page_name'].'/move/down/id/$key" class="btn btn-sm btn-default" rel="tooltip" title="Сдвинуть вниз"><i class="glyphicon glyphicon-chevron-down"></i></a></div>',
            'sort'          => false,
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['cat_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['cat_name'] = array(
            'name'          => 'Название',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'cell_func' => array(
                'model' => 'adm_shop_categories',
                'func'  => 'get_child_pages'
            ),
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'tab'           => array (
                'name'      => 'Основные параметры',
                'default'   => true,
            ),
            'help'          => 'Введите имя категории.'
        );
        $shop_page = $this->Cms_shop->get_shop_page();
        $opts['fdd']['cat_url'] = array(
            'name'          => 'URL страницы',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'URL'           => '/'.$shop_page.'/$value',
            'URLdisp'       => '/'.$shop_page.'/$value',
            'URLtarget'     => '_blank',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда слово на английском, которое будет выведено в URL. Разрешены латинские буквы, цифры, минус и символ подчеркивания. Во время ввода будет проведена автоматическая проверка данных.'
        );
        $opts['fdd']['cat_desc'] = array(
            'name'          => 'Текст описания категории',
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
            'help'          => 'Введите сюда текст описания товарной категории!'
        );
        $opts['fdd']['pic'] = array(
            'name'          => 'Иконка',
            'options'       => 'LACPD',
            'required'      => false,
            'sort'          => false,
            'size'          => '50',
            'nodb'          => true,
            'file'          => array (
                'tn'        => '',
                'url'       => $this->config->item('cms_shop_cat_dir'),
                'multiple'  => false,
                'accepted'  => 'image/*'
            ),
            'help'          => 'Выберите иконку на своем компьютере для загрузки.'
        );
        $opts['fdd']['cat_pid'] = array(
            'name'          => 'Родительский раздел',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->categories_list,
            'default'       => $this->session->userdata('w_cat_parent'),
            'required'      => true,
            'sort'          => true,
            'help'          => 'Проставляется автоматически при заведении страницы. Можно использовать, когда требуется перенести категорию в другой раздел.'
        );
        if($publish)
		{
			$opts['fdd']['cat_active'] = array(
				'name'          => 'Статус',
				'select'        => 'D',
				'options'       => 'LACPDV',
				'values2'       => array (
					'1'         => 'Активна',
					'0'         => 'Неактивна'
				),
				'save'          => true,
				'default'       => 0,
				'help'          => 'Статус категории на сайте. Если вы хотите, чтобы категории не было видно на сайте - сделайте ее неактивным, т.е. совсем не обязательно удалять категорию, чтобы ее скрыть.'
			);
		}
        $where = array(
            'field' => 'cat_pid',
            'value' => $this->session->userdata('w_cat_parent')
        );
		$opts['fdd']['cat_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'LACPD',
            'default'       => $this->Cms_utils->get_max_sort('cat_sort', 'w_shop_categories', $where),
            'save'          => true,
            'sort'          => false
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['cat_meta_title'] = array(
            'name'          => 'Заголовок страницы',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'tab'           => 'Мета-информация',
            'help'          => 'Введите сюда заголовок страницы - заголовок окна браузера &lt;title&gt;.'
        );
        $opts['fdd']['cat_meta_keywords'] = array(
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
        $opts['fdd']['cat_meta_description'] = array(
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
        $opts['fdd']['cat_seo'] = array(
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

        $opts['fdd']['cat_lang_id'] = array(
            'name'          => 'Язык',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('w_alang'),
            'sort'          => false
        );
		$opts['fdd']['created_at'] = array(
			'name'          => 'Дата создания',
			'select'        => 'T',
			'options'       => 'ACPH',
			'default'       => date('Y-m-d G:i:s'),
			'sort'          => false
		);

        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_cat_parent');
        $opts['parent_crumbs']  = $this->_get_crumbs();

        // ------------------------------------------------------------------------

		return $opts;
	}
}
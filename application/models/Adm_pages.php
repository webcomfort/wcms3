<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление страницами сайта
 */

class Adm_pages extends CI_Model {

    private $forest = array();
    private $items_list = array();
    private $crumbs = array();

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
        parent::__construct();
        $this->load->helper( array('string') );
        $this->load->model('Cms_inclusions');
        $this->load->model('Cms_articles');
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
        $(document).ready(function() {

            var urlcheck = $(\'#PME_data_page_url\').val();
            var titlecheck = $(\'#PME_data_page_meta_title\').val();
			var linkcheck = $(\'#PME_data_page_link_title\').val();

            $(\'#PME_data_page_name\').keyup(function(){
                if (urlcheck == \'\') { url_generate(); }
                if (titlecheck == \'\') { $(\'#PME_data_page_meta_title\').val($(\'#PME_data_page_name\').val()); }
				if (linkcheck == \'\') { $(\'#PME_data_page_link_title\').val($(\'#PME_data_page_name\').val()); }
            });

            $(\'#PME_data_page_url\').keyup(function(){
                check_availability();
            });
            
        });

        function url_generate(){
            var url = $(\'#PME_data_page_name\').val();
            $.post(\'/adm_pages/p_url_generate\', { url: url, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(\'#PME_data_page_url\').val(result);
                check_availability();
            });
        }

        function check_availability(){
            var name = $(\'#PME_data_page_url\').val();

            if($(\'#PME_data_page_url\').val().length < 3){
                $(\'#PME_data_page_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                $(\'#PME_data_page_url_help\').html(\'Должно быть не менее трех символов\');
            }
            else{
                $(\'#PME_data_page_url_alert\').removeClass(\'clearfix alert alert-danger alert-warning alert-info alert-success\');
                $(\'#PME_data_page_url_help\').html(\'Проверка...\');
                $.post(\'/adm_pages/p_check_url\', { name: name, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                    if(result == 1){
                        $(\'#PME_data_page_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-success\');
                        $(\'#PME_data_page_url_help\').html(\'<strong>\' + name + \'</strong> свободно\');
                    }
                    if(result == 2){
                        $(\'#PME_data_page_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_page_url_help\').html(\'URL содержит недопустимые символы\');
                    }
                    if(result == 0){
                        $(\'#PME_data_page_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_page_url_help\').html(\'<strong>\' + name + \'</strong> занято\');
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
        // Получаем данные
        $filter_init = $this->config->item('cms_site_menues');

        foreach ($filter_init as $key => $value)
        {
            $filter_values[$key] = $value['name'];
        }

        // Сессия
        if (!$this->session->userdata('page_filter'))
        {
            $this->session->set_userdata(array('page_filter' => current(array_keys($filter_values))));
        }

        if($this->input->post('page_filter', true) && preg_int($this->input->post('page_filter', true)))
        {
            $this->session->set_userdata(array('page_filter' => $this->input->post('page_filter', true)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите меню',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'page_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('page_filter'),
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
                $this->db->select('page_id');
                $this->db->where('page_url', $this->input->post('name', TRUE));
                $query = $this->db->get('w_pages');

                if ($query->num_rows() > 0) echo 0;
                else echo 1;
            }
            else echo 2;
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

            return $this->Cms_articles->get_article_editors($id, 'pages');
        }
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
        if ($this->session->userdata('w_pages_parent') != 0)
        {
            $this->db->select('page_pid AS pid')
                ->from('w_pages')
                ->where('page_id', $this->session->userdata('w_pages_parent'));

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

        $this->db->select('page_id, page_pid, page_name')
            ->order_by('page_pid, page_sort');

        $query = $this->db->get('w_pages');

        if ($query->num_rows() > 0) @$this->forest =& $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), 0);
        $this->_get_items_array ($this->forest, 'page_id', 'page_pid', 'page_name', '');

        return $val_arr;
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
	 * Массив макетов для формирования выпадающего списка
	 *
	 * @access	private
	 * @return	array
	 */

    function _get_view_list()
    {
        $views  = $this->config->item('cms_site_views');

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
        $plus = false;

        $this->db->select('page_id, page_pid, page_name')
            ->where('page_lang_id', $this->session->userdata('w_alang'))
            ->order_by('page_pid, page_name');
        $query = $this->db->get('w_pages');

        if ($query->num_rows() > 0) {
            $forest = $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), $key);
            if (count($forest) > 0) $plus = true;
        }

        if ($plus) return '<a data-toggle="collapse" href="#collapseExample'.$key.'" aria-expanded="false" aria-controls="collapseExample'.$key.'">'.$value.' [+]'.'</a><div class="collapse" id="collapseExample'.$key.'">'.$this->_reformat_forest($forest).'</div>';
        else return $value;
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
            $menu .= $tree['page_name'];
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
        $this->set_crumbs($this->forest, 'page_id', 'page_pid', 'page_name', '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/', $this->session->userdata('w_pages_parent'));
        $this->tree->get_crumbs();
        $this->crumbs = array_reverse($this->crumbs);
        foreach ($this->crumbs as $value) $crumbs .= '<a href="'.$value['url'].'">'.$value['page_name'].'</a> &raquo; ';
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
		
		// Переопределяем кнопки
		$opts['buttons']['L']['up'] = array('add','save','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_pages';

        // Ключ
        $opts['key'] = 'page_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('page_sort');
        $opts['ui_sort_field'] = 'page_sort';

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
        if($this->uri->segment(3) == 'lang' && preg_int ($this->uri->segment(4))) $this->session->unset_userdata('w_pages_parent');

        if(isset($uri_assoc_array['parent'])){
            $this->session->set_userdata('w_pages_parent', $uri_assoc_array['parent']);
        }
        if(!$this->session->userdata('w_pages_parent')) {
            $this->session->set_userdata('w_pages_parent', 0);
        }

        // Фильтрация вывода
        $opts['filters'] = array (
            "page_pid = '" . $this->session->userdata('w_pages_parent') . "'",
            "page_lang_id = '" . $this->session->userdata('w_alang') . "'",
            "page_menu_id = '" . $this->session->userdata('page_filter') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/pages_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/pages_update_after.php';
		$opts['triggers']['delete']['after']  = APPPATH.'triggers/pages_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Страница сайта';
        $opts['logtable_field'] = 'page_name';

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

        $opts['fdd']['page_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['page_name'] = array(
            'name'          => 'Название страницы',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'cell_func' => array(
                'model' => 'adm_pages',
                'func'  => 'get_child_pages'
            ),
            'tab'           => array (
                'name'      => 'Основные параметры',
                'default'   => true,
            ),
            'help'          => 'Введите название страницы. Это название будет использовано при выводе в меню.'
        );
        $opts['fdd']['page_url'] = array(
            'name'          => 'URL страницы',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'URL'           => '/$value',
            'URLdisp'       => '/$value',
            'URLtarget'     => '_blank',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда слово на английском, которое будет выведено в URL. Разрешены латинские буквы, цифры, минус и символ подчеркивания. Во время ввода будет проведена автоматическая проверка данных.'
        );
		$opts['fdd']['page_meta_title'] = array(
            'name'          => 'Заголовок страницы',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда заголовок страницы - заголовок окна браузера &lt;title&gt;.'
        );
        $opts['fdd']['page_view_id'] = array(
            'name'          => 'Макет',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->_get_view_list(),
            'default'       => 0,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Выберите из списка макет для отображения этой страницы'
        );
		$opts['fdd']['page_articles'] = array(
            'name'          => 'Тексты',
            'nodb'          => true,
            'options'       => 'ACP',
            'add_display'   => $this->get_articles(),
            'change_display'=> $this->get_articles(),
            'sort'          => false,
            'help'          => 'Заполните поля требуемыми текстами. На сайт они будут выводится по следующему порядку: справа-налево и сверху-вниз.'
        );
        if($publish)
		{
			$opts['fdd']['page_status'] = array(
				'name'          => 'Статус',
				'select'        => 'D',
				'options'       => 'LACPDV',
				'values2'       => array (
					'1'         => 'Активна и открыта',
					'2'         => 'Активна и невидима',
					'0'         => 'Неактивна',
					'3'         => 'Переход на уровень ниже'
				),
				'save'          => true,
				'default'       => 2,
				'help'          => 'Поведение страницы'
			);
		}

        // ------------------------------------------------------------------------

        $opts = array_merge_recursive((array)$opts, (array)$this->Cms_inclusions->get_admin_inclusions('pages'));

        // ------------------------------------------------------------------------

        $this->_get_parent_list();
        $opts['fdd']['page_pid'] = array(
            'name'          => 'Родительский раздел',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->items_list,
            'default'       => $this->session->userdata('w_pages_parent'),
            'required'      => true,
            'tab'           => 'Вспомогательные параметры',
            'sort'          => true,
            'help'          => 'Проставляется автоматически при заведении страницы. Можно использовать, когда требуется перенести страницу в другой раздел.'
        );
        $opts['fdd']['page_redirect'] = array(
            'name'          => 'Переадресация',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'help'          => 'Введите сюда адрес страницы, на которую нужно будет перебросить посетителя.'
        );

        // ------------------------------------------------------------------------

		$opts['fdd']['page_link_title'] = array(
            'name'          => 'Заголовок ссылки меню',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
			'tab'           => 'Мета-информация',
            'help'          => 'Введите сюда заголовок для ссылки в меню, т.е. атрибут title тега &lt;a&gt;.'
        );
        $opts['fdd']['page_meta_keywords'] = array(
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
        $opts['fdd']['page_meta_description'] = array(
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
        $opts['fdd']['page_meta_additional'] = array(
            'name'          => 'Дополнительные мета-теги',
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
            'help'          => 'Сюда можно добавить дополнительные инструкции для этой страницы. Они будут размещены между тегами &lt;head&gt;&lt;/head&gt;!'
        );
		$opts['fdd']['page_footer_additional'] = array(
            'name'          => 'Дополнительный код в подвал',
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
            'help'          => 'Сюда можно добавить дополнительные инструкции для этой страницы. Они будут размещены до закрывающего тега &lt;/body&gt;!'
        );
        $opts['fdd']['page_url_segments'] = array(
            'name'          => 'Сегменты',
            'select'        => 'D',
            'options'       => 'LACPDV',
            'values2'       => array (
                '0'         => 'Любое кол-во',
                '1'         => 'Один',
                '2'         => 'Два',
                '3'         => 'Три',
                '4'         => 'Четыре',
                '5'         => 'Пять'
            ),
            'save'          => true,
            'default'       => 0,
            'help'          => 'Допустимое кол-во сегментов в урл'
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['page_menu_id'] = array(
            'name'          => 'Меню',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('page_filter'),
            'sort'          => false
        );
        $opts['fdd']['page_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'ACPH',
            'default'       => time(),
            'fdefault'      => time(),
            'sort'          => false
        );
        $opts['fdd']['page_lang_id'] = array(
            'name'          => 'Язык',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('w_alang'),
            'sort'          => false
        );

        $opts['parent_id']      = $this->_get_parent();
        $opts['parent_sess_id'] = $this->session->userdata('w_pages_parent');
        $opts['parent_crumbs']  = $this->_get_crumbs();

        // ------------------------------------------------------------------------

		return $opts;
	}
}
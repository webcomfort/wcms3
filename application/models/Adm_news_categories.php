<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостными рубриками
 */

class Adm_news_categories extends CI_Model {

	private $forest = array();
	private $categories_list = array();
	private $crumbs = array();

	function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }

	    $this->load->helper( array('string') );
	    $this->load->model('Cms_utils');
	    $this->_get_parent_list();

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
     * Функция, отдающая html для других функций
     *
     * @access  private
     * @return  string
     */

    function _get_inc_form()
    {
        $this->session->unset_userdata('news_filter');
        return '
            <div class="form-group">
                <label for="news_cat_name">Рубрика</label>
                <input type="text" class="form-control" id="news_cat_name" name="news_cat_name" placeholder="Введите название рубрики">
            </div>
            <div class="form-group">
                <label for="news_cat_view_id">Внеший вид (макет)</label>
                '.form_dropdown('news_cat_view_id', $this->_get_view_list(), '', 'class="form-control"').'
            </div>
            ';
    }

    // ------------------------------------------------------------------------

    /**
     * Функция для вывода формы через ajax
     *
     * @access  public
     * @return  string
     */

    function p_add_cat()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            echo $this->_get_inc_form();
        }
        else
        {
            echo "У вас недостаточно прав для этой операции.";
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция для добавления рубрики через ajax
     *
     * @access  public
     * @return  string
     */

    function p_save_cat()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            $data = array(
               'news_cat_id'        => '',
               'news_cat_name'      => $this->input->post('news_cat_name', TRUE),
               'news_cat_view_id'   => $this->input->post('news_cat_view_id', TRUE)
            );

            $this->db->insert('w_news_categories', $data);

            echo '<div class="alert alert-success" role="alert">Рубрика была успешно добавлена!</div>'.$this->_get_inc_form();
        }
        else
        {
            echo "У вас недостаточно прав для этой операции.";
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция для смены значений поля select через ajax
     *
     * @access  public
     * @return  string
     */

    function p_return_cat()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {

            $options = '';

            // Получаем данные
            $this->db->select('news_cat_id AS id, news_cat_name AS name')
                ->from('w_news_categories');

            $query  = $this->db->get();

            if ($query->num_rows() > 0)
            {
                $query2 = $this->db->query('SELECT MAX(news_cat_id) as mid FROM w_news_categories');
                $row2 = $query2->row();

                foreach ($query->result() as $row)
                {
                    $options .= '<option value="'.$row->id.'"';
                    if($row2->mid == $row->id) $options .= ' selected';
                    $options .= '>'.$row->name.'</option>';
                }

                echo $options;
            }
        }
        else
        {
            echo "У вас недостаточно прав для этой операции.";
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
     * Массив макетов для формирования выпадающего списка
     *
     * @access  private
     * @return  array
     */

    function _get_view_list()
    {
        $views  = $this->config->item('cms_news_views');

        foreach ($views as $key => $value)
        {
            $val_arr[$key] = $value['name'];
        }

        return $val_arr;
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
		if ($this->session->userdata('w_ncat_parent') != 0)
		{
			$this->db->select('news_cat_pid AS pid')
			         ->from('w_news_categories')
			         ->where('news_cat_id', $this->session->userdata('w_ncat_parent'));

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
		$this->db->select('news_cat_id, news_cat_pid, news_cat_name')
		         ->order_by('news_cat_pid, news_cat_sort');

		$query = $this->db->get('w_news_categories');

		$this->categories_list[0] = 'Верхний уровень';

		if ($query->num_rows() > 0) {
			@$this->forest =& $this->tree->get_tree('news_cat_id', 'news_cat_pid', $query->result_array(), 0);
			$this->_get_cats_array ($this->forest, 'news_cat_id', 'news_cat_pid', 'news_cat_name', '');
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
		$this->db->select('news_cat_id, news_cat_pid, news_cat_name')
		         ->order_by('news_cat_pid, news_cat_sort');
		$query = $this->db->get('w_news_categories');

		if ($query->num_rows() > 0)$forest = $this->tree->get_full_tree('news_cat_id', 'news_cat_pid', $query->result_array(), $key);

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
			$menu .= '<a href="/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/'.$tree['news_cat_id'].'">';
			$menu .= $tree['news_cat_name'];
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
		$this->set_crumbs($this->forest, 'news_cat_id', 'news_cat_pid', 'news_cat_name', '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/parent/', $this->session->userdata('w_ncat_parent'));
		$this->crumbs = array_reverse($this->crumbs);
		foreach ($this->crumbs as $value) $crumbs .= '<a href="'.$value['url'].'">'.$value['news_cat_name'].'</a> &raquo; ';
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
        $opts['tb'] = 'w_news_categories';

        // Ключ
        $opts['key'] = 'news_cat_id';

        // Начальная и ручная(UI) сортировка
		// Начальная и ручная(UI) сортировка
		$opts['sort_field'] = array('news_cat_sort');
		$opts['ui_sort_field'] = 'news_cat_sort';

        // Кол-во записей для вывода на экран
        $opts['inc'] = 100;

        // Имя файла модуля, передаем для последующей проверки прав на него
        $opts['module'] = basename(__FILE__);

        // Права пользователя, получаем из модуля cms_user:
        // A - добавление,  C - изменение, P - копирование, V - просмотр, D - удаление,
        // F - фильтры (всегда активно), I - начальная сортировка (всегда активно)
        $rights = $this->cms_user->get_user_myedit_rights();
        $opts['options'] = $rights[basename(__FILE__)];

		// Активизируем родительский режим и управляем сессиями
		if(isset($uri_assoc_array['parent'])){
			$this->session->set_userdata('w_ncat_parent', $uri_assoc_array['parent']);
		}
		if(!$this->session->userdata('w_ncat_parent')) {
			$this->session->set_userdata('w_ncat_parent', 0);
		}

		// Фильтрация вывода
		$opts['filters'] = array (
			"news_cat_pid = '" . $this->session->userdata('w_ncat_parent') . "'",
			"news_cat_lang_id = '" . $this->session->userdata('w_alang') . "'"
		);

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
		$opts['triggers']['delete']['after']  = APPPATH.'triggers/news_categories_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Новостная рубрика';
        $opts['logtable_field'] = 'news_cat_name';

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

		$opts['fdd']['news_cat_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['news_cat_name'] = array(
            'name'          => 'Название рубрики',
            'options'       => 'LACPDV',
            'cell_func' => array(
	            'model' => 'adm_news_categories',
	            'func'  => 'get_child_pages'
            ),
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название рубрики.'
        );
		$opts['fdd']['news_cat_pid'] = array(
			'name'          => 'Родительский раздел',
			'select'        => 'D',
			'options'       => 'ACPD',
			'values2'       => $this->categories_list,
			'default'       => $this->session->userdata('w_ncat_parent'),
			'required'      => true,
			'sort'          => true,
			'help'          => 'Проставляется автоматически при заведении категории. Можно использовать, когда требуется перенести категорию в другой раздел.'
		);
		$where = array(
			'field' => 'news_cat_pid',
			'value' => $this->session->userdata('w_ncat_parent')
		);
		$opts['fdd']['news_cat_sort'] = array(
			'name'          => 'Сортировка',
			'select'        => 'T',
			'options'       => 'LACPD',
			'default'       => $this->Cms_utils->get_max_sort('news_cat_sort', 'w_news_categories', $where),
			'save'          => true,
			'sort'          => false
		);
        $opts['fdd']['news_cat_view_id'] = array(
            'name'          => 'Макет',
            'select'        => 'D',
            'options'       => 'ACPD',
            'values2'       => $this->_get_view_list(),
            'default'       => 0,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Выберите из списка макет для отображения этой рубрики'
        );
		$opts['fdd']['code'] = array(
			'name'          => 'Код',
			'nodb'          => true,
			'options'       => 'L',
			'cell_display'  => '{@module mod_news_latest 3 $key@}',
			'sort'          => false,
		);
		$opts['fdd']['news_cat_lang_id'] = array(
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
		$opts['parent_sess_id'] = $this->session->userdata('w_ncat_parent');
		$opts['parent_crumbs']  = $this->_get_crumbs();

        // ------------------------------------------------------------------------

		return $opts;
	}
}

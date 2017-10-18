<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление баннерами
 */

class Adm_banners extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
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
        // Получаем данные
        $filter_init = $this->config->item('cms_banners_places');

        foreach ($filter_init as $key => $value)
        {
            $filter_values[$key] = $value['name'];
        }

        // Сессия
        if (!$this->session->userdata('banner_filter'))
        {
            $this->session->set_userdata(array('banner_filter' => current(array_keys($filter_values))));
        }

        if($this->input->post('banner_filter', true) && preg_int($this->input->post('banner_filter', true)))
        {
            $this->session->set_userdata(array('banner_filter' => $this->input->post('banner_filter', true)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите место размещения баннера',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'banner_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('banner_filter'),
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
     * Массив макетов для формирования выпадающего списка
     *
     * @access  private
     * @return  array
     */

    function _get_view_list()
    {
        $views  = $this->config->item('cms_banners_views');

        foreach ($views as $key => $value)
        {
            $val_arr[$key] = $value['name'];
        }

        return $val_arr;
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
		$opts['buttons']['L']['up'] = array('add','save','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_banners';

        // Ключ
        $opts['key'] = 'banner_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('banner_sort');
        $opts['ui_sort_field'] = 'banner_sort';

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
        $opts['filters'] = array (
            "banner_lang_id = '" . $this->session->userdata('w_alang') . "'",
            "banner_place_id = '" . $this->session->userdata('banner_filter') . "'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Баннер';
        $opts['logtable_field'] = 'banner_name';

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
            'cell_display'   => '<div class="mr20"><a href="'.$opts['page_name'].'/move/up/id/$key" class="btn btn-sm btn-default mr2" rel="tooltip" title="Сдвинуть вверх"><i class="glyphicon glyphicon-chevron-up"></i></a><a href="'.$opts['page_name'].'/move/down/id/$key" class="btn btn-sm btn-default" rel="tooltip" title="Сдвинуть вниз"><i class="glyphicon glyphicon-chevron-down"></i></a></div>',
            'sort'          => false,
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['banner_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['banner_name'] = array(
            'name'          => 'Название баннера',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название баннера.'
        );
        $opts['fdd']['pic'] = array(
            'name'          => 'Баннер',
            'required'      => false,
            'sort'          => false,
            'size'          => '50',
            'nodb'          => true,
            'file'          => array (
                'tn'        => '',
                'url'       => $this->config->item('cms_banners_dir'),
                'multiple'  => false
            ),
            'help'          => 'Выберите баннер на своем компьютере для загрузки. Он может быть в формате .jpg, .gif, .swf, .png, .bmp. Удаление баннера из режима редактирования приводит к его безвозвратному удалению.'
        );
        $opts['fdd']['banner_link'] = array(
            'name'          => 'Ссылка',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите ссылку для баннера. Информация для разработчиков баннеров: внутри флэш-баннера ссылка должна быть прописана следующего вида <strong>on (release) { getURL(_root.link1, "_blank"); }</strong>'
        );
        $opts['fdd']['banner_code'] = array(
            'name'          => 'Код',
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
            'help'          => 'Вы можете не загружать баннер, если у вас есть сторонний код. Например, это может быть код счетчика. Если в это поле введены данные, то системой не будут учтены поля "В новом окне?", "Ссылка" и не будет показано загруженное изображение.'
        );
        $opts['fdd']['banner_blank'] = array(
            'name'          => 'В новом окне?',
            'select'        => 'D',
            'options'       => 'ACPDV',
            'values2'       => array (
                '1'         => 'Да',
                '0'         => 'Нет'
            ),
            'default'       => 0,
            'help'          => 'Будет ли открываться новое окно при щелчке на баннер'
        );
        $opts['fdd']['banner_view_id'] = array(
            'name'          => 'Макет',
            'select'        => 'D',
            'options'       => 'ACPDV',
            'values2'       => $this->_get_view_list(),
            'default'       => 0,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Выберите из списка макет для отображения этой галереи.'
        );
        if($publish)
		{
			$opts['fdd']['banner_active'] = array(
				'name'          => 'Статус',
				'select'        => 'D',
				'options'       => 'LACPDV',
				'values2'       => array (
					'1'         => 'Активен',
					'0'         => 'Неактивен'
				),
				'default'       => 0,
				'save'			=> true,
				'help'          => 'Неактивный баннер не показывается на сайте'
			);
		}
        $opts['fdd']['banner_click'] = array(
            'name'          => 'Клики',
            'select'        => 'N',
            'options'       => 'L',
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['banner_place_id'] = array(
            'name'          => 'Место',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 3,
            'default'       => $this->session->userdata('banner_filter'),
            'sort'          => false
        );
        $where = array(
            'field' => 'banner_place_id',
            'value' => $this->session->userdata('banner_filter')
        );
        $opts['fdd']['banner_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'LACPD',
            'default'       => $this->Cms_utils->get_max_sort('banner_sort', 'w_banners', $where),
            'save'          => true,
            'sort'          => false
        );
        $opts['fdd']['banner_lang_id'] = array(
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
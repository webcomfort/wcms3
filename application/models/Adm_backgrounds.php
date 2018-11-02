<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление фотографиями
 */

class Adm_backgrounds extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        parent::__construct();
    }

	// ------------------------------------------------------------------------

	/**
	 * Функция для добавления галереи через ajax
	 *
	 * @access  public
	 * @return  string
	 */

	function p_save_bg()
	{
		$rights = $this->cms_user->get_user_rights();

		if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
		{
			$bg_name = $this->input->post('bg_name', TRUE);
			$files = $this->input->post( 'pic_files', true );
			$article_num = $this->input->post( 'article_num', true );

			if($bg_name != '' && is_array( $files )) {
				$data = array(
					'bg_id'      => '',
					'bg_name'    => $bg_name,
					'bg_label'   => '',
					'bg_active'  => '1',
					'created_at' => date( 'Y-m-d G:i:s' )
				);
				$this->db->insert( 'w_backgrounds', $data );

				$this->load->library( 'image_lib' );
				foreach ( $files as $value ) {
					$this->image_lib->src_file_move( $value, $this->config->item( 'cms_bg_dir' ), $this->db->insert_id(), false, true, array(
						'_thumb' => array(
							'width'  => 150,
							'height' => 150
						)
					) );
				}

				$response['result'] = 1;
				$response['bg_id'] = $this->db->insert_id();
				$response['article_num'] = $article_num;
				$response['alert'] = '<div class="alert alert-success" role="alert">Фон был успешно загружен!</div>';
				echo json_encode($response);

			} else {
				$response['result'] = 2;
				$response['alert'] = '<div class="alert alert-warning" role="alert">Вы не заполнили требуемые поля!</div>';
				echo json_encode($response);
			}
		}
		else
		{
			$response['result'] = 2;
			$response['alert'] = '<div class="alert alert-warning" role="alert">У вас недостаточно прав!</div>';
			echo json_encode($response);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Функция для смены значений поля select через ajax
	 *
	 * @access  public
	 * @return  string
	 */

	function p_return_bg()
	{
		$rights = $this->cms_user->get_user_rights();

		if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) ) {
			$select = '';
			$bg     = $this->input->post( 'bg_id', true );
			$article_num = $this->input->post( 'article_num', true );

			$this->db->select( 'bg_id, bg_name' );
			$this->db->where( 'bg_active', 1 );
			$this->db->order_by( 'bg_name', 'ASC' );
			$query = $this->db->get( 'w_backgrounds' );

			if ( $query->num_rows() > 0 ) {
				$select .= '<option value="0"' . ( ( $bg == 0 ) ? 'selected="selected"' : '' ) . '>Без фона</option>';

				foreach ( $query->result() as $row ) {
					$select .= '<option value="' . $row->bg_id . '"' . ( ( $bg == $row->bg_id ) ? 'selected="selected"' : '' ) . '>' . $row->bg_name . '</option>';
				}

				$response['select'] = $select;
				$response['article_num'] = $article_num;
				echo json_encode($response);

			}
		}
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
        $opts['tb'] = 'w_backgrounds';

        // Ключ
        $opts['key'] = 'bg_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('bg_name');
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

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/bg_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/bg_update_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Фон';
        $opts['logtable_field'] = 'bg_name';

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

        $opts['fdd']['bg_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['bg_name'] = array(
            'name'          => 'Название',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название фона.'
        );
		$opts['fdd']['bg_label'] = array(
			'name'          => 'Метка',
			'options'       => 'LACPDV',
			'select'        => 'T',
			'maxlen'        => 65535,
			'required'      => false,
			'sort'          => true,
			'help'          => 'Введите метку фона (иногда требуется) - предпочтительно, если это будут латинские буквы, цифры, минус и символ подчеркивания.'
		);
        $opts['fdd']['pic'] = array(
            'name'          => 'Фото',
            'required'      => false,
            'sort'          => false,
            'size'          => '50',
            'nodb'          => true,
            'file'          => array (
                'tn'        => '_thumb',
                'url'       => $this->config->item('cms_bg_dir'),
                'multiple'  => false,
	            'accepted'  => 'image/*',
            ),
            'help'          => 'Выберите фон на своем компьютере для загрузки. Удаление фона из режима редактирования приводит к его безвозвратному удалению.'
        );
        if($publish)
		{
			$opts['fdd']['bg_active'] = array(
				'name'          => 'Статус',
				'select'        => 'D',
				'options'       => 'LACPDV',
				'values2'       => array (
					'1'         => 'Активно',
					'0'         => 'Неактивно'
				),
				'save'          => true,
				'default'       => 0,
				'help'          => 'Статус фона на сайте. Если вы хотите, чтобы фон не было видно на сайте - сделайте его неактивным, т.е. совсем не обязательно удалять фон, чтобы его скрыть.'
			);
		}
		$opts['fdd']['created_at'] = array(
			'name'          => 'Дата создания',
			'select'        => 'T',
			'options'       => 'ACPH',
			'default'       => date('Y-m-d G:i:s'),
			'sort'          => false
		);

        // ------------------------------------------------------------------------

		return $opts;
	}
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостными рубриками
 */

class Adm_news_categories extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
        parent::__construct();
        $this->config->load('cms_news');
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

        // Таблица
        $opts['tb'] = 'w_news_categories';

        // Ключ
        $opts['key'] = 'news_cat_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('news_name');
        $opts['ui_sort_field'] = '';

        // Кол-во записей для вывода на экран
        $opts['inc'] = 100;

        // Имя файла модуля, передаем для последующей проверки прав на него
        $opts['module'] = basename(__FILE__);

        // Права пользователя, получаем из модуля cms_user:
        // A - добавление,  C - изменение, P - копирование, V - просмотр, D - удаление,
        // F - фильтры (всегда активно), I - начальная сортировка (всегда активно)
        $rights = $this->cms_user->get_user_myedit_rights();
        $opts['options'] = $rights[basename(__FILE__)];

        // Фильтрация вывода
        $opts['filters'] = array ();

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
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название рубрики.'
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

        // ------------------------------------------------------------------------

		return $opts;
	}
}
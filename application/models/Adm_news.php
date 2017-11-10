<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление новостями
 */

class Adm_news extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        parent::__construct();
        $this->load->helper( array('string') );
        $this->load->model('Cms_inclusions');
        $this->load->model('Cms_news');
        $this->load->model('Cms_myedit');
        $this->load->model('Cms_articles');

        // Сработает при наличи в POST полей news_rubrics
        $this->Cms_myedit->mass_save('news_rubrics', 'news_id', 'news_cat_id', 'ncc_id', 'w_news_categories_cross');
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

            var urlcheck = $(\'#PME_data_news_url\').val();
            var titlecheck = $(\'#PME_data_news_meta_title\').val();

            $(\'#PME_data_news_name\').keyup(function(){
                if (urlcheck == \'\') { url_generate(); }
                if (titlecheck == \'\') { $(\'#PME_data_news_meta_title\').val($(\'#PME_data_news_name\').val()); }
            });

            $(\'#PME_data_news_url\').keyup(function(){
                check_availability();
            });
        });

        function url_generate(){
            var url = $(\'#PME_data_news_name\').val();
            $.post(\'/adm_news/p_url_generate\', { url: url, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                $(\'#PME_data_news_url\').val(result);
                check_availability();
            });
        }

        function check_availability(){
            var name = $(\'#PME_data_news_url\').val();

            if($(\'#PME_data_news_url\').val().length < 3){
                $(\'#PME_data_news_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                $(\'#PME_data_news_url_help\').html(\'Должно быть не менее трех символов\');
            }
            else{
                $(\'#PME_data_news_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\');
                $(\'#PME_data_news_url_help\').html(\'Проверка...\');
                $.post(\'/adm_news/p_check_url\', { name: name, '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
                    if(result == 1){
                        $(\'#PME_data_news_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-success\');
                        $(\'#PME_data_news_url_help\').html(\'<strong>\' + name + \'</strong> свободно\');
                    }
                    if(result == 2){
                        $(\'#PME_data_news_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_news_url_help\').html(\'URL содержит недопустимые символы\');
                    }
                    if(result == 0){
                        $(\'#PME_data_news_url_alert\').removeClass(\'alert alert-danger alert-warning alert-info alert-success\').addClass(\'alert alert-danger\');
                        $(\'#PME_data_news_url_help\').html(\'<strong>\' + name + \'</strong> занято\');
                    }
                });
            }
        }

        </script>';

        // JS функции для модуля select2 - простой список
        $meta .= $this->Cms_myedit->get_ajax_default_format();
        // JS функции для модуля select2 - список с изображениями
        $meta .= $this->Cms_myedit->get_ajax_icon_format();

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
        $filter_values = array();
        $filter_values[0] = '- Все новости';
        $filter_values[999999999] = '- Не подключены к рубрикам';

        // Получаем данные
        $this->db->select('news_cat_id AS id, news_cat_name AS name')
            ->from('w_news_categories');

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
        if (!$this->session->userdata('news_filter') && $this->session->userdata('news_filter') != 0)
        {
            $this->session->set_userdata(array('news_filter' => 0));
        }

        if(($this->input->post('news_filter', true) || $this->input->post('news_filter', true) == '0') && preg_int($this->input->post('news_filter', true)))
        {
            $this->session->set_userdata(array('news_filter' => $this->input->post('news_filter', true)));
        }

        if($this->uri->segment(3) == 'category' && preg_int($this->uri->segment(4)))
        {
            $this->session->set_userdata(array('news_filter' => $this->uri->segment(4)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите новостную рубрику',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'news_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('news_filter'),
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
                $this->db->select('news_id');
                $this->db->where('news_url', $this->input->post('name', TRUE));
                $query = $this->db->get('w_news');

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

            return $this->Cms_articles->get_article_editors($id, 'news');
        }
    }

    // ------------------------------- РУБРИКИ -----------------------------------------

    /**
     * Функция, отдающая массив рубрик
     *
     * @access  public
     * @return  array
     */
    function _get_rubrics()
    {
        // Получаем данные
        $this->db->select('news_cat_id AS id, news_cat_name AS name')
            ->from('w_news_categories');

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

    /**
     * Функция, список выбора для пересечений с событиями
     *
     * @access	private
     * @return	string
     */

    function get_rubrics_select($key, $value='')
    {
        // Получаем массивы с данными для формирования селекта
        $select_array = $this->_news_rubrics($key);

        // Строим селект
        $this->load->helper('form');
        //$opts = 'class="js-data-ajax-rubrics-'.$key.'"';
        $opts = 'class="select2"';
        $form = form_multiselect('news_rubrics_'.$key.'[]', $select_array['values'], $select_array['defaults'], $opts);

        // Получаем js-код для этого поля
        //$script = $this->Cms_myedit->get_ajax($key, '/adm_news/p_rub_generate', 'rubrics', 1);
        $script = '';

        // Компануем и выводим все это ячейку таблицы списка
        return $form.$script;
    }

    /**
     * Функция, генерирующая список событий для <select> (внешний вызов)
     *
     * @access  public
     * @return  string
     */
    function p_rub_generate()
    {
        $rights = $this->cms_user->get_user_rights();
        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            echo $this->Cms_myedit->get_ajax_query ('news_cat_id', 'news_cat_name', 'w_news_categories', 1, false, false);
        }
    }

    /**
     * Функция, отдающая массивы пересечений новостей и категорий для формирования селекта с дефолтными значениями
     *
     * @access	private
     * @param   int - id для выборки
     * @return	array
     */
    function _news_rubrics($key)
    {
        $val_arr = $this->_get_rubrics();
        $val_arr_active = array();

        if($key == 0 && $this->session->userdata('news_filter') != 999999999 && $this->session->userdata('news_filter') != 0) {
            $query_active = $this->db->query('SELECT news_cat_id AS id, news_cat_name AS name FROM w_news_categories WHERE news_cat_id = "'.$this->session->userdata('news_filter').'"');
            $row_active = $query_active->row();
            $val_arr[$row_active->id] = $row_active->name;
            $val_arr_active[] = $row_active->id;
        }

        // Active
        $this->db->select('w_news_categories_cross.news_cat_id AS id, news_cat_name AS name')
            ->from('w_news_categories_cross')
            ->join('w_news_categories', 'w_news_categories.news_cat_id = w_news_categories_cross.news_cat_id')
            ->where('news_id', $key);

        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $val_arr_active[] = $row->id;
            }

            $data = array(
                'values'    => $val_arr,
                'defaults'  => $val_arr_active
            );

            return $data;
        }
        else {
            $data = array(
                'values'    => $val_arr,
                'defaults'  => $val_arr_active
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
        $opts['tb'] = 'w_news';

        // Ключ
        $opts['key'] = 'news_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('-news_date','-news_id');
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

        // ------------------------------------------------------------------------
        // Фильтрация вывода
        $opts['filters'] = array();

        // Фильтр по рубрикам
        $rub_ids = array();
        if ($this->session->userdata('news_filter')) {
            if ($this->session->userdata('news_filter') != 999999999) {
                $this->db->select('news_id AS id');
                $this->db->from('w_news_categories_cross');
                $this->db->where('w_news_categories_cross.news_cat_id', $this->session->userdata('news_filter'));
                $query = $this->db->get();
                if ($query->num_rows() > 0)
                {
                    foreach ($query->result() as $row)
                    {
                        $rub_ids[] = $row->id;
                    }
                }
            } else {
                $this->db->distinct();
                $this->db->select('news_id AS id');
                $this->db->from('w_news_categories_cross');
                $query_a = $this->db->get();

                $n_ids = 0;
                if ($query_a->num_rows() > 0)
                {
                    foreach ($query_a->result() as $row_a)
                    {
                        $a_ids[] = $row_a->id;
                    }

                    $n_ids = join(',',$a_ids);
                }

                if($n_ids) {
                    $this->db->select('news_id AS id');
                    $this->db->from('w_news');
                    $this->db->where('news_id NOT IN (' . $n_ids . ')');
                    $query = $this->db->get();

                    if ($query->num_rows() > 0) {
                        foreach ($query->result() as $row) {
                            $rub_ids[] = $row->id;
                        }
                    }
                }
            }
        }

        if (count($rub_ids) > 0){
            $ids = join(',',$rub_ids);
            $opts['filters'][] = "news_id IN (".$ids.")";
        } else {
            if($this->session->userdata('news_filter')) $opts['filters'][] = "news_id IN (0)";
        }

        // Фильтр по языкам
        $opts['filters'][] = "news_lang_id = '" . $this->session->userdata('w_alang') . "'";

        // ------------------------------------------------------------------------

        // Триггеры
        // $this->opts['triggers']['insert']['after'] = '';
        // $this->opts['triggers']['update']['after'] = '';
        // $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/news_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/news_update_after.php';
        $opts['triggers']['delete']['after']  = APPPATH.'triggers/news_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Новость';
        $opts['logtable_field'] = 'news_name';

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

        $opts['fdd']['news_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['news_date'] = array(
            'name'          => 'Дата',
            'options'       => 'LACPDV',
            'addcss'        => 'datepicker w100',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'tab'           => array (
                'name'      => 'Основные параметры',
                'default'   => true,
            ),
            'save'          => true,
            'default'       => date('Y-m-d H:i', time()),
            'help'          => 'Дата вводится следующего вида <b>ГГГГ-ММ-ДД ЧЧ:ММ</b> (2004-03-31 12:21). При заведении новой новости по умолчанию ставится текущая дата и время.'
        );
        $opts['fdd']['news_name'] = array(
            'name'          => 'Заголовок новости',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите заголовок новости.'
        );
        $opts['fdd']['news_url'] = array(
            'name'          => 'URL страницы',
            'options'       => 'LACPDV',
            'URL'           => '/post/$value',
            'URLdisp'       => 'Посмотреть',
            'URLtarget'     => '_blank',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите сюда слово на английском, которое будет выведено в URL. Разрешены латинские буквы, цифры, минус и символ подчеркивания. Во время ввода будет проведена автоматическая проверка данных.'
        );
        $opts['fdd']['news_cut'] = array(
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
            'help'          => 'Введите в это поле краткий текст для вывода в списке новостей.'
        );
        $opts['fdd']['news_articles'] = array(
            'name'          => 'Тексты',
            'nodb'          => true,
            'options'       => 'ACP',
            'add_display'   => $this->get_articles(),
            'change_display'=> $this->get_articles(),
            'sort'          => false,
            'help'          => 'Заполните поля требуемыми текстами.'
        );
        $opts['fdd']['pic'] = array(
            'name'          => 'Картинка',
            'required'      => false,
            'sort'          => false,
            'size'          => '50',
            'nodb'          => true,
            'file'          => array (
                'tn'        => '_thumb',
                'url'       => $this->config->item('cms_news_dir'),
                'multiple'  => false
            ),
            'help'          => 'Выберите картинку на своем компьютере для загрузки, если хотите, чтобы в списке рядом с этой новостью стояла картинка. Картинки, которые вы хотите расположить по тексту статьи, надо загружать через редактор. Удаление картинки из режима редактирования новости приводит к ее безвозвратному удалению.'
        );
        $rubrics_select = $this->get_rubrics_select($id);
        $opts['fdd']['news_cat'] = array(
            'name'     => 'Рубрики',
            'nodb'     => true,
            'select'   => 'M',
            'options'  => 'ACPL',
            'add_display'   => $rubrics_select,
            'change_display'=> $rubrics_select,
            'cell_func' => array(
                'model' => 'adm_news',
                'func'  => 'get_rubrics_select'
            ),
            'required' => false,
            'sort'     => false,
            'help'     => 'Выберите из списка рубрики, в которых будет располагаться новость. Одна и та же новость может быть размещена в разных рубриках.'
        );
        if($publish)
        {
            $opts['fdd']['news_active'] = array(
                'name'          => 'Статус',
                'select'        => 'D',
                'options'       => 'LACPDV',
                'values2'       => array (
                    '1'         => 'Активна',
                    '2'         => 'Активна и невидима',
                    '0'         => 'Неактивна'
                ),
                'save'          => true,
                'default'       => 2,
                'help'          => 'Статус новости на сайте. Если вы хотите, чтобы новость не была видна на сайте - сделайте ее неактивной, т.е. совсем не обязательно удалять новость, чтобы ее скрыть.'
            );
        }

        // ------------------------------------------------------------------------

        $opts = array_merge_recursive((array)$opts, (array)$this->Cms_inclusions->get_admin_inclusions('news'));

        // ------------------------------------------------------------------------

        $opts['fdd']['news_meta_title'] = array(
            'name'          => 'Заголовок страницы',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'tab'           => 'Мета-информация',
            'help'          => 'Введите сюда заголовок страницы - заголовок окна браузера &lt;title&gt;.'
        );
        $opts['fdd']['news_meta_keywords'] = array(
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
        $opts['fdd']['news_meta_description'] = array(
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

        $opts['fdd']['news_lang_id'] = array(
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
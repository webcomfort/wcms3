<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление корзиной
 */

class Adm_changelog extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
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
        $filter_values['delete'] = 'Удаленные записи';
        $filter_values['update'] = 'Измененные записи';
        $filter_values['insert'] = 'Добавленные записи';

        // Сессия
        if (!$this->session->userdata('changelog_filter'))
        {
            $this->session->set_userdata(array('changelog_filter' => current(array_keys($filter_values))));
        }

        if($this->input->post('changelog_filter', true) && preg_alpha($this->input->post('changelog_filter', true)))
        {
            $this->session->set_userdata(array('changelog_filter' => $this->input->post('changelog_filter', true)));
        }

        // Отображение
        $data = array(
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'changelog_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('changelog_filter'),
            'filter_values' => $filter_values
        );

        $rights = $this->cms_user->get_user_rights();

        if($rights[basename(__FILE__)]['delete'])
        {
            $data['filter_name'] = '
            Выберите группу действий <a href="#" id="empty_trash" class="btn btn-xs btn-primary pull-right mb10"><i class="icon-trash icon-white"></i>&nbsp;&nbsp;Очистить корзину</a>
            <script>
            $(document).ready(function () {
            $(\'#empty_trash\').click(function(event) {
                if (confirm("Вы действительно хотите удалить все данные из корзины?")) {
                   window.location = \'/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/empty\';
                }
            });
            });
            </script>
            ';
        }
        else
        {
            $data['filter_name'] = 'Выберите группу действий';
        }

        $filters = '
        <div class="row">
            <div class="col-xs-12"><div class="p20 ui-block">'.
				$this->load->view('admin/filter_default', $data, true)
			.'</div>
        </div></div>
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
        if($this->uri->segment(3) == 'empty') {
            $this->_empty_trash();
            header ('Location: /admin/'.$this->uri->segment(2));
        }

        $this->load->library('myedit', $this->_get_crud_model());
        return $this->myedit->get_output();
    }

    // ------------------------------------------------------------------------

    /**
     * Функция, очищающая корзину
     *
     * @access  private
     * @return  void
     */
    function _empty_trash()
    {
        $rights = $this->cms_user->get_user_rights();

        if($rights[basename(__FILE__)]['delete'])
        {
            $this->db->query("TRUNCATE TABLE w_changelog");

            $dir = FCPATH.'public/upload/';
            $handle = opendir($dir);

            while($f = readdir($handle))
            {
                if ($f != '.' && $f != '..' && is_dir($dir.$f)) {

                    $dir2 = FCPATH.'public/upload/'.$f.'/';
                    $handle2 = opendir($dir2);

                    while($ff = readdir($handle2))
                    {
                        if ($ff != '.' && $ff != '..' && is_dir($dir2.$ff)) {
                            if (is_dir($dir2 . $ff . '/trash')) {
                                delete_files($dir2 . $ff . '/trash/', TRUE);
                            }
                        }
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Восстановление данных (внешний вызов)
     *
     * @access  public
     * @return  void
     */
    function p_restore()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            $id = $this->input->post('id', TRUE);

            $query = $this->db->get_where('w_changelog', array('id' => $id));

            if ($query->num_rows() > 0)
            {
                $row = $query->row();

                // Восстановление удаленных записей
                if ($row->operation == 'delete')
                {
                    // Восстановление дочерних записей
                    $query_child = $this->db->get_where('w_changelog', array('pid' => $id));

                    if ($query_child->num_rows() > 0)
                    {
                        foreach ($query_child->result() as $row_child)
                        {
                            if ($row_child->operation == 'delete') $this->db->insert($row_child->tab, unserialize($row_child->oldval));
                            if ($row_child->operation == 'update')
                            {
                                // Находим индекс
                                $query_ind = $this->db->query("SHOW INDEX FROM ".$row_child->tab." WHERE Key_name = 'PRIMARY'");
                                $row_ind = $query_ind->row();

                                if(preg_ext_string ($row_child->col))
                                {
                                    $data = array( $row_child->col => $row_child->oldval );
                                    $this->db->update($row_child->tab, $data, $row_ind->Column_name." = '".$row_child->rowkey."'");
                                }
                            }

                            if ($row_child->files) $this->_restore_files(unserialize($row_child->files), $row_child->id, $row_child->rowkey);
                            if ($this->db->affected_rows()) $this->db->delete('w_changelog', array('id' => $row_child->id));
                        }
                    }

                    $this->db->insert($row->tab, unserialize($row->oldval));
                    if ($row->files) $this->_restore_files(unserialize($row->files), $row->id, $row->rowkey);
                    if ($this->db->affected_rows())
                    {
                        $this->db->delete('w_changelog', array('id' => $row->id));
                        echo '1';
                    }
                }

                // ------------------------------------------------------------------------

                // Восстановление измененных записей
                if ($row->operation == 'update')
                {
                    // Восстановление дочерних записей
                    $query_child = $this->db->get_where('w_changelog', array('pid' => $id));

                    if ($query_child->num_rows() > 0)
                    {
                        foreach ($query_child->result() as $row_child)
                        {
                            if ($row_child->operation == 'delete') $this->db->insert($row_child->tab, unserialize($row_child->oldval));
                            if ($row_child->operation == 'update')
                            {
                                // Находим индекс
                                $query_ind = $this->db->query("SHOW INDEX FROM ".$row_child->tab." WHERE Key_name = 'PRIMARY'");
                                $row_ind = $query_ind->row();

                                if(preg_ext_string ($row_child->col))
                                {
                                    $data = array( $row_child->col => $row_child->oldval );
                                    $this->db->update($row_child->tab, $data, $row_ind->Column_name." = '".$row_child->rowkey."'");
                                }
                            }

                            if ($row_child->files) $this->_restore_files(unserialize($row_child->files), $row_child->id, $row_child->rowkey);
                            if ($this->db->affected_rows()) $this->db->delete('w_changelog', array('id' => $row_child->id));
                        }
                    }

                    // Находим индекс
                    $query_ind = $this->db->query("SHOW INDEX FROM ".$row->tab." WHERE Key_name = 'PRIMARY'");
                    $row_ind = $query_ind->row();

                    if(preg_ext_string ($row->col) && $row->oldval != '')
                    {
                        $data = array( $row->col => $row->oldval );
                        $this->db->update($row->tab, $data, $row_ind->Column_name." = '".$row->rowkey."'");
                    }

                    if ($row->files) $this->_restore_files(unserialize($row->files), $row->id, $row->rowkey);
                    echo '1';
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Восстановление файлов
     *
     * @access  private
     * @param   array
     * @param   int
     * @param   int
     * @return  void
     */

    function _restore_files($files, $iid, $id)
    {
        if(is_array($files))
        {
            foreach ($files as $key => $value)
            {
                $df     = ceil(intval($id)/1000);
                $path   = FCPATH.substr($value['url'], 1).$df.'/';
                $dpath  = $path.$id.'/';
                $tpath  = $path.'trash/'.$iid.'/';
                $tmpath = $path.'trash/'.$iid.'/'.$id.'/';

                if (is_dir($tpath) && $handle = opendir($tpath))
                {
                    while (false !== ($file = readdir($handle)))
                    {
                        if ($value['multiple'] && is_dir($tmpath) && $mhandle = opendir($tmpath))
                        {
                            if(!is_dir($dpath)) mkdir($dpath, 0, true);

                            while (false !== ($mfile = readdir($mhandle)))
                            {
                                if (preg_match ("/^[0-9a-zA-Z_]*\.([[:alnum:]])*$/", $mfile)) {
                                    copy($tmpath.$mfile, $dpath.$mfile);
                                }
                            }

                            delete_files($tmpath, TRUE);
                        }
                        else
                        {
                            if (preg_match ("/^".intval($id)."\.([[:alnum:]])*$/", $file)) {
                                copy($tpath.$file, $path.$file);
                                unlink ($tpath.$file);
                            }
                            if (preg_match ("/^".intval($id)."_[a-zA-Z]*\.([[:alnum:]])*$/", $file)) {
                                copy($tpath.$file, $path.$file);
                                unlink ($tpath.$file);
                            }
                        }
                    }

                    rmdir($tpath);
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

        // Таблица
        $opts['tb'] = 'w_changelog';

        // Ключ
        $opts['key'] = 'id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('-updated');

        // Кол-во записей для вывода на экран
        $opts['inc'] = 100;

        // Имя файла модуля, передаем для последующей проверки прав на него
        $opts['module'] = basename(__FILE__);

        // Права пользователя, получаем из модуля cms_user:
        // A - добавление,  C - изменение, P - копирование, V - просмотр, D - удаление,
        // F - фильтры (всегда активно), I - начальная сортировка (всегда активно)
        $rights = $this->cms_user->get_user_myedit_rights();
        if(isset($rights[basename(__FILE__)]) && $rights[basename(__FILE__)] != '') $opts['options'] = 'VFI';

        // Фильтрация вывода
        $opts['filters'] = array (
            "operation = '" . $this->session->userdata('changelog_filter') . "'",
            "pid = '0'"
        );

        // Триггеры
		// $opts['triggers']['insert']['after'] = '';
		// $opts['triggers']['update']['after'] = '';
		// $opts['triggers']['delete']['before'] = '';

        // Логирование: общее название класса и поле где хранится название объекта
        // $opts['logtable_title'] = '';
        // $opts['logtable_field'] = '';

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

        $opts['fdd']['id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['description'] = array(
            'name'          => 'Что',
            'select'        => 'T',
            'options'       => 'LV',
            'maxlen'        => 65535,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['updated'] = array(
            'name'          => 'Дата и время',
            'select'        => 'T',
            'options'       => 'LFV',
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['user'] = array(
            'name'          => 'Пользователь',
            'select'        => 'T',
            'options'       => 'LFV',
            'values'        => array (
                'table'     => 'w_user',
                'column'    => 'user_id',
                'description' => array(
                    'columns' => array(
                        0   => 'user_name',
                        1   => 'user_surname'
                    ),
                    'divs'  => array(
                        0   => ' '
                    )
                )
            ),
            'maxlen'        => 255,
            'sort'          => true,
            'help'          => 'Пользователь'
        );
        $opts['fdd']['host'] = array(
            'name'          => 'Адрес',
            'select'        => 'T',
            'options'       => 'LFV',
            'maxlen'        => 255,
            'sort'          => true,
            'help'          => 'Адрес'
        );
        if ($this->session->userdata('changelog_filter') == 'update')
        {
            $opts['fdd']['col'] = array(
                'name'          => 'Поле',
                'select'        => 'T',
                'options'       => 'LFV',
                'maxlen'        => 255,
                'sort'          => true,
                'help'          => 'Измененное поле'
            );
        }
        if ($this->session->userdata('changelog_filter') == 'delete' || $this->session->userdata('changelog_filter') == 'update')
        {
            $script = '
                <script>
                $(document).ready(function () {
                $(\'#delete_$key\').click(function(){
                    if(confirm("Вы действительно хотите вернуть старые данные?")){
                        $.ajax({
                            type: "POST",
                            url: "/adm_changelog/p_restore",
                            data: { id: "$key", '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }
                        }).done(function(result) {
                            if (result == 1) {
                                $(\'#delete_$key\').removeClass(\'btn-success\').addClass(\'btn-default\').html(\'<i class="glyphicon glyphicon-retweet"></i>&nbsp;&nbsp;Данные были восстановлены!\');
                            }
                        });
                    }
                });
                });
                </script>
            ';

            $opts['fdd']['restore'] = array(
                'name'      => 'Восстановление',
                'select'    => 'T',
                'options'   => 'L',
                'nodb'      => true,
                'cell_display'   => '<div id="delete_$key" class="btn btn-sm btn-success"><i class="glyphicon glyphicon-retweet icon-white"></i>&nbsp;&nbsp;Восстановить старые данные</a>'.$script,
                'maxlen'    => 255
            );
        }

        // ------------------------------------------------------------------------

		return $opts;
	}
}
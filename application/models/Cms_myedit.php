<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CRUD модуль - вспомогательные функции
 */

class Cms_myedit extends CI_Model {

    private $icons = '/public/admin/img/icons/filetype/';

	function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
	 * Отдача массива базовых параметров для myedit, чтобы избежать их повторного копирования
     * от модели к модели. Не выносим в конфигурационный файл, так как используются библиотеки
     * фреймворка.
	 *
	 * @access	public
	 * @return	array
	 */
	function get_base_opts()
	{
		$opts = array();

        // Базовый урл для формирования ссылок
        $opts['page_name'] = ($this->input->get("url", true)) ? $this->input->get("url", true) : '/'.$this->uri->segment(1).'/'.$this->uri->segment(2);

        // Передаем соединение с базой
        $opts['dbh'] = $this->db->conn_id;

        // Адреса, где хранятся ресурсы и адреса аякс-вызовов
        $opts['url'] = array(
            'images'    => '/public/admin/img/icons/',
            'icons'     => $this->icons,
            'filedel'   => '/cms_myedit/p_delete'
        );

        // Тип PRIMARY ключа
        $opts['key_type'] = 'int';

        // id пользователя, получаем из модуля cms_user
        $opts['user'] = $this->cms_user->get_user_id();

        // Какое кол-во опций выводить в <select multiple>
        $opts['multiple'] = '8';

        // Параметры расположения навигации
        // Стиль: B - кнопки (по умолчанию), T - текстовые ссылки, G - иконки, Q - bootstrap-стиль
        // Расположение: U - сверху, D - снизу (по умолчанию)
        $opts['navigation'] = 'UDQ';
        $opts['buttons']['L']['up'] = array('add','<<','<','>','>>','goto_combo');
        $opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Вывод дополнительной информации
        $opts['display'] = array(
            'form'  => true,
            'query' => false,
            'sort'  => false,
            'time'  => false,
            'tabs'  => true
        );

        // Язык интерфейса (имя файла с переводом)
        $opts['language'] = 'russian';

        // Разделитель для имен css-классов
        $opts['css']['divider'] = 0;

        // Таблица для создания логов (корзина)
        $opts['logtable'] = 'w_changelog';

		return $opts;
	}

	// ------------------------------------------------------------------------

	/**
	 * Функция, загружающая файлы из дроп-зоны в tmp
	 *
	 * @access	public
	 * @return	void
	 */
	function p_drop_upload(){
		$module = $this->input->post( 'module', true );
		$rights = $this->cms_user->get_user_rights();
		if ( is_array( $rights ) && $rights[ $module ] != '' ) {
			$path = FCPATH . 'tmp/';
			if ( ! is_dir( $path ) ) {
				mkdir( $path, 0755, true );
			}

			$chunk = ( $this->input->post( 'dzchunkindex', true ) != '' ) ? $this->input->post( 'dzchunkindex', true ) : false;

			if ( $chunk !== false ) {
				$out = @fopen( $path . $this->input->post( 'file_name', true ), ( $chunk != 0 ) ? "ab" : "wb" );
				$in  = @fopen( $_FILES["file"]["tmp_name"], "rb" );
				while ( $buff = fread( $in, 4096 ) ) {
					fwrite( $out, $buff );
				}
				fclose( $out );
				fclose( $in );
			} else {
				$file_path = $path . $this->input->post( 'file_name', true );
				move_uploaded_file( $_FILES['file']['tmp_name'], $file_path );
			}
		}
	}

	/**
	 * Функция, загружающая файлы из дроп-зоны в папку модуля
	 *
	 * @access	public
	 * @return	void
	 */
	function p_drop_update(){
		$module = $this->input->post( 'module', true );
		$rights = $this->cms_user->get_user_rights();
		if ( is_array( $rights ) && $rights[ $module ] != '' ) {

			$this->load->library( 'image_lib' );
			$id = $this->input->post( 'id', true );
			$iid = ceil( intval( $id ) / 1000 );
			$storeFolder = $this->input->post( 'path', true );
			$multiple = intval($this->input->post( 'multiple', true ));
			$file_name = $this->input->post('file_name', TRUE);

			if($multiple){
				$path   = FCPATH . substr( $storeFolder, 1 ) . $iid . '/' . $id . '/';
			} else {
				$path   = FCPATH . substr( $storeFolder, 1 ) . $iid . '/';
			}

			if ( ! is_dir( $path ) ) {
				mkdir( $path, 0755, true );
			}

			$file_path = $path . $file_name;

			$chunk = ( $this->input->post( 'dzchunkindex', true ) != '' ) ? $this->input->post( 'dzchunkindex', true ) : false;

			if ( $chunk !== false ) {
				$out = @fopen( $file_path, ( $chunk != 0 ) ? "ab" : "wb" );
				$in  = @fopen( $_FILES["file"]["tmp_name"], "rb" );
				while ( $buff = fread( $in, 4096 ) ) {
					fwrite( $out, $buff );
				}
				fclose( $out );
				fclose( $in );
			} else {
				move_uploaded_file( $_FILES['file']['tmp_name'], $file_path );
			}
		}
	}

	/**
	 * Функция, для вывода списка файлов
	 *
	 * @access	public
	 * @return	void
	 */
	function p_drop_get_files(){
		$module = $this->input->post( 'module', true );
		$rights = $this->cms_user->get_user_rights();
		if ( is_array( $rights ) && $rights[ $module ] != '' ) {
			$result  = array();
			$id = $this->input->post( 'id', true );
			$tn = $this->input->post( 'tn', true );
			$iid = ceil( intval( $id ) / 1000 );
			$storeFolder = $this->input->post( 'path', true );
			$multiple = intval($this->input->post( 'multiple', true ));

			if($multiple) {
				$dir   = FCPATH . substr( $storeFolder, 1 ) . $iid . '/' . $id . '/';
				$files = scandir( $dir );
				if ( false !== $files ) {
					foreach ( $files as $file ) {
						if ( preg_match( "/^[0-9]*\.([[:alnum:]])*$/", $file ) && is_file($dir . $file)) {

							$path_parts = pathinfo( $dir . $file );
							$extension  = $path_parts['extension'];
							$filename   = $path_parts['filename'];
							$obj['name'] = $storeFolder . $iid . '/' . $id . '/' . $file;
							$size = getimagesize ($dir . $file);

							if($tn){
								$obj['tn']   = $storeFolder . $iid . '/' . $id . '/' . $filename . $tn . '.' . $extension . '?' . time();
							} elseif ($size && $size[2] != 13){
								$obj['tn']   = $storeFolder . $iid . '/' . $id . '/' . $file . '?' . time();
							} else {
								$icon = FCPATH.substr( $this->icons, 1 ) . $extension . '.png';
								$icon_url = $this->icons . $extension . '.png';
								$obj['tn']   = (is_file($icon)) ? $icon_url : $obj['name'];
							}

							$obj['size'] = filesize( $dir . $file );
							$result[]    = $obj;
						}
					}
				}
			} else {
				$dir   = FCPATH . substr( $storeFolder, 1 ) . $iid . '/';
				$files = scandir( $dir );
				if ( false !== $files ) {
					foreach ( $files as $file ) {
						if ( preg_match( "/^" . $id . "\.([[:alnum:]])*$/", $file ) && is_file($dir . $file)) {
							$path_parts = pathinfo( $dir . $file );
							$extension  = $path_parts['extension'];
							$filename   = $path_parts['filename'];
							$obj['name'] = $storeFolder . $iid . '/' . $file;
							$size = getimagesize ($dir . $file);
							if($tn){
								$obj['tn'] = $storeFolder . $iid . '/' . $filename . $tn . '.' . $extension . '?' . time();
							} elseif ($size && $size[2] != 13){
								$obj['tn']   = $storeFolder . $iid . '/' . $file . '?' . time();
							} else {
								$icon = FCPATH.substr( $this->icons, 1 ) . $extension . '.png';
								$icon_url = $this->icons . $extension . '.png';
								$obj['tn']   = (is_file($icon)) ? $icon_url : $obj['name'];
							}
							$obj['size'] = filesize( $dir . $file );
							$result[]    = $obj;
						}
					}
				}
			}

			header('Content-type: text/json');
			header('Content-type: application/json');
			echo json_encode($result);
		}
	}

	/**
	 * Функция, удаляющая файлы, загруженные из дроп-зоны в tmp
	 *
	 * @access	public
	 * @return	void
	 */
	function p_drop_delete(){
		$module = $this->input->post('module', TRUE);
		$rights = $this->cms_user->get_user_rights();
		$path = FCPATH.'tmp/';
		if(!is_dir($path)) mkdir($path, 0755, true);
		if ( is_array($rights) && $rights[$module] != '' )
		{
			if(is_file($path.$this->input->post('file_name', TRUE))) unlink ($path.$this->input->post('file_name', TRUE));
		}
	}

	/**
	 * Функция, удаляющая файлы, из папки модуля
	 *
	 * @access	public
	 * @return	void
	 */
	function p_drop_update_delete(){

		$module = $this->input->post('module', TRUE);
		$rights = $this->cms_user->get_user_rights();
		$id = $this->input->post( 'id', true );
		$iid = ceil( intval( $id ) / 1000 );
		$storeFolder = $this->input->post( 'path', true );
		$multiple = intval($this->input->post( 'multiple', true ));
		$tn = $this->input->post( 'tn', true );
		$filedel = $this->input->post('file_name', TRUE);

		if ( is_array($rights) && $rights[$module] != '' )
		{
			if($multiple){
				$dir   = FCPATH . substr( $storeFolder, 1 ) . $iid . '/' . $id . '/';
				$path_parts = pathinfo( $dir . $filedel );
				$filename   = $path_parts['filename'];

				if(preg_int($filename)) {
					if ( $handle = opendir( $dir ) ) {
						while ( false !== ( $file = readdir( $handle ) ) ) {
							if ( preg_match( "/^" . intval( $filename ) . "\.([[:alnum:]])*$/", $file ) || preg_match( "/^" . intval( $filename ) . "_([[:alnum:]])*\.([[:alnum:]])*$/", $file ) ) {
								unlink( $dir . $file );
							}
						}
					}
				}

				if(preg_string($filename) && is_file($dir . $filedel)) unlink ($dir.$filedel);

			} else {
				$dir   = FCPATH . substr( $storeFolder, 1 ) . $iid . '/';
				$path_parts = pathinfo( $dir . $filedel );
				$filename   = $path_parts['filename'];

				if(preg_int($filename)) {
					if ( $handle = opendir( $dir ) ) {
						while ( false !== ( $file = readdir( $handle ) ) ) {
							if ( preg_match( "/^" . intval( $id ) . "\.([[:alnum:]])*$/", $file ) || preg_match( "/^" . intval( $id ) . "_([[:alnum:]])*\.([[:alnum:]])*$/", $file ) ) {
								unlink( $dir . $file );
							}
						}
					}
				}

				if(preg_string($filename) && is_file($dir . $filedel)) unlink ($dir.$filedel);
			}

		}
	}

    // ------------------------------------------------------------------------

    /**
	 * Удаление файла (внешний вызов)
	 *
	 * @access	public
	 * @return	string
	 */
	function p_delete()
	{
		$id     = $this->input->post('id', TRUE);
		$iid    = ceil(intval($id)/1000);
		$path   = FCPATH.substr($this->input->post('folder', TRUE), 1).$iid.'/';
        $module = $this->input->post('module', TRUE);
        $check  = false;

        $rights = $this->cms_user->get_user_myedit_rights();

        if ( is_array($rights) && $rights[$module] != '' )
        {
            if ($handle = opendir($path))
            {
                while (false !== ($file = readdir($handle)))
                {
                    if (
                    preg_match ("/^".intval($id)."\.([[:alnum:]])*$/", $file) ||
                    preg_match ("/^".intval($id)."_([[:alnum:]])*\.([[:alnum:]])*$/", $file)
                    )
                    {
                        unlink ($path.$file);
                        $check = true;
                    }
                    if (is_dir($path.$file) && $file == $id)
                    {
                        delete_files($path.$file, TRUE);
                        rmdir($path.$file);
                        $check = true;
                    }
                }
            }
        }
		if ($check) return 'true'; else return 'false';
	}

    /**
	 * Безопасный запуск Elfinder (внешний вызов)
	 *
	 * @access	public
	 * @return	void
	 */
	function p_elfinder()
	{
		if ($this->cms_user->get_group_admin())
		{
			$id = $this->cms_user->get_user_id();

			if ($this->cms_user->get_group_files()){
				$dir = FCPATH.'public/userfiles/';
				$url = '/public/userfiles/';
			} else {
				$dir = FCPATH.'public/userfiles/'.$id.'/';
				$url = '/public/userfiles/'.$id.'/';
			}

			if(!is_dir($dir)) mkdir($dir, 0755, true);

			$opts = array(
				'roots' => array(
					array(
						'driver'        => 'LocalFileSystem',
						'path'          => $dir,
						'URL'           => $url
					)
				)
			);
			$this->load->library('efinder', $opts);
		}
	}

    // ------------------------------------------------------------------------

    /**
     * Функция, отдающая типовые шаблоны для селектов
     *
     * @access	public
     * @return	string
     */

    function get_ajax_default_format ()
    {
        $script = '<script>
        function formatItems1 (items) {
          if (items.loading) return items.text;
          var markup = items.text;
          return markup;
        }
        function formatItemsSelection1 (items) {
          return items.text || items.id;
        }
        </script>';

        return $script;
    }

    /**
     * Функция, отдающая шаблоны с иконками для селектов
     *
     * @access	public
     * @return	string
     */

    function get_ajax_icon_format ($folder)
    {
        $script = '<script>
        function formatItems_icon (items) {
            if (items.loading) return items.text;
            var iid = Math.ceil(items.id/1000);
            var avatar;
            
            if(items.id == 0){
                avatar = "<div class=\'select2-result-repository__avatar\'></div>";
            } else {
                avatar = "<div class=\'select2-result-repository__avatar\'><a href=\''.$folder.'" + iid + \'/\' + items.id + ".jpg\' target=\'_blank\'><img src=\''.$folder.'" + iid + \'/\' + items.id + "_thumb.jpg\' /></a></div>";
            }
            
            var markup = "<div class=\'select2-result-repository clearfix\'>" +
            "<div class=\'select2-result-repository__avatar\'>"+avatar+"</div>" +
            "<div class=\'select2-result-repository__meta\'>" +
              "<div class=\'select2-result-repository__title\'>" + items.text + "</div>" +
            "</div></div>";
            
            return markup;
        }
        
        function formatItemsSelection_icon (items) {
            
            var iid = Math.ceil(items.id/1000);
            var avatar;
            
            if(items.id == 0){
                avatar = "<div class=\'select2-result-repository__avatar\'></div>";
            } else {
                avatar = "<div class=\'select2-result-repository__avatar\'><a href=\''.$folder.'" + iid + \'/\' + items.id + ".jpg\' target=\'_blank\'><img src=\''.$folder.'" + iid + \'/\' + items.id + "_thumb.jpg\' /></a></div>";
            }
            
            var markup = "<div class=\'select2-result-repository\'>" +
            "<div class=\'select2-result-repository__avatar\'>"+avatar+"</div>" +
            "<div class=\'select2-result-repository__meta\'>" +
              "<div class=\'select2-result-repository__title\'>" + items.text + "</div>" +
            "</div></div>";
            return markup || items.text || items.id;
        }
        </script>';

        return $script;
    }

	/**
	 * Функция, отдающая аякс-код для селектов
	 *
	 * @access	public
	 * @param   int     - id поля
	 * @param   string  - адрес вызова /модель/метод
	 * @param   string  - префикс для класса селектов
	 * @param   int     - тип отображения выпадающего списка (вызов сторонней функции)
	 * @return	string
	 */
	function get_ajax ($key, $url, $prefix, $mode = 1, $tags = false)
	{
		$script ='<script>
        $(document).ready(function () {
            $(".js-data-ajax-'.$prefix.'-'.$key.'").select2({
            '.(($tags)?'tags: true, tokenSeparators: [","],':'');
		$script .='language: "ru", 
              ajax: {
                method: "POST",
                url: "'.$url.'",
                dataType: \'json\',
                delay: 250,
                data: function (params) {
                  return {
                    search: params.term, // search term
                    page: params.page,
                    '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'"
                  };
                },
                processResults: function (data, params) {
                  // parse the results into the format expected by Select2
                  // since we are using custom formatting functions we do not need to
                  // alter the remote JSON data, except to indicate that infinite
                  // scrolling can be used
                  params.page = params.page || 1;
        
                  return {
                    results: data.items,
                    pagination: {
                      more: (params.page * 10) < data.total_count
                    }
                  };
                },
                cache: true
              },
              escapeMarkup: function (markup) { return markup; },
              minimumInputLength: 3,
              templateResult: formatItems'.$mode.',
              templateSelection: formatItemsSelection'.$mode.'
            });
        });
        </script>';

		return $script;
	}

    /**
     * Функция, отдающая массивы значений для селекта при вводе посковой фразы
     *
     * @access	public
     * @param   string  - имя поля ключа
     * @param   string  - имя поля содержащего название
     * @param   string  - имя таблицы
     * @param   int     - вид отдачи (1 - типовой, 2 - с картинками)
     * @return	string
     */
    function get_ajax_query ($key_field, $name_field, $table, $mode = 1, $filter = false, $folder = false)
    {
        $this->load->library('search');
        $this->load->helper('text');

        $page  = intval($this->input->post('page', TRUE))*10;
        $words = text2words($this->input->post('search', TRUE));
        $words = $this->search->index_prepare($words, 'ru_RU');
        $words = $words['words'];

        if($filter && $folder && $page == 0){
            $val_arr[] = array(
                'id' => 0,
                'text' => 'Показать все варианты',
                'img' => '/public/upload/thumb.jpg'
            );
        } else {
            $val_arr = array();
        }

        // Получаем список позиций с учетом поисковых слов без лимита
        $this->db->select($key_field.' AS id');
        $this->db->from($table);
        foreach ($words as $keyw => $value){
            ($keyw == 0) ? $this->db->like($name_field, $value, 'both') : $this->db->or_like($name_field, $value, 'both');
        }
        $query  = $this->db->get();
        $total_count = $query->num_rows();

        // Получаем список позиций с учетом поисковых слов с лимитом
        $this->db->select($key_field.' AS id, '.$name_field.' AS name');
        $this->db->from($table);
        foreach ($words as $keyw => $value){
            ($keyw == 0) ? $this->db->like($name_field, $value, 'both') : $this->db->or_like($name_field, $value, 'both');
        }
        $this->db->order_by($name_field, 'ASC');
        $page = ($page) ? $page-10 : $page;
        $this->db->limit(10, $page);
        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                if($mode == 1) {
                    $val_arr[] = array(
                        'id' => $row->id,
                        'text' => $row->name
                    );
                }
                if($mode == 2 && $folder) {
                    $val_arr[] = array(
                        'id'   => $row->id,
                        'text' => $row->name,
                        'img'  => $this->get_img($row->id, $folder)
                    );
                }
            }
        }

        $res_arr = array(
            'total_count' => $total_count,
            'items'       => $val_arr
        );

        return json_encode($res_arr);
    }

    /**
     * Получаем URL изображения по id для вставки в src
     *
     * @access  public
     * @param   int     - id фото
     * @return  string
     */
    function get_img($id, $folder)
    {
        $path = FCPATH.substr($folder, 1).$id.'_thumb.jpg';
        $url  = $folder.$id.'_thumb.jpg';
        $durl  = $folder.'thumb.jpg';

        if (is_file ($path)) {
            return $url;
        } else {
            return $durl;
        }
    }

    /**
     * Массовое сохранение параметров в таблице пересечений
     *
     * @access	public
     * @param   string - базовая часть имени поля из POST массива, содержащее данные
     * @param   string - имя поля ключа
     * @param   string - имя поля связанного ключа
     * @param   string - имя поля id в таблице пересечений
     * @param   string - имя таблицы пересечения
     * @return	void
     */
    function mass_save($field_base, $key, $key_rel, $key_cross, $table)
    {
        if ($this->input->post() && is_array($this->input->post()) && $this->input->post('PME_sys_savelist')) {
            foreach ($this->input->post() as $k => $v) {
                // события
                if (preg_match_all('/^'.$field_base.'_([0-9]*)$/', $k, $matches)) {

                    // Удаляем старые записи
                    $this->db->where($key, $matches[1][0]);
                    $this->db->delete($table);

                    // Вносим новые записи
                    if (is_array($this->input->post($matches[0][0], TRUE))) {
                        foreach ($this->input->post($matches[0][0], TRUE) as $value) {
                            $data = array(
                                $key_cross  => '',
                                $key        => $matches[1][0],
                                $key_rel    => trim($value)
                            );

                            $this->db->insert($table, $data);
                        }
                    }
                }
            }
        }
    }
}

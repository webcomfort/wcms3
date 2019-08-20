<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление фотографиями
 */

class Adm_gallery_photos extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        $this->load->model('Cms_utils');
	    $this->load->model('Cms_myedit');
	    $this->load->model('Cms_tags');
        $this->load->helper('form');
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
	    // JS функции для модуля select2 - простой список
	    $meta = $this->Cms_myedit->get_ajax_default_format();
        return $meta;
    }

    // ------------------------------------------------------------------------

	/**
	 * Функция, список выбора для пересечений с тегами
	 *
	 * @access	public
	 * @param   int - article id
	 * @return	string
	 */
	function get_insert_ui($aid){

    	// Строим селект
		$this->load->helper('form');
		$fopts = 'id="gallery_insert_select_'.$aid.'" class="js-data-ajax-gal-'.$aid.'"';
		$ui = form_dropdown('insert_gal_'.$aid, [], [], $fopts);

    	$ui .= '<a class="btn btn-success btn-xs" href="#" role="button" data-toggle="modal" data-target="#GalleryInsModal'.$aid.'" id="gallery_add_button_'.$aid.'"><span class="glyphicon glyphicon-plus"></span></a>';
		$ui .= '<a class="btn btn-primary btn-ins-'.$aid.' ml5" href="#" role="button" data-id="'.$aid.'">Вставить</a>';
		$ui .= $this->load->view('admin/ins_gallery', array('ins_id'=>$aid), true);
		// Получаем js-код для этого поля
		$ui .= $this->Cms_myedit->get_ajax($aid, '/adm_gallery_photos/p_get_gal', 'gal', 1, false);
    	return $ui;
	}

	/**
	 * Функция, генерирующая список событий для <select> (внешний вызов)
	 *
	 * @access  public
	 * @return  string
	 */
	function p_get_gal()
	{
		$rights = $this->cms_user->get_user_rights();
		if ( is_array($rights) && ($rights['Adm_gallery_photos.php']['edit'] || $rights['Adm_gallery_photos.php']['copy'] || $rights['Adm_gallery_photos.php']['add']) )
		{
			echo $this->Cms_myedit->get_ajax_query ('gallery_id', 'gallery_name', 'w_galleries', 1);
		}
	}

    /**
     * Функция, отдающая html для других функций
     *
     * @access  private
     * @return  string
     */

    function _get_inc_form($inc_id)
    {
        $this->session->unset_userdata('photo_filter');
        $modal = '
            <div class="form-group">
                <label for="gallery_name">Галерея</label>
                <input type="text" class="form-control" id="gallery_name" name="gallery_name" placeholder="Введите название галереи">
            </div>
            <div class="form-group">
                <label for="gallery_view_id">Внеший вид (макет)</label>
                '.form_dropdown('gallery_view_id', $this->_get_view_list(), '', 'class="form-control"').'
            </div>
            <div class="form-group">
                <label for="galfile">Выберите фотографии</label><br>
				<div class="dropzonearea" id="galleryDropzone'.$inc_id.'"><div class="dz-default dz-message"><span>Перетащите сюда файлы для загрузки</span></div></div>
				<div id="gallery_hidden'.$inc_id.'">
			</div>
			
			<script>
			    $(document).ready(function () {
				    var galleryDropzone'.$inc_id.' = new Dropzone("div#galleryDropzone'.$inc_id.'", {
				        url: "/cms_myedit/p_drop_upload/",
				        paramName: "file",
				        parallelUploads: 1,
		                acceptedFiles: "image/*",
		                addRemoveLinks: true,
				        chunking: true,
				        chunkSize: 3145728,
				        retryChunks: true, 
				        retryChunksLimit: 3,
				        renameFile: function(file){
			                var fname = file.name;
				            var ext = fname.slice((Math.max(0, fname.lastIndexOf(".")) || Infinity) + 1);
			                return getRandom(1,1000)+"_"+hashCode(file.name)+"."+ext;
			            },
				        init: function() {
				            this.on("sending", function (file, xhr, formData) {
				                formData.append("' . $this->security->get_csrf_token_name() . '", "' . $this->security->get_csrf_hash() . '");
				                formData.append("module", "'.basename(__FILE__).'");
				                formData.append("file_name", file.upload.filename);
				            });
				            this.on("success", function (file, response) {
				                $("#gallery_hidden'.$inc_id.'").append($(\'<input type="hidden" \' +
			                                      \'name="gallery_hidden_files[]" \' +
			                                      \'value="\' + file.upload.filename + \'">\'));
				            });
				            this.on("removedfile", function (file) {
				                var filename;
				                if (typeof file.upload === "undefined"){
								    filename = file.name;
								} else {
									filename = file.upload.filename;
								}
				                $.ajax({
						            method: "POST",
						            url: "/cms_myedit/p_drop_delete/",
						            data: {
						            "'.$this->security->get_csrf_token_name().'": "'.$this->security->get_csrf_hash().'",
						            "module": "'.basename(__FILE__).'",
						            "file_name": filename
						            }
						        });
				                $("input[value=\'"+filename+"\']").remove();
				            });	
				        }
				    });
	            });
			</script>		
	        ';

	    return $modal;
    }

    // ------------------------------------------------------------------------

    /**
     * Функция для вывода формы через ajax
     *
     * @access  public
     * @return  string
     */

    function p_add_gal()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            echo $this->_get_inc_form($this->input->post('inc_id', TRUE));
        }
        else
        {
            echo "У вас недостаточно прав для этой операции.";
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция для добавления галереи через ajax
     *
     * @access  public
     * @return  string
     */

    function p_save_gal()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {
            $data = array(
               'gallery_id'        => '',
               'gallery_name'      => $this->input->post('gallery_name', TRUE),
               'gallery_view_id'   => $this->input->post('gallery_view_id', TRUE),
               'gallery_active'    => '1',
               'gallery_lang_id'   => $this->session->userdata('w_alang')
            );

            $this->db->insert('w_galleries', $data);
	        $files = $this->input->post('gallery_hidden_files', true);
	        if (is_array($files))
	        {
	        	$this->_upload_files($this->db->insert_id(), $files, $this->input->post('gallery_name', TRUE));
	        }

            echo '<div class="alert alert-success" role="alert">Галерея была успешно добавлена!</div>'.$this->_get_inc_form($this->input->post('inc_id', TRUE));
        }
        else
        {
            echo "У вас недостаточно прав для этой операции.";
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция, загружающая файлы
     *
     * @access  private
     * @return  void
     */

    function _upload_files($gal_id = 0, $files = array(), $name = '')
    {
        if($gal_id) {
	        $this->load->library( 'image_lib' );
	        $dimensions = $this->config->item( 'cms_gallery_sizes' );
	        $i = 10;
	        foreach ( $files as $value ) {
		        $data = array(
			        'photo_id'         => '',
			        'photo_gallery_id' => $gal_id,
			        'photo_name'       => $name,
			        'photo_sort'       => $i,
			        'photo_active'     => '1',
			        'photo_lang_id'    => $this->session->userdata( 'w_alang' )
		        );
		        $this->db->insert( 'w_gallery_photos', $data );
		        $id  = $this->db->insert_id();
		        $this->image_lib->src_file_move ($value, $this->config->item( 'cms_gallery_dir' ), $id, false, true, $dimensions, true);
		        $i = $i + 10;
	        }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Функция для смены значений поля select галереи через ajax
     *
     * @access  public
     * @return  string
     */

    function p_return_gal()
    {
        $rights = $this->cms_user->get_user_rights();

        if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
        {

            $options = '<option value="0">Не выбрано</option>';

            // Получаем данные
            $this->db->select('gallery_id AS id, gallery_name AS name')
                ->from('w_galleries')
                ->where('gallery_lang_id', $this->session->userdata('w_alang'));

            $query  = $this->db->get();

            if ($query->num_rows() > 0)
            {
                $query2 = $this->db->query('SELECT MAX(gallery_id) as mid FROM w_galleries');
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
        $filter_values = array();
		
		// Создаем галерею
        if (isset($_POST['gal_name']) && $_POST['gal_name'] != '') $this->_add_gallery();

        // Получаем данные
        $this->db->select('gallery_id AS id, gallery_name AS name')
            ->from('w_galleries')
			->order_by('gallery_name', 'asc')
            ->where('gallery_lang_id', $this->session->userdata('w_alang'));

        $query  = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $filter_values[$row->id] = $row->name;
            }
        }

        if($this->uri->segment(3) == 'lang' && preg_int ($this->uri->segment(4))) $this->session->unset_userdata('photo_filter');

        // Сессия
        if (!$this->session->userdata('photo_filter'))
        {
            $this->session->set_userdata(array('photo_filter' => current(array_keys($filter_values))));
        }

        if($this->input->post('photo_filter', true) && preg_int($this->input->post('photo_filter', true)))
        {
            $this->session->set_userdata(array('photo_filter' => $this->input->post('photo_filter', true)));
        }

        if($this->uri->segment(3) == 'gallery' && preg_int($this->uri->segment(4)))
        {
            $this->session->set_userdata(array('photo_filter' => $this->uri->segment(4)));
        }

        // Отображение
        $data = array(
            'filter_name'   => 'Выберите галерею',
            'filter_action' => '/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/',
            'filter_field'  => 'photo_filter',
            'filter_class'  => ' select2',
            'filter_active' => $this->session->userdata('photo_filter'),
            'filter_values' => $filter_values
        );

        $filters = '
        <div class="row"><div class="col-xs-12"><div class="p20 ui-block">
		
		<div class="row">
            <div class="col-xs-6">'.
                $this->load->view('admin/filter_default', $data, true)
            .'</div>
            <div class="col-xs-2"><h6 class="m0 mb15">&nbsp</h6>{@module mod_gallery '.$this->session->userdata('photo_filter').'@}</div>
			<div class="col-xs-4">
                <h6 class="m0 mb10">Быстрое создание галереи</h6>
                '.form_open('/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/', array('class' => 'm0 form-inline', 'role' => 'form')).'
                <div class="form-group">
					<input type="text" name="gal_name" placeholder="Имя галереи" class="form-control" />
				</div>
				<div class="form-group">
					'.form_dropdown('gal_tpl', $this->_get_view_list(), '', 'class="form-control select2"').'
				</div>
				<button type="submit" class="btn btn-primary">Создать</button>
                </form>
            </div>
        </div>
		
		</div> </div> </div>';

        if(!$this->input->get('PME_sys_operation') && !$this->input->post('PME_sys_operation')) {
			$filters .= '<div class="row"><div class="col-xs-12"><div class="p20 ui-block">
			<input type="hidden" name="' . $this->security->get_csrf_token_name() . '" id="token" value="' . $this->security->get_csrf_hash() . '">
			<div class="dropzonearea" id="galleryDropzone"><div class="dz-default dz-message"><span>Перетащите сюда файлы для загрузки</span></div></div>
			</div> </div> </div>
			
			<script>
			    $(document).ready(function () {
				    var galleryDropzone = new Dropzone("div#galleryDropzone", {
				        url: "/adm_gallery_photos/p_drop_upload/",
				        paramName: "file",
				        parallelUploads: 1,
		                acceptedFiles: "image/*",
				        chunking: true,
				        chunkSize: 3145728,
				        retryChunks: true, 
				        retryChunksLimit: 3,
				        renameFile: function(file){
			                var fname = file.name;
				            var ext = fname.slice((Math.max(0, fname.lastIndexOf(".")) || Infinity) + 1);
			                return getRandom(1,1000)+"_"+hashCode(file.name)+"."+ext;
			            },
				        init: function() {
				            this.on("sending", function (file, xhr, formData) {
				                formData.append("' . $this->security->get_csrf_token_name() . '", "' . $this->security->get_csrf_hash() . '");
				                formData.append("gallery_id", "' . $this->session->userdata( 'photo_filter' ) . '");
				                formData.append("file_name", file.upload.filename);
				            });
				            this.on("success", function(file) {
				                $.ajax({
						            method: "GET",
						            url: "/adm_gallery_photos/p_get_output/",
						            data: {url: "/' . urlencode( $this->uri->segment( 1 ) . '/' . $this->uri->segment( 2 ) ) . '"}
						        }).done(function(result) {
						            $("#admin_interface").html(result);
						            $(".thumbnail").each(function() { $(this).css("height","auto"); });
						        });
				            });
				        }
				    });
	            });
			</script>		
	        ';
		}

        return $filters;
    }

	/**
	 * Функция, загружающая файлы из дроп-зоны
	 *
	 * @access	public
	 * @return	void
	 */
    function p_drop_upload(){
	    $rights = $this->cms_user->get_user_rights();

	    if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
	    {
		    $gal_id = $this->input->post("gallery_id", TRUE);
		    $file_name = $this->input->post('file_name', TRUE);
	    	if($gal_id == 0) $gal_id = $this->session->userdata('photo_filter');

		    $this->load->library('image_lib');
		    $dimensions = $this->config->item('cms_gallery_sizes');
		    $path = FCPATH.substr($this->config->item('cms_gallery_dir'), 1);
		    if(!is_dir($path)) mkdir($path, 0, true);

		    $this->db->select('gallery_name AS name, gallery_view_id AS vid');
		    $this->db->from('w_galleries');
		    $this->db->where('gallery_id', $gal_id);

		    $query = $this->db->get();

		    if ($query->num_rows() > 0) {

			    $chunk = ($this->input->post('dzchunkindex', TRUE) != '') ? $this->input->post('dzchunkindex', TRUE) : false;
			    $chunkcount = ($this->input->post('dzchunkindex', TRUE) != '') ? $this->input->post('dztotalchunkcount', TRUE) : false;

			    if($chunk === false || $chunkcount == $chunk+1) {
				    $row   = $query->row();
				    $where = array(
					    'field' => 'photo_gallery_id',
					    'value' => $gal_id
				    );
				    $i     = $this->Cms_utils->get_max_sort( 'photo_sort', 'w_gallery_photos', $where );
				    $data  = array(
					    'photo_id'         => '',
					    'photo_gallery_id' => $gal_id,
					    'photo_name'       => $row->name,
					    'photo_sort'       => $i,
					    'photo_active'     => '1',
					    'photo_lang_id'    => $this->session->userdata( 'w_alang' )
				    );
				    $this->db->insert( 'w_gallery_photos', $data );
				    $id  = $this->db->insert_id();
				    $iid = ceil( intval( $id ) / 1000 );
				    if ( ! is_dir( $path . $iid . '/' ) ) {
					    mkdir( $path . $iid . '/', 0, true );
				    }
			    }

			    if($chunk !== false){
				    $out = @fopen($path.'/'.$this->input->post('file_name', TRUE), ($chunk != 0) ? "ab" : "wb");
				    $in = @fopen($_FILES["file"]["tmp_name"], "rb");
				    while ($buff = fread($in, 4096)) {
					    fwrite($out, $buff);
				    }
				    fclose($out);
				    fclose($in);
				    if($chunkcount == $chunk+1){
					    rename($path.'/'.$this->input->post('file_name', TRUE), $path.'/'.$iid.'/'.$this->input->post('file_name', TRUE));
				    	$this->image_lib->src_file_move ($file_name, $this->config->item('cms_gallery_dir'), $id, false, true, $dimensions, false);
				    }
			    } else {
				    $file_path = $path . $iid . '/' . $file_name;
				    move_uploaded_file( $_FILES['file']['tmp_name'], $file_path );
				    $this->image_lib->src_file_move ($file_name, $this->config->item('cms_gallery_dir'), $id, false, true, $dimensions, false);
			    }
		    }
	    }
    }

	/**
	 * Функция, отдающая основной интерфейс через аякс
	 *
	 * @access	public
	 * @return	string
	 */

	function p_get_output()
	{
		$rights = $this->cms_user->get_user_rights();

		if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) ) {
			$this->load->library( 'myedit', $this->_get_crud_model() );

			echo $this->myedit->get_output();
		}
	}
	
	// ------------------------------------------------------------------------

    /**
     * Массив макетов для формирования выпадающего списка при создании галереи
     *
     * @access  private
     * @return  array
     */

    function _get_view_list()
    {
        $views  = $this->config->item('cms_gallery_views');

        foreach ($views as $key => $value)
        {
            $val_arr[$key] = $value['name'];
        }

        return $val_arr;
    }
	
	// ------------------------------------------------------------------------

    /**
     * Создаем галерею
     *
     * @access  private
     * @return  void
     */

    function _add_gallery()
    {
        $data = array(
		   'gallery_id' => '',
		   'gallery_name' => $this->input->post('gal_name', true),
		   'gallery_view_id' => $this->input->post('gal_tpl', true),
		   'gallery_active' => 1,
		   'gallery_lang_id' => $this->session->userdata('w_alang')
		);

		$this->db->insert('w_galleries', $data);
		
		$this->session->set_userdata(array('photo_filter' => $this->db->insert_id()));
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
     * Функция, отдающая массив галерей
     *
     * @access  private
     * @return  array
     */
    function _get_galleries()
    {
        // Получаем данные
        $this->db->select('gallery_id AS id, gallery_name AS name')
            ->from('w_galleries')
            ->where('gallery_lang_id', $this->session->userdata('w_alang'));

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
	 * AJAX сортировка
	 *
	 * @access	public
	 * @return	array
	 */
	function p_sortable() {
		$rights = $this->cms_user->get_user_rights();
		if ( is_array($rights) && (isset($rights[basename(__FILE__)])) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) ) {
			$i = 10;
			foreach ($this->input->get('item', true) as $value) {
				$data = array(
					'photo_sort' => $i
				);
				$this->db->where('photo_id', $value);
				$this->db->update('w_gallery_photos', $data);
				$i = $i+10;
				echo $value;
			}
		}
	}

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
        $this->load->model('Cms_myedit');
        $opts = $this->Cms_myedit->get_base_opts();
		
		// Переопределяем кнопки
		$opts['buttons']['L']['up'] = array('add','<<','<','>','>>','goto_combo');
		$opts['buttons']['L']['down'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['up'] = $opts['buttons']['L']['up'];
        $opts['buttons']['F']['down'] = $opts['buttons']['L']['up'];

        // Таблица
        $opts['tb'] = 'w_gallery_photos';
        $opts['list_view'] = array(
	        'list_view' => 'admin/list_of_photos',
	        'file'      => 'pic',
        );

        // Ключ
        $opts['key'] = 'photo_id';

        // Начальная и ручная(UI) сортировка
        $opts['sort_field'] = array('photo_sort');
        $opts['ui_sort_field'] = 'photo_sort';

        // Кол-во записей для вывода на экран
        $opts['inc'] = 1000;

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
            "photo_lang_id = '" . $this->session->userdata('w_alang') . "'",
            "photo_gallery_id = '".$this->session->userdata('photo_filter')."'"
        );

        // Триггеры
		// $this->opts['triggers']['insert']['after'] = '';
		// $this->opts['triggers']['update']['after'] = '';
		// $this->opts['triggers']['delete']['before'] = '';
        $opts['triggers']['insert']['after']  = APPPATH.'triggers/photo_insert_after.php';
        $opts['triggers']['update']['after']  = APPPATH.'triggers/photo_update_after.php';
		$opts['triggers']['delete']['after']  = APPPATH.'triggers/photo_delete_after.php';

        // Логирование: общее название класса и поле где хранится название объекта
        $opts['logtable_title'] = 'Фото';
        $opts['logtable_field'] = 'photo_name';

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

        $opts['fdd']['photo_id'] = array(
            'name'          => 'Номер по б/д',
            'select'        => 'T',
            'options'       => 'F', // Автоинкремент
            'maxlen'        => 11,
            'default'       => '0',
            'sort'          => true
        );
        $opts['fdd']['photo_gallery_id'] = array(
            'name'          => 'Галерея',
            'select'        => 'T',
            'options'       => 'ACPH',
            'maxlen'        => 11,
            'default'       => $this->session->userdata('photo_filter'),
            'sort'          => false,
            'tab'           => array (
                'name'      => 'Основные параметры',
                'default'   => true,
            )
        );
        $opts['fdd']['photo_name'] = array(
            'name'          => 'Название',
            'options'       => 'LACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => true,
            'sort'          => true,
            'help'          => 'Введите название фотографии.'
        );
        $opts['fdd']['pic'] = array(
            'name'          => 'Фото',
            'required'      => false,
            'sort'          => false,
            'size'          => '50',
            'nodb'          => true,
            'file'          => array (
                'tn'        => '_thumb',
                'url'       => $this->config->item('cms_gallery_dir'),
                'multiple'  => false,
                'accepted'  => 'image/*',
            ),
            'help'          => 'Выберите фото на своем компьютере для загрузки. Удаление фото из режима редактирования приводит к его безвозвратному удалению.'
        );

        // Tags
		$opts = array_merge_recursive((array)$opts, (array)$this->Cms_tags->get_admin_opts($id, 'photo'));

        if($publish)
		{
			$opts['fdd']['photo_active'] = array(
				'name'          => 'Статус',
				'select'        => 'D',
				'options'       => 'LACPDV',
				'values2'       => array (
					'1'         => 'Активно',
					'0'         => 'Неактивно'
				),
				'save'          => true,
				'default'       => 0,
				'help'          => 'Статус фото на сайте. Если вы хотите, чтобы фото не было видно на сайте - сделайте его неактивным, т.е. совсем не обязательно удалять фото, чтобы его скрыть.'
			);
		}
        $where = array(
            'field' => 'photo_gallery_id',
            'value' => $this->session->userdata('photo_filter')
        );
        $opts['fdd']['photo_sort'] = array(
            'name'          => 'Сортировка',
            'select'        => 'T',
            'options'       => 'LACPD',
            'default'       => $this->Cms_utils->get_max_sort('photo_sort', 'w_gallery_photos', $where),
            'save'          => true,
            'sort'          => false
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['photo_link'] = array(
            'name'          => 'Ссылка с фотографии',
            'options'       => 'ACPDV',
            'select'        => 'T',
            'maxlen'        => 65535,
            'required'      => false,
            'sort'          => true,
            'tab'           => 'Дополнительно',
            'help'          => 'Введите сюда ссылку. Может потребоваться для некоторых видов галерей.'
        );
        $opts['fdd']['photo_text'] = array(
            'name'          => 'Пояснительный текст',
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
            'help'          => 'Введите в это поле пояснительный текст для фотографии. Может потребоваться для некоторых видов галерей.'
        );

        // ------------------------------------------------------------------------

        $opts['fdd']['photo_lang_id'] = array(
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

        // ------------------------------------------------------------------------

		return $opts;
	}
}

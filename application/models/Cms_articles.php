<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для статей
 */

class Cms_articles extends CI_Model {

    function __construct()
    {
        parent::__construct();
	    $this->load->model('Cms_myedit');
    }

    // ------------------------------------------------------------------------

    /**
     * Функция, отдающая вспомогательный html для редактирования статей
     *
     * @access  public
     * @param   int - номер блока
     * @param   string - статья
     * @param   int - номер фона
     * @param   int - номер вида
     * @return  string
     */
    function p_get_html($id=false, $type='pages', $trigger=1, $article='', $bg=0, $view=1, $place=0, $full=false)
    {
        if($this->input->get('id', TRUE)) $id = $this->input->get('id', TRUE);
	    if($this->input->get('type', TRUE)) $type = $this->input->get('type', TRUE);
	    if($this->input->get('trigger', TRUE)) $trigger = $this->input->get('trigger', TRUE);

        if($id){

            $response['div'] = '<div class="article-div" data-id="'.$id.'">';

            $views = $this->_get_article_views($type, $trigger, $place);
            $view  = (array_key_exists($view, $views)) ? $view : key($views);

            $response['selects'] = '<div class="article-selects-div">
            '.$this->_get_article_bg($id, $bg).'
            '.form_dropdown('page_article_place_'.$id, $this->_get_article_places($type, $trigger), $place, 'data-id="'.$id.'" data-trigger="'.$trigger.'" data-type="'.$type.'" class="select2 place-select"').'
            '.form_dropdown('page_article_view_'.$id, $this->_get_article_views($type, $trigger, $place), $view, 'id="page_article_view_'.$id.'" class="select2 view-select"').'
            </div>';

            $response['buttons'] = '<div class="article-buttons-div">
<button class="btn btn-primary btn-xs article-button-plus" data-id="'.$id.'" title="Добавить еще блок"><span class="glyphicon glyphicon-plus"></span></button>
<button class="btn btn-default btn-xs article-button-move article-button-up" data-id="'.$id.'" title="Наверх"><span class="glyphicon glyphicon-chevron-up"></span></button>
<button class="btn btn-default btn-xs article-button-move article-button-down" data-id="'.$id.'"  title="Вниз"><span class="glyphicon glyphicon-chevron-down"></span></button>
<button class="btn btn-default btn-xs article-button-remove" data-id="'.$id.'" title="Удалить"><span class="glyphicon glyphicon-remove"></span></button>
</div>';
            $response['hidden']  = '<input type="hidden" class="page_article_order" name="page_article_order_'.$id.'" value="'.$id.'">';
            $response['textarea'] = '<textarea name="page_article_'.$id.'" class="htmleditor">'.$article.'</textarea>';

        }

        if($this->input->get('id', TRUE) && $full === false) echo json_encode($response);
        else return $response;
    }

	/**
	 * Функция, генерирующая текстовые поля с возможностью редактирования (ajax)
	 *
	 * @access  public
	 * @return  void
	 */
	function p_get_articles()
	{
		if($this->input->get('id', TRUE) || $this->input->get('id', TRUE) == 0) $id = $this->input->get('id', TRUE);
		if($this->input->get('type', TRUE) || $this->input->get('type', TRUE) == 0) $type = $this->input->get('type', TRUE);
		if($this->input->get('trigger', TRUE) || $this->input->get('trigger', TRUE) == 0) $trigger = $this->input->get('trigger', TRUE);

		if(isset($id) && isset($type) && isset($trigger)){
			echo $this->_get_article_editors($id, $type, $trigger, true);
		}
	}

	/**
	 * Функция, отдающая текстовые поля с возможностью редактирования
	 *
	 * @access  public
	 * @param   int - id родителя
	 * @param   string - тип родителя
	 * @param   int - триггер, переключающий макеты
	 * @return  string
	 */
    function get_article_editors($id, $type, $trigger = 1)
	{
		$data  = $this->config->item('cms_articles');
		$type_trigger = $data[$type]['trigger'];
		$script = '';
		if($type_trigger){
			$script = '<script>
			$(document).ready(function () {
				$( "#'.$type_trigger.'" ).change(function() {
				    var trigger = $(this).val();
				    $("#page_article_trigger").val(trigger);
				    $.ajax({
			            method: "GET",
			            url: "/cms_articles/p_get_articles/",
			            data: { id: "'.$id.'", type: "'.$type.'", trigger: trigger }
			        }).done(function(result) {			           
			            $( "#articles_area" ).html( result );
			            
			            $(".select2").select2({ language: "ru" });
					    if ($(".select2_icon")[0]){
					        $(".select2_icon").select2({
					            language: "ru",
					            escapeMarkup: function (markup) { return markup; },
					            templateResult: formatItems_icon,
					            templateSelection: formatItemsSelection_icon
					        });
					    }
					    $(".htmleditor").each(function(index){
					        CKEDITOR.replace( this, { customConfig: "/public/admin/third_party/ckeditor/config.js" });
					    });
			        });
				});
				
				$( ".place-select" ).change(function() {
				    
				    var id = $(this).data( "id" );
				    var trigger = $(this).data( "trigger" );
				    var type = $(this).data( "type" );
				    var place = $(this).val();
				    var select = $("#page_article_view_" + id);				   
				    
				    $.ajax({
			            method: "GET",
			            url: "/cms_articles/p_get_views/",
			            data: { id: id, type: type, trigger: trigger, place: place }
			        }).done(function(result) {
			            select.empty().append(result);
			        });
				});
			});
			</script>';
		}

		return $this->_get_article_editors($id, $type, $trigger).$this->Cms_myedit->get_ajax_icon_format($this->config->item('cms_bg_dir')).$script;
	}

	/**
	 * Функция, генерирующая значения селектов для ajax вызовов
	 *
	 * @access  public
	 * @return  void
	 */
	function p_get_views(){
		if($this->input->get('id', TRUE) || $this->input->get('id', TRUE) == 0) $id = $this->input->get('id', TRUE);
		if($this->input->get('type', TRUE) || $this->input->get('type', TRUE) == 0) $type = $this->input->get('type', TRUE);
		if($this->input->get('trigger', TRUE) || $this->input->get('trigger', TRUE) == 0) $trigger = $this->input->get('trigger', TRUE);
		if($this->input->get('place', TRUE) || $this->input->get('place', TRUE) == 0) $place = $this->input->get('place', TRUE);

		if(isset($id) && isset($type) && isset($trigger) && isset($place)){
			$data  = $this->config->item('cms_articles');
			$views = $data[$type]['values'][$trigger]['places'][$place]['views'];
			$options = '';
			foreach ($views as $key => $value)
			{
				$options .= '<option value="'.$key.'">'.$value['name'].'</option>';
			}
			echo $options;
		}
	}

    /**
     * Функция, генерирующая текстовые поля с возможностью редактирования
     *
     * @access  private
     * @param   int - id родителя
     * @param   string - тип родителя
     * @param   int - триггер, переключающий макеты
     * @param   bool - вариант вывода полей
     * @return  string
     */
    function _get_article_editors($id, $type, $trigger = 1, $full=false)
    {
    	$i = 1;
        $fields = '';
	    $trigger_field = '<input type="hidden" id="page_article_trigger" name="page_article_trigger" value="'.$trigger.'"><input type="hidden" id="page_article_type" name="page_article_type" value="'.$type.'">';

        $this->db->select('article_text, article_bg_id, article_view_id, article_place_id');
        $this->db->where('article_pid', $id);
        $this->db->where('article_pid_type', $type);
        $this->db->order_by('article_order', 'ASC');
        $query = $this->db->get('w_pages_articles');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $html = $this->p_get_html($i, $type, $trigger, $row->article_text, $row->article_bg_id, $row->article_view_id, $row->article_place_id, $full);

                $fields .= $html['div'];
                $fields .= $html['selects'];
                $fields .= $html['buttons'];
                $fields .= $html['hidden'];
                $fields .= $html['textarea'];
                $fields .= '</div>';

                $i++;
            }
        }
        else
        {
            $html = $this->p_get_html($i, $type, $trigger);

            $fields .= $html['div'];
            $fields .= $html['selects'];
            $fields .= $html['buttons'];
            $fields .= $html['hidden'];
            $fields .= $html['textarea'];
            $fields .= '</div>';
        }

	    $fields .= $trigger_field;

        return '<div id="articles_area">'.$fields.'</div>';
    }

    /**
     * Массив макетов для формирования выпадающего списка
     *
     * @access	private
     * @return	array
     */

    function _get_article_views($type, $trigger, $place)
    {
        $data  = $this->config->item('cms_articles');
	    $type_data = $data[$type]['values'][$trigger]['places'][$place]['views'];

        foreach ($type_data as $key => $value)
        {
            $val_arr[$key] = $value['name'];
        }

        return $val_arr;
    }

    /**
     * Массив мест статей для формирования выпадающего списка
     *
     * @access	private
     * @return	array
     */

    function _get_article_places($type, $trigger)
    {
	    $data  = $this->config->item('cms_articles');
	    $type_data = $data[$type]['values'][$trigger]['places'];

        foreach ($type_data as $key => $value)
        {
            $val_arr[$key] = $value['name'];
        }

        return $val_arr;
    }

    /**
     * Массив фонов для формирования выпадающего списка
     *
     * @access	private
     * @return	string
     */

    function _get_article_bg($id, $bg=0)
    {
        $this->db->select('bg_id, bg_name');
        $this->db->where('bg_active', 1);
        $this->db->order_by('bg_name', 'ASC');
        $query = $this->db->get('w_backgrounds');

        if ($query->num_rows() > 0)
        {
            $select = '<select name="page_article_bg_'.$id.'" class="select2_icon">';
	        $select .= '<option value="0"'. (($bg == 0) ? 'selected="selected"' : '').'>Без фона</option>';

        	foreach ($query->result() as $row) {
		        $select .= '<option value="'.$row->bg_id.'"'. (($bg == $row->bg_id) ? 'selected="selected"' : '').'>'.$row->bg_name.'</option>';
            }

	        $select .= '</select>';
        }

        return $select;
    }

    /**
     * Статьи на вывод
     *
     * @access  public
     * @param   int
     * @return  array
     */
    function get_articles($id, $type='pages', $trigger=1)
    {
        $articles = array();
	    $data  = $this->config->item('cms_articles');

        $this->db->select('article_id, article_bg_id, article_view_id, article_place_id, article_text');
        $this->db->where('article_pid', $id);
        $this->db->where('article_pid_type', $type);
        $this->db->order_by('article_place_id', 'asc');
        $this->db->order_by('article_order', 'asc');
        $query = $this->db->get('w_pages_articles');

        if ($query->num_rows() > 0)
        {
            $this->load->library('parser');

            foreach ($query->result() as $row)
            {
                $text = $this->parser->parse_modules($row->article_text);
	            $views_array = $data[$type]['values'][$trigger]['places'][$row->article_place_id]['views'];
                $view = (array_key_exists($row->article_view_id, $views_array)) ? $views_array[$row->article_view_id]['file'] : false;
                $data['article_text'] = $text;
                $data['article_bg'] = $this->_get_bg($row->article_bg_id);
                if ($view) $articles[$row->article_place_id][] = $this->load->view('site/'.$view, $data, true);
            }

            return $articles;
        }
        else
        {
            return $articles;
        }
    }

    /**
     * Фон
     *
     * @access  public
     * @param   int
     * @return  array
     */
    function _get_bg($id)
    {
        $iid = ceil(intval($id)/1000);
        $path = FCPATH.substr($this->config->item('cms_bg_dir'), 1).$iid.'/'.$id.'.jpg';
        $url  = $this->config->item('cms_bg_dir').$iid.'/'.$id.'.jpg';
        if (is_file ($path)) return $url;
    }
}
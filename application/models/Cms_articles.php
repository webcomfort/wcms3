<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для статей
 */

class Cms_articles extends CI_Model {

    function __construct()
    {
        parent::__construct();
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
    function p_get_html($id=false, $article='', $bg=0, $view=1, $place=0)
    {
        if($this->input->get('id', TRUE)) $id = $this->input->get('id', TRUE);

        if($id){

            $response['div'] = '<div class="article-div" data-id="'.$id.'">';

            $views = $this->_get_article_views();
            $view  = (array_key_exists($view, $views)) ? $view : key($views);

            $response['selects'] = '<div class="article-selects-div">
            '.$this->_get_article_bg($id, $bg).'
            '.form_dropdown('page_article_view_'.$id, $this->_get_article_views(), $view, 'class="select2"').'           
            '.form_dropdown('page_article_place_'.$id, $this->_get_article_places(), $place, 'class="select2"').'
            </div>';

            $response['buttons'] = '<div class="article-buttons-div">
<button class="btn btn-primary btn-xs article-button-plus" data-id="'.$id.'" title="Добавить еще блок"><span class="glyphicon glyphicon-plus"></span></button>
<button class="btn btn-default btn-xs article-button-move article-button-up" data-id="'.$id.'" title="Наверх"><span class="glyphicon glyphicon-chevron-up"></span></button>
<button class="btn btn-default btn-xs article-button-move article-button-down" data-id="'.$id.'"  title="Вниз"><span class="glyphicon glyphicon-chevron-down"></span></button>
<button class="btn btn-default btn-xs article-button-remove" data-id="'.$id.'" title="Удалить"><span class="glyphicon glyphicon-remove"></span></button>
</div>';
            $response['hidden'] = '<input type="hidden" class="page_article_order" name="page_article_order_'.$id.'" value="'.$id.'">';
            $response['textarea'] = '<textarea name="page_article_'.$id.'" class="htmleditor">'.$article.'</textarea>';

        }

        if($this->input->get('id', TRUE)) echo json_encode($response);
        else return $response;
    }

    /**
     * Функция, генерирующая текстовые поля с возможностью редактирования
     *
     * @access  public
     * @param   int - id родителя
     * @param   string - тип родителя
     * @return  string
     */
    function get_article_editors($id, $type)
    {
	    $this->load->model('Cms_myedit');
    	$i = 1;
        $fields = '';

        $this->db->select('article_text, article_bg_id, article_view_id, article_place_id');
        $this->db->where('article_pid', $id);
        $this->db->where('article_pid_type', $type);
        $this->db->order_by('article_order', 'ASC');
        $query = $this->db->get('w_pages_articles');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $html = $this->p_get_html($i, $row->article_text, $row->article_bg_id, $row->article_view_id, $row->article_place_id);

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
            $html = $this->p_get_html($i);

            $fields .= $html['div'];
            $fields .= $html['selects'];
            $fields .= $html['buttons'];
            $fields .= $html['hidden'];
            $fields .= $html['textarea'];
            $fields .= '</div>';
        }

        return '<div id="articles_area">'.$fields.'</div>'.$this->Cms_myedit->get_ajax_icon_format($this->config->item('cms_bg_dir'));
    }

    /**
     * Массив макетов для формирования выпадающего списка
     *
     * @access	private
     * @return	array
     */

    function _get_article_views()
    {
        $views  = $this->config->item('cms_article_views');

        foreach ($views as $key => $value)
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

    function _get_article_places()
    {
        $places  = $this->config->item('cms_article_places');

        foreach ($places as $key => $value)
        {
            $val_arr[$key] = $value;
        }

        return $val_arr;
    }

    /**
     * Массив фонов для формирования выпадающего списка
     *
     * @access	private
     * @return	string
     *
     * <select name="page_article_view_1" class="select2 select2-hidden-accessible" tabindex="-1" aria-hidden="true">
    <option value="1" selected="selected">В контейнере</option>
    <option value="2">На всю ширину</option>
    </select>
     *
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
    function get_articles($id, $type='pages')
    {
        $articles = array();
        $views = $this->config->item('cms_article_views');

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
                $view = (array_key_exists($row->article_view_id, $views)) ? $views[$row->article_view_id]['file'] : false;
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
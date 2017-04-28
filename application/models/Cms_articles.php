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
     * @return  string
     */
    function p_get_html($id=false, $article='')
    {
        if($this->input->get('id', TRUE)) $id = $this->input->get('id', TRUE);

        if($id){

            $response['div'] = '<div class="article-div" data-id="'.$id.'">';

            $response['selects'] = '<div class="article-selects-div">
            <select name="page_article_view_'.$id.'">
                <option value="0">Выберите оформление</option>
                <option value="1">Вариант 1</option>
                <option value="2">Вариант 2</option>
            </select>
            <select name="page_article_bg_'.$id.'">
                <option value="0">Выберите фон</option>
                <option value="1">Вариант 1</option>
                <option value="2">Вариант 2</option>
            </select>
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
        $i = 1;
        $fields = '';

        $this->db->select('article_text');
        $this->db->where('article_pid', $id);
        $this->db->where('article_pid_type', $type);
        $this->db->order_by('article_order', 'ASC');
        $query = $this->db->get('w_pages_articles');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $html = $this->p_get_html($i, $row->article_text);

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

        return $fields;
    }
}
<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для новостей
 */

class Cms_news extends CI_Model {

    function __construct()
    {
        parent::__construct();

        $this->config->load('cms_news');
        if (defined('LANGF')) $this->lang->load('cms_news', LANGF);
        $this->load->helper(array('html'));
    }

    // ------------------------------------------------------------------------

    /**
     * Находим страницу
     *
     * @access  public
     * @param   int
     * @return  int
     */
    function get_news_page($id)
    {
        // Урл страницы, к которой подключена лента
        $this->db->select('page_url');
        $this->db->from('w_includes');
        $this->db->join('w_pages', 'w_pages.page_id = w_includes.obj_id');
        $this->db->where('inc_id', 2);
        $this->db->where('inc_value', $id);
		$this->db->where('inc_type', 'pages');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            return $row->page_url;
        }
        else
        {
            return false;
        }
    }

    /**
     * Параметры категории
     *
     * @access  public
     * @param   int
     * @return  array
     */
    function get_cat_params($id)
    {
        $this->db->select('news_cat_name, news_cat_view_id');
        $this->db->from('w_news_categories');
        $this->db->where('news_cat_id', $id);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            return array('name' => $row->news_cat_name, 'view' => $row->news_cat_view_id);
        }
        else
        {
            return false;
        }
    }

    /**
     * Изображения
     *
     * @access  public
     * @param   int
     * @param   string
     * @return  array
     */
    function get_img($id, $name)
    {
        $thumbs = $this->config->item('cms_news_images');
        $images = array();

        foreach ($thumbs as $key => $value)
        {
            $path = FCPATH.substr($this->config->item('cms_news_dir'), 1).$id.$key.'.jpg';
            $url  = substr($this->config->item('cms_news_dir'), 1).$id.$key.'.jpg';

            if (is_file ($path))
            {
                $size   = getimagesize ($path);
                $width  = $size[0];
                $height = $size[1];

                $image_properties = array(
                    'src'       => $url,
                    'alt'       => $name,
                    'width'     => $width,
                    'height'    => $height,
                    'title'     => $name
                );

                $images[$key] = img($image_properties);
            }
        }

        return $images;
    }
}
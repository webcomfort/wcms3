<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для новостей
 */

class Cms_news extends CI_Model {

    function __construct()
    {
        parent::__construct();
	    $this->load->model('Cms_page');
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
        $this->db->select('page_id, page_url');
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
            return $this->Cms_page->get_url($row->page_id);
        }
        else
        {
            return false;
        }
    }

	/**
	 * Находим урлы страниц, к которым подключены ленты
	 *
	 * @access  public
	 * @return  array
	 */
	function get_news_pages()
	{
		$pages = array();

		// Урл страницы, к которой подключена лента
		$this->db->select('page_id, inc_value');
		$this->db->from('w_includes');
		$this->db->join('w_pages', 'w_pages.page_id = w_includes.obj_id');
		$this->db->where('inc_id', 2);
		$this->db->where('inc_type', 'pages');
		$this->db->where('page_status !=', 0);
		$this->db->where('page_status !=', 3);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$pages[$row->inc_value] = $this->Cms_page->get_url( $row->page_id );
			}
		}
		return $pages;
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
	 * Записи, которые нужно учитывать в тегах
	 *
	 * @access	public
	 * @param   int
	 * @return	array
	 */
	function get_tag_items ($cat_id){
		$this->db->select('w_news.news_id as id');
		$this->db->from('w_news_categories_cross');
		$this->db->join('w_news', 'w_news.news_id = w_news_categories_cross.news_id');
		$this->db->where('news_cat_id', $cat_id);
		$this->db->where_in('news_active', array(1));
		$this->db->where('news_lang_id', LANG);
		//----------------------------------------------------------------------------------
		$query = $this->db->get();

		$ids = array(0);
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$ids[] = $row->id;
			}
		}
		return $ids;
	}

    /**
     * Изображения
     *
     * @access  public
     * @param   int
     * @param   string
     * @return  array
     */
    function get_img($id, $name, $css='')
    {
        $thumbs = $this->config->item('cms_news_images');
        $images = array();
        $iid = ceil(intval($id)/1000);

        foreach ($thumbs as $key => $value)
        {
            $path = FCPATH.substr($this->config->item('cms_news_dir'), 1).$iid.'/'.$id.$key.'.jpg';
            $url  = substr($this->config->item('cms_news_dir'), 1).$iid.'/'.$id.$key.'.jpg';

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
                    'title'     => $name,
                    'class'     => $css
                );

                $images[$key] = img($image_properties);
            }
        }

        return $images;
    }
}
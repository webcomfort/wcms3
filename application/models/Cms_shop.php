<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для новостей
 */

class Cms_shop extends CI_Model {

    private $cat_direct_childs = array();
    private $cat_childs = array();
    private $crumbs = array();
    private $forest = array();

    function __construct()
    {
        parent::__construct();

        $this->load->helper(array('html'));
    }

    // ------------------------------------------------------------------------

    /**
     * Находим страницу
     *
     * @access  public
     * @return  string
     */
    function get_shop_page()
    {
        // Урл страницы, к которой подключен магазин
        $this->db->select('page_url');
        $this->db->from('w_includes');
        $this->db->join('w_pages', 'w_pages.page_id = w_includes.obj_id');
        $this->db->where('inc_id', 1);
        $this->db->where('inc_value', $this->config->item('cms_shop_mod'));
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

    // ------------------------------------------------------------------------

    /**
     * Находим страницу категории
     *
     * @access  public
     * @param   int
     * @return  string
     */
    function get_cat_page($id)
    {
        // Урл страницы, к которой подключена категория
        $this->db->select('cat_url');
        $this->db->from('w_shop_categories');
        $this->db->where('cat_id', $id);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            return $row->cat_url;
        }
        else
        {
            return false;
        }
    }

    /**
     * Находим верхнюю категорию
     *
     * @access  public
     * @return  string
     */
    function get_top_cat()
    {
        // Урл страницы, к которой подключена категория
        $this->db->select('cat_url');
        $this->db->from('w_shop_categories');
        $this->db->where('cat_pid', 0);
        $this->db->order_by('cat_sort', 'asc');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            return $row->cat_url;
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
    function get_cat_params($url)
    {
        $this->db->select('cat_id, cat_pid, cat_name, cat_url, cat_set_id, cat_active, cat_sort, cat_meta_title, cat_meta_keywords, cat_meta_description, cat_seo, cat_lang_id');
        $this->db->from('w_shop_categories');
        $this->db->where('cat_url', $url);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $data = $query->result_array();
            return $data[0];
        }
        else
        {
            return false;
        }
    }

    /**
     * id категории
     *
     * @access  public
     * @param   int
     * @return  array
     */
    function get_cat_id($url)
    {
        $this->db->select('cat_id');
        $this->db->from('w_shop_categories');
        $this->db->where('cat_url', $url);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            return $row->cat_id;
        }
        else
        {
            return false;
        }
    }

    /**
     * Отдаем цепочку дочерних категорий
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_categories_chain ($id)
    {
        $this->db->select('cat_id, cat_pid, cat_name, cat_url, cat_set_id, cat_active, cat_sort, cat_meta_title, cat_meta_keywords, cat_meta_description, cat_seo, cat_lang_id');
        $this->db->from('w_shop_categories');
        $this->db->where('cat_active =', 1);
        $this->db->where('cat_lang_id =', LANG);
        $this->db->order_by('cat_sort', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $forest =& $this->tree->get_tree('cat_id', 'cat_pid', $query->result_array(), $id);
            $this->_categories_processing($forest, $id);
            $childs_plus = array();
            $childs_plus[] = $id;
            $childs_plus = array_merge($childs_plus, $this->cat_childs);

            $data = array('childs' => $this->cat_childs, 'direct_childs' => $this->cat_direct_childs, 'childs_plus' => $childs_plus);

            return $data;
        }
    }

    function _categories_processing ($forest, $id)
    {
        foreach ($forest as $tree)
        {
            if ($tree['cat_pid'] == $id) $this->cat_direct_childs[] = array('id' => $tree['cat_id'], 'url' => $tree['cat_url'], 'name' => $tree['cat_name']);
            $this->cat_childs[] = $tree['cat_id'];

            if (isset($tree['nodes'])) $this->_categories_processing($tree['nodes'], $id);
        }
    }

    function get_cat_crumbs (){
        $this->db->select('cat_id, cat_pid, cat_name, cat_url');
        $this->db->from('w_shop_categories');
        $this->db->where('cat_active =', 1);
        $this->db->where('cat_lang_id =', LANG);
        $this->db->order_by('cat_sort', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0 && $this->uri->segment(2) && preg_ext_string ($this->uri->segment(2)))
        {
            $id = $this->get_cat_id($this->uri->segment(2));
            $this->forest =& $this->tree->get_tree('cat_id', 'cat_pid', $query->result_array(), 0);
            $this->_set_crumbs($this->forest, 'cat_id', 'cat_pid', 'cat_name', 'cat_url', $id, $this->get_shop_page());
            $this->crumbs = array_reverse($this->crumbs);
            return $this->crumbs;
        }
    }

    function _set_crumbs ($forest, $id_name, $parent_name, $level_name, $url_name, $active_id, $shop_url)
    {
        if (is_array($forest))
        {
            foreach ($forest as $tree)
            {
                if ($tree[$id_name] == $active_id)
                {
                    $this->crumbs[$tree[$id_name]]['id'] = $tree[$id_name];
                    $this->crumbs[$tree[$id_name]]['pid'] = $tree[$parent_name];
                    $this->crumbs[$tree[$id_name]]['name'] = $tree[$level_name];
                    $this->crumbs[$tree[$id_name]]['url'] = $shop_url.'/'.$tree[$url_name];

                    if ($tree[$parent_name] != 0) $this->_set_crumbs($this->forest, $id_name, $parent_name, $level_name, $url_name, $tree[$parent_name], $shop_url);
                }
                else
                {
                    if(isset($tree['nodes'])) $this->_set_crumbs($tree['nodes'], $id_name, $parent_name, $level_name, $url_name, $active_id, $shop_url);
                }
            }
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
        $thumbs = $this->config->item('cms_shop_images');
        $images = array();

        foreach ($thumbs as $key => $value)
        {
            $path = FCPATH.substr($this->config->item('cms_shop_dir'), 1).$id.$key.'.jpg';
            $url  = substr($this->config->item('cms_shop_dir'), 1).$id.$key.'.jpg';

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

                $images[$key]['img'] = img($image_properties);
                $images[$key]['url'] = $url;
            }
        }

        return $images;
    }
}
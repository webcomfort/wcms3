<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для страниц
 */

class Cms_page extends CI_Model {

	private $crumbs         = array();
	private $name           = '';
	private $title          = '';
    private $keywords       = '';
    private $description    = '';
    private $head           = '';
	private $link_title		= '';
	private $foot			= '';
	private $canonical  	= '';
    private $articles  	    = array();
	private $page_segment   = 1;
	private $base_url       = '';

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Получение настройки
     *
     * @access  public
     * @param   string
     * @return  string
     */
    function get_config($label)
    {
        $this->db->select('config_value');
        $this->db->where('config_label', $label);
        $this->db->limit(1);
        $query = $this->db->get('w_cms_configs');

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            return $row->config_value;
        }
        else
        {
            return '';
        }
    }

	/**
	 * Получение массива крошек
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	function set_crumbs($menu_id)
	{
		$this->db->select('page_id, page_pid, page_name, page_url, page_status, page_redirect');
		$this->db->from('w_pages');
		$this->db->where('page_menu_id =', $menu_id);
		$this->db->where('page_lang_id =', LANG);
		$this->db->where_in('page_status', array(1, 2, 3, 4));
		$this->db->order_by('page_sort', 'asc');
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			$forest = $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), 0);
			$this->tree->set_tree($forest);
			$this->tree->set_crumbs($forest, 'page_id', 'page_pid', 'page_name', 'page_url', '/', 'page_status', 3, PAGE_ID);
			$crumbs = $this->tree->get_crumbs();

			if(!empty($crumbs))
			{
				$this->crumbs = $crumbs;
			}
		}
	}

	/**
	 * Находим страницу по модулю
	 *
	 * @access  public
	 * @param   string
	 * @param   int
	 * @return  mixed
	 */
	function get_module_page($file, $lang)
	{
		$this->db->select('page_id, page_url');
		$this->db->from('w_includes');
		$this->db->join('w_pages', 'w_pages.page_id = w_includes.obj_id');
		$this->db->join('w_cms_modules', 'w_cms_modules.module_id = w_includes.inc_value');
		$this->db->where('inc_id', 1);
		$this->db->where('w_pages.page_lang_id', $lang);
		$this->db->where('w_cms_modules.module_file', $file);
		$this->db->where('inc_type', 'pages');
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->page_id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение полного урла страницы по id
	 *
	 * @access  public
	 * @param   int
	 * @param   mixed
	 * @return  mixed
	 */
	function get_url($id, $menu = false)
	{
		$this->load->library(array('tree'));

		$this->db->where('page_status !=', 0);
		($menu) ? $this->db->where('page_menu_id', $menu) : $this->db->where_in('page_menu_id', $this->config->item('cms_menu_indexing'));
		$query = $this->db->get('w_pages');

		if ($query->num_rows() > 0) {

			$forest = $this->tree->get_tree( 'page_id', 'page_pid', $query->result_array(), 0 );
			$this->tree->set_tree( $forest );

			$url = '';
			$this->tree->reset_crumbs();
			$this->tree->set_crumbs($forest, 'page_id', 'page_pid', 'page_name', 'page_url', '/', 'page_status', 33, $id);
			$crumbs = $this->tree->get_crumbs();
			foreach ($crumbs as $crumb){
				$url .= '/'.$crumb['page_url'];
			}
			return ($url != '') ? $url : false;
		} else {
			return false;
		}
	}

	/**
	 * Получение полного урла страницы по id при наличии готового forest
	 *
	 * @access  public
	 * @param   int
	 * @param   array
	 * @return  mixed
	 */
	function get_url_with_forest($id, $forest)
	{
		$this->load->library(array('tree'));
		$this->tree->set_tree( $forest );
		$url = '';
		$this->tree->reset_crumbs();
		$this->tree->set_crumbs($forest, 'page_id', 'page_pid', 'page_name', 'page_url', '/', 'page_status', 33, $id);
		$crumbs = $this->tree->get_crumbs();
		foreach ($crumbs as $crumb){
			$url .= '/'.$crumb['page_url'];
		}
		return ($url != '') ? $url : false;
	}

	/**
	 * Получаем номер сегмента урла для страницы
	 *
	 * @access	public
	 * @return	string
	 */
	function page_segment()
	{
		$segments = $this->uri->segment_array();
		$statuses = array(1, 2, 3);

		$this->db->select('page_id, page_pid, page_name, page_url');
		$this->db->from('w_pages');
		$this->db->where_in('page_status', $statuses);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$forest = $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), 0);
			$this->check_segment($forest, $segments);
			$this->base_url();
		}

		return $this->get_page_segment();
	}

	/**
	 * Проверка сегментов
	 *
	 * @access	private
	 * @param   array
	 * @param   array
	 * @return	string
	 */
	function check_segment($forest, $segments){
		foreach ($forest as $tree)
		{
			if(in_array($tree['page_url'], $segments)){
				$this->set_page_segment(array_search($tree['page_url'], $segments));
				if (isset($tree['nodes'])) $this->check_segment($tree['nodes'], $segments);
				return;
			}
		}
	}

	/**
	 * Базовый УРЛ страницы
	 *
	 * @access	private
	 * @return	string
	 */
	function base_url(){
		$segs = $this->uri->segment_array();
		for ($i = 1; $i <= $this->page_segment; $i++) {
			$this->add_base_url('/'.$segs[$i]);
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
	function get_img($id, $name, $thumbs, $dir, $css='')
	{
		$images = array();
		$iid = ceil(intval($id)/1000);
		$folder = $dir.$iid.'/';
		$folder_path = FCPATH.substr($folder, 1);

		// Src
		if ($handle = opendir($folder_path))
		{
			while (($file = readdir($handle)) !== false)
			{
				if (preg_match ("/^".$id."_src\.([[:alnum:]])*$/", $file))
				{
					$url  = $folder.$file;
					$size = getimagesize($folder_path.$file);
					$width  = $size[0];
					$height = $size[1];

					$image_properties = array(
						'src'       => $url,
						'alt'       => $name,
						'class'     => $css,
						'width'     => $width,
						'height'    => $height,
						'title'     => $name,
						'rel'       => ''
					);

					$images['_src']['url']  = $url;
					$images['_src']['img']  = img($image_properties, FALSE);
					$images['_src']['name'] = $name;
				}
			}
		}

		// Main image
		if ($this->config->item('cms_webp')){
			$path = $folder_path.$id.'_webp.webp';
			$url  = $folder.$id.'_webp.webp';
		} else {
			$path = $folder_path.$id.'.jpg';
			$url  = $folder.$id.'.jpg';
		}
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

			$images['_main']['url']  = $url;
			$images['_main']['img']  = img($image_properties, FALSE);
			$images['_main']['name'] = $name;
		}

		// Thumbs
		foreach ($thumbs as $key => $value)
		{
			if ($this->config->item('cms_webp')){
				$path = $folder_path.$id.$key.'.webp';
				$url  = $folder.$id.$key.'.webp';
			} else {
				$path = $folder_path.$id.$key.'.jpg';
				$url  = $folder.$id.$key.'.jpg';
			}

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

				$images[$key]['url']  = $url;
				$images[$key]['img']  = img($image_properties, FALSE);
				$images[$key]['name'] = $name;
			}
		}

		return $images;
	}

    // ------------------------------------------------------------------------

    /**
     * Присваиваем значения переменным
     *
     * @access  public
     * @param   string
     * @return  void
     */
	function set_name($value)           { $this->name           = $value; }
    function set_title($value)          { $this->title          = $value; }
	function set_link_title($value)     { $this->link_title     = $value; }
    function set_keywords($value)       { $this->keywords       = $value; }
    function set_description($value)    { $this->description    = $value; }
    function set_head($value)           { $this->head           = $value; }
	function set_foot($value)           { $this->foot           = $value; }
	function set_canonical($value)      { $this->canonical      = $value; }
    function set_articles($value)       { $this->articles       = $value; }
	function set_page_segment($value)   { $this->page_segment   = $value; }
	function set_base_url($value)       { $this->base_url       = $value; }
	
	// ------------------------------------------------------------------------

    /**
     * Добавляем значения переменным
     *
     * @access  public
     * @param   string
     * @return  void
     */
	function add_crumbs($value)         { $this->crumbs[]       = $value; }
	function add_name($value)           { $this->name          .= $value; }
    function add_title($value)          { $this->title         .= $value; }
	function add_link_title($value)     { $this->link_title    .= $value; }
    function add_keywords($value)       { $this->keywords      .= $value; }
    function add_description($value)    { $this->description   .= $value; }
    function add_head($value)           { $this->head          .= $value; }
	function add_foot($value)           { $this->foot          .= $value; }
	function add_canonical($value)      { $this->canonical     .= $value; }
    function add_articles($value)       { $this->articles      .= $value; }
	function add_base_url($value)       { $this->base_url      .= $value; }

    // ------------------------------------------------------------------------

    /**
     * Отдаем значения переменных
     *
     * @access  public
     * @return  string
     */
	function get_crumbs()         { return $this->crumbs; }
	function get_name()           { return $this->name; }
    function get_title()          { return $this->title; }
	function get_link_title()     { return $this->link_title; }
    function get_keywords()       { return $this->keywords; }
    function get_description()    { return $this->description; }
    function get_head()           { return $this->head; }
	function get_foot()           { return $this->foot; }
	function get_canonical()      { return ($this->canonical != '')?'<link href="//'.$_SERVER['HTTP_HOST'].'/'.$this->canonical.'" rel="canonical" />':''; }
    function get_articles()       { return $this->articles; }
	function get_page_segment()   { return $this->page_segment; }
	function get_base_url()       { return $this->base_url; }
}

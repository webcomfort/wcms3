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
	function get_canonical()      { return $this->canonical; }
    function get_articles()       { return $this->articles; }
}

<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для страниц
 */

class Cms_page extends CI_Model {

    private $title          = '';
    private $keywords       = '';
    private $description    = '';
    private $head           = '';
	private $link_title		= '';
	private $foot			= '';
	private $canonical  	= '';

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Тексты
     *
     * @access  public
     * @param   int
     * @return  array
     */
    function get_articles($id)
    {
        $data = array();

        $this->db->select('article_id, article_text');
        $this->db->where('article_page_id', $id);
        $this->db->order_by('article_id', 'asc');
        $query = $this->db->get('w_pages_articles');

        if ($query->num_rows() > 0)
        {
            $this->load->library('parser');

            foreach ($query->result() as $row)
            {
                $text = $this->parser->parse_modules($row->article_text);
                $data['page_article_'.$row->article_id] = $text;
            }

            return $data;
        }
        else
        {
            return $data;
        }
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

    // ------------------------------------------------------------------------

    /**
     * Присваиваем значения переменным
     *
     * @access  public
     * @param   string
     * @return  void
     */
    function set_title($value)          { $this->title          = $value; }
	function set_link_title($value)     { $this->link_title     = $value; }
    function set_keywords($value)       { $this->keywords       = $value; }
    function set_description($value)    { $this->description    = $value; }
    function set_head($value)           { $this->head           = $value; }
	function set_foot($value)           { $this->foot           = $value; }
	function set_canonical($value)      { $this->canonical      = $value; }
	
	// ------------------------------------------------------------------------

    /**
     * Добавляем значения переменным
     *
     * @access  public
     * @param   string
     * @return  void
     */
    function add_title($value)          { $this->title         .= $value; }
	function add_link_title($value)     { $this->link_title    .= $value; }
    function add_keywords($value)       { $this->keywords      .= $value; }
    function add_description($value)    { $this->description   .= $value; }
    function add_head($value)           { $this->head          .= $value; }
	function add_foot($value)           { $this->foot          .= $value; }
	function add_canonical($value)      { $this->canonical     .= $value; }

    // ------------------------------------------------------------------------

    /**
     * Отдаем значения переменных
     *
     * @access  public
     * @return  string
     */
    function get_title()          { return $this->title; }
	function get_link_title()     { return $this->link_title; }
    function get_keywords()       { return $this->keywords; }
    function get_description()    { return $this->description; }
    function get_head()           { return $this->head; }
	function get_foot()           { return $this->foot; }
	function get_canonical()      { return $this->canonical; }
}
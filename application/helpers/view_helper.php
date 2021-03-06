<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Загрузка модуля в шаблоне
*
* @access	public
* @param    string
* @param    array
* @return	string
*/
if ( ! function_exists('module'))
{
	function module($model, $params = array())
	{
		$CI =& get_instance();

		if(is_file(APPPATH.'models/'.ucfirst($model).'.php'))
        {
            $CI->load->model($model);
            return $CI->$model->get_output($params);
        }
	}
}

/**
* Загрузка шаблона в шаблоне
*
* @access   public
* @param    string
* @param    array
* @param    string
* @param    string
* @return   string
*/
if ( ! function_exists('view'))
{
    function view($view, $params = array(), $lang = false, $place = 'site/')
    {
        if(is_file(APPPATH.'views/'.$place.$view.'.php'))
        {
            $CI =& get_instance();
            if($lang) $CI->lang->load($lang, LANGF);
            return $CI->load->view($place.$view, $params, true);
        }
    }
}

/**
 * Загрузка параметра конфигурации в шаблоне
 *
 * @access   public
 * @param    string
 * @return   string
 */
if ( ! function_exists('conf'))
{
    function conf($label)
    {
        $CI =& get_instance();
        $CI->load->model('cms_page');
        return $CI->cms_page->get_config($label);
    }
}

/**
 * Загрузка параметра конфигурации в шаблоне
 *
 * @access   public
 * @param    string
 * @return   string
 */
if ( ! function_exists('file_conf'))
{
    function file_conf($name)
    {
        $CI =& get_instance();
        return $CI->config->item($name);
    }
}
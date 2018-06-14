<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода тегов
 */

class Mod_tags_list extends CI_Model {

	function __construct()
    {
        parent::__construct();
	    $this->load->model('Cms_shop');
	    $this->load->model('Cms_tags');
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array()) {

    	$label  = false;
    	$incs   = $this->Cms_inclusions->get_active_inclusions();
    	$conf   = $this->config->item('cms_site_inclusions');

    	foreach ($incs as $key => $value){
    		if(is_array($conf[$key]['tags'])){
			    $label   = $conf[$key]['label'];
			    $ids = $this->{$conf[$key]['tags']['model']}->{$conf[$key]['tags']['method']}($value);
		    }
	    }

	    if($label){

	    	$tags = $this->Cms_tags->get_tags_by_group($ids, $label);

	    	if(count($tags) > 0) {
			    $data = array(
				    'tags'       => $tags,
				    'tags_limit' => 2,
				    'page'       => '/'.$this->uri->segment(1)
			    );

			    return $this->load->view( 'site/tags_list', $data, true );
		    } else {
	    		return '';
		    }
	    }
    }
}
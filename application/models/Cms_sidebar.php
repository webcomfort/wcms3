<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Утилиты для сайдбара
 */

class Cms_sidebar extends CI_Model {

    private $widgets;

	function __construct()
    {
        parent::__construct();
		$this->widgets = $this->config->item('cms_widgets');
    }

	// ------------------------------------------------------------------------

	/**
	 * Вызываем модуль
	 *
	 * @access  public
	 * @param   int
	 * @return  int
	 */
	function get_output($params = array())
	{
		$id = (isset($params[0])) ? $params[0] : false;

		$this->db->select('*');
		$this->db->from('w_sidebar_widgets');
		$this->db->join('w_cms_modules', 'w_sidebar_widgets.widget_type = w_cms_modules.module_id', 'left');
		$this->db->where('sidebar_id', $id);
		$this->db->order_by('widget_sort', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$sidebar = '';
			foreach ($query->result() as $row) {
				$model = basename($row->module_file, '.php');
				$this->load->model($model);
				$sidebar .= $this->$model->get_output(array($row->widget_param_1, $row->widget_param_2, $row->widget_param_3, $row->widget_param_4, $row->widget_param_5));
			}
			return $sidebar;
		}
		else
		{
			return '';
		}
	}

    // ------------------------------------------------------------------------

	/**
	 * Массив полей для администраторского интерфейса
	 *
	 * @access  public
	 * @param   string
	 * @return  array
	 */

	function get_fields($id)
	{
		if($id){
			$this->db->select('*');
			$this->db->from('w_sidebar_widgets');
			$this->db->join('w_cms_modules', 'w_sidebar_widgets.widget_type = w_cms_modules.module_id', 'left');
			$this->db->where('widget_id', $id);
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row = $query->row();
				$opts = array();
				$fields = $this->widgets[$row->module_file];

				foreach ($fields as $key => $value){
					$opts['fdd'][$key] = $value;
				}
				return $opts;
			} else {
				return array();
			}
		} else {
			return array();
		}





		$opts       = array();
		$inclusions = $this->config->item('cms_site_inclusions');
		$i = 0;

		foreach ($inclusions as $key => $value)
		{
			if(in_array($type, $value['where']))
			{
				$opts['fdd'][$type.'_inc_'.$key]['name']      = $value['name'];
				$opts['fdd'][$type.'_inc_'.$key]['nodb']      = true;
				$opts['fdd'][$type.'_inc_'.$key]['options']   = 'ACPDV';
				$opts['fdd'][$type.'_inc_'.$key]['select']    = 'D';
				$opts['fdd'][$type.'_inc_'.$key]['values2'][0]= 'Не выбрано';
				$opts['fdd'][$type.'_inc_'.$key]['required']  = true;
				$opts['fdd'][$type.'_inc_'.$key]['sort']      = true;
				$opts['fdd'][$type.'_inc_'.$key]['help']      = $value['help'];
				$opts['fdd'][$type.'_inc_'.$key]['addcss']    = 'select2';

				if (isset($value['add_code'])) $opts['fdd'][$type.'_inc_'.$key]['add_code']  = $value['add_code'];
				if (isset($value['modal_code'])) {
					$data = array();
					$data['inc_id'] = $key;
					$data['ajax_select'] = $type.'_inc_'.$key;
					if(isset($value['adm_model'])){
						$this->load->model('cms_utils');
						if($page = $this->cms_utils->get_admin_page_id($value['adm_model'])){
							$data['inc_admin_page'] = $page;
						}
					}
					$opts['fdd'][$type.'_inc_'.$key]['modal_code']  = $this->load->view($value['modal_code'], $data, true);
				}

				if (!$i) $opts['fdd'][$type.'_inc_'.$key]['tab'] = 'Подключения';

				($value['filter'] != '') ? eval($value['filter']) : $filters = '';

				$this->db->select($value['key'].', '.$value['description']);
				if ($filters != '') $this->db->where($filters);
				$this->db->order_by($value['orderby']);
				$query = $this->db->get($value['table']);

				if ($query->num_rows() > 0)
				{
					foreach ($query->result() as $row)
					{
						$opts['fdd'][$type.'_inc_'.$key]['values2'][$row->{$value['key']}] = $row->{$value['description']};
					}
				}

				$rec = ($this->input->get('PME_sys_rec', TRUE)) ? $this->input->get('PME_sys_rec', TRUE) : $this->input->post('PME_sys_rec', TRUE);

				if($rec)
				{
					$this->db->select('inc_value');
					$this->db->where('obj_id', $rec);
					$this->db->where('inc_id', $key);
					$this->db->where('inc_type', $type);
					$query = $this->db->get('w_includes');

					if ($query->num_rows() > 0)
					{
						$row = $query->row();
						$opts['fdd'][$type.'_inc_'.$key]['fdefault'] = @$row->inc_value;
					}
					else
					{
						$opts['fdd'][$type.'_inc_'.$key]['default'] = 0;
					}
				}
				else
				{
					$opts['fdd'][$type.'_inc_'.$key]['default'] = 0;
				}
			}
			$i++;
		}

		return $opts;
	}
}

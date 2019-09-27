<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для включений
 */

class Cms_inclusions extends CI_Model {

    private $active_inclusions = array();

	function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Включения
     *
     * @access  public
     * @param   int
     * @param   string
     * @return  array
     */
    function get_inclusions($id, $type)
    {
        $data = array();
        $inclusions = $this->config->item('cms_site_inclusions');

        $this->db->select('inc_id, inc_value');
        $this->db->where('obj_id', $id);
        $this->db->where('inc_type', $type);
        $query = $this->db->get('w_includes');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                if($row->inc_value)
                {
                    $model = $inclusions[$row->inc_id]['file'];
                    $this->load->model($model);
                    $data['inc_module_'.$row->inc_id] = $this->$model->get_output(array($row->inc_value));
                    $this->active_inclusions[$row->inc_id] = $row->inc_value;
                }
                else
                {
                    $data['inc_module_'.$row->inc_id] = '';
                }
            }
        }

        return $data;
    }

	// ------------------------------------------------------------------------

	/**
	 * Активные включения
	 *
	 * @access  public
	 * @return  array
	 */
	function get_active_inclusions()
	{
		return $this->active_inclusions;
	}

    // ------------------------------------------------------------------------

    /**
     * Массив подключений для администраторского интерфейса
     *
     * @access  public
     * @param   string
     * @return  array
     */

	function get_admin_inclusions($type)
	{
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

    // ------------------------------------------------------------------------

    /**
     * Добавляем или изменяем данные в таблице подключений (для триггеров)
     *
     * @access  public
     * @param   int
     * @param   string
     * @return  void
     */

    function admin_inclusions_insert_update($id, $type)
    {
        foreach ($this->input->post(NULL, TRUE) as $key => $value)
        {
            unset($data);

            if (preg_match("/^PME_data_".$type."_inc_([0-9])*$/", $key, $matches))
            {
                $this->db->select('i_id');
                $this->db->where('obj_id', $id);
                $this->db->where('inc_id', $matches[1]);
                $this->db->where('inc_type', $type);
                $query = $this->db->get('w_includes');

                if ($query->num_rows() > 0)
                {
                    $row = $query->row();

                    $data = array( 'inc_value' => $value );
                    $this->db->where('i_id', $row->i_id);
                    $this->db->update('w_includes', $data);
                }
                else
                {
                    $data = array(
                        'i_id'      => '',
                        'obj_id'    => $id,
                        'inc_id'    => $matches[1],
                        'inc_value' => $this->input->post($key, TRUE),
                        'inc_type'  => $type
                    );

                    $this->db->insert('w_includes', $data);
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Удаляем данные в таблице подключений (для триггеров)
     *
     * @access  public
     * @param   int
     * @param   string
     * @return  void
     */

    function admin_inclusions_delete($id, $type, $last_basket_element = false)
    {
        $query = $this->db->get_where('w_includes', array('obj_id' => $id,'inc_type' => $type));
        if($last_basket_element === false) $last_basket_element = $this->trigger->get_last_basket_element();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $this->trigger->delete_relative($row->i_id, $last_basket_element, 'w_includes', 'i_id', 'Подключение', '');
            }
        }
    }
}

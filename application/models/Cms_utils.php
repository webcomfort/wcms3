<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Утилиты административного раздела
 */

class Cms_utils extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
	 * Поиск номера страницы с подключенным модулем
	 *
	 * @access	public
     * @param   string
	 * @return	int
	 */
	function get_admin_page_id($model)
	{
        $this->db->select('p.cms_page_id AS id');
        $this->db->from('w_cms_modules AS m');
        $this->db->join('w_cms_pages AS p', 'm.module_id = p.cms_page_model_id', 'left');
        $this->db->where('m.module_file', trim($model).'.php');
        $query = $this->db->get();
        if ($query->num_rows() > 0){
            $row = $query->row();
            return $row->id;
        } else {
            return 0;
        }
	}

    // ------------------------------------------------------------------------

    /**
     * Извлекаем максимальное значение сортировки
     *
     * @access  private
     * @param   string
     * @param   string
     * @return  int
     */
    function get_max_sort($field, $table, $where=false) {
        $this->db->select_max($field, 'sort');
        if($where && is_array($where)) {
            if(array_key_exists('field', $where)) {
                $this->db->where($where['field'], $where['value']);
            }
            if(array_key_exists(0, $where)) {
                foreach ($where AS $value) {
                    $this->db->where($value['field'], $value['value']);
                }
            }
        }
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->sort+10;
        } else {
            return 10;
        }
    }

	// ------------------------------------------------------------------------

	/**
	 * Изменяем поле updated_at
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @param   int
	 * @return  void
	 */
	function update_updated($table, $id_name, $id) {
		$data = array(
			'updated_at' => date('Y-m-d G:i:s')
		);
		$this->db->where($id_name, $id);
		$this->db->update($table, $data);
	}
}
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Функции для триггеров
 */

class Trigger {

	private $CI;

	function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('search');
    }

    // ------------------------------------------------------------------------

    /**
     * Удаляем дочерние элементы и помещаем их в корзину
     *
     * @access	public
     * @param	array
     * @param	int
     * @param	string
     * @param	string
     * @param	string
     * @param	string
     * @return	void
     */
    function delete_child ($forest, $pid, $table, $id_name, $title, $title_value, $additional = array(), $child_indexing = false, $child_inc = false)
    {
        foreach ($forest as $tree)
        {
            $this->delete_relative($tree[$id_name], $pid, $table, $id_name, $title, $title_value);

            // Очищаем поисковый индекс, без возможности восстановления
            if(is_array($child_indexing)){
                $url = $tree[$child_indexing['url']];
                $this->CI->search->index_delete_by_url($url);
            }

            // Подключения
            if($child_inc) {
                $this->CI->Cms_inclusions->admin_inclusions_delete($tree[$id_name], $child_inc, $pid);
            }

            if(count($additional) > 0){

                // Если это один массив
                if(array_key_exists('table_pid', $additional)) {
                    $table_where = array($additional['table_pid'] => $tree[$id_name]);
                    $table_where = array_merge($table_where, $additional['table_where']);
                    $query = $this->CI->db->get_where($additional['table'], $table_where);

                    if ($query->num_rows() > 0) {
                        foreach ($query->result() as $row) {
                            $a = $additional['table_key'];
                            $this->delete_relative($row->$a, $pid, $additional['table'], $additional['table_key'], $additional['title'], '');
                        }
                    }
                }

                // Если это набор массивов
                if(array_key_exists(0, $additional)) {
                    foreach ($additional AS $value) {
                        $table_where = array($value['table_pid'] => $tree[$id_name]);
                        $table_where = array_merge($table_where, $value['table_where']);
                        $query = $this->CI->db->get_where($value['table'], $table_where);

                        if ($query->num_rows() > 0) {
                            foreach ($query->result() as $row) {
                                $a = $value['table_key'];
                                $this->delete_relative($row->$a, $pid, $value['table'], $value['table_key'], $value['title'], '');
                            }
                        }
                    }
                }

            }

            if (isset($tree['nodes'])) {
                $this->delete_child($tree['nodes'], $pid, $table, $id_name, $title, $title_value, $additional, $child_indexing);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Удаляем зависимые элементы и помещаем их в корзину
     *
     * @access	public
     * @param	int
     * @param	int
     * @param	string
     * @param	string
     * @param	string
     * @param	string
     * @return	void
     */
    function delete_relative ($id, $pid, $table, $id_name, $title, $title_value, $dir=false)
    {
        $query = $this->CI->db->get_where($table, array($id_name => $id));
        $row = $query->row_array();
        $title_value = ($title_value) ? ' "'.$row[$title_value].'"' : '';
        $files = ($dir) ? serialize(array(array('url' => $dir,'multiple' => false))) : '';

        $data = array(
            'id'			=> '',
            'pid'			=> $pid,
            'description'	=> $title.$title_value,
            'updated'		=> date('Y-m-d H:i:s'),
            'user'			=> $this->CI->cms_user->get_user_id(),
            'host'			=> $this->CI->input->ip_address(),
            'operation'		=> 'delete',
            'tab'			=> $table,
            'rowkey'		=> $id,
            'col'			=> '',
            'files'		    => $files,
            'oldval'		=> serialize($row),
            'newval'		=> ''
        );

        $this->CI->db->insert('w_changelog', $data);

        if($dir)
        {
            $lid = $this->CI->db->insert_id();
            files_delete ($dir, $id, $lid);
        }

        $this->CI->db->delete($table, array($id_name => $id));
    }

    // ------------------------------------------------------------------------

    /**
     * Изменяем зависимые элементы и помещаем их в корзину
     *
     * @access  public
     * @param   int
     * @param   int
     * @param   string
     * @param   string
     * @param   string
     * @param   string
     * @param   string
     * @return  void
     */
    function change_relative ($id, $pid, $table, $id_name, $col_name, $title, $title_value)
    {
        $query = $this->CI->db->get_where($table, array($id_name => $id));
        $row = $query->row_array();
        $title_value = ($title_value) ? ' "'.$row[$title_value].'"' : '';

        $data = array(
            'id'            => '',
            'pid'           => $pid,
            'description'   => $title.$title_value,
            'updated'       => date('Y-m-d H:i:s'),
            'user'          => $this->CI->cms_user->get_user_id(),
            'host'          => $this->CI->input->ip_address(),
            'operation'     => 'update',
            'tab'           => $table,
            'rowkey'        => $id,
            'col'           => $col_name,
            'files'         => '',
            'oldval'        => $row[$col_name],
            'newval'        => ''
        );

        $this->CI->db->insert('w_changelog', $data);
    }

    // ------------------------------------------------------------------------

	/**
	 * Получаем id последнего элемента в корзине
	 *
	 * @access	public
	 * @return	int
	 */
	function get_last_basket_element ()
    {
        $this->CI->db->select('MAX(id) AS id')->from('w_changelog');
        $query  = $this->CI->db->get();
        $row = $query->row();
        return $row->id;
    }
}
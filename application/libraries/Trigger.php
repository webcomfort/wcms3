<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Функции для триггеров
 */

class Trigger {

	private $CI;

	function __construct()
    {
        $this->CI =& get_instance();
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
	function delete_child ($forest, $pid, $table, $id_name, $title, $title_value)
    {
        foreach ($forest as $tree)
        {
            $this->delete_relative($tree[$id_name], $pid, $table, $id_name, $title, $title_value);

            if (isset($tree['nodes'])) {
                $this->delete_child($tree['nodes'], $pid, $table, $id_name, $title, $title_value);
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
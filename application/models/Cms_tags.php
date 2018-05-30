<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Функции для работы с тегами
 */

class Cms_tags extends CI_Model {

    function __construct()
    {
        parent::__construct();
	    $this->load->library('trigger');
	    $this->load->model('Cms_myedit');
    }

    // ------------------------------------------------------------------------

    /**
	 * Удаление пересечений с тегом
	 *
	 * @access	public
     * @param   int
     * @param   int
	 * @return	void
	 */
	function delete_tags_cross($id, $lbe)
	{
		$this->db->where('tag_id', $id);
		$query = $this->db->get('w_tags_cross');

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$this->trigger->delete_relative($row->tc_id, $lbe, 'w_tags_cross', 'tc_id', 'Пересечение с тегом', '');
			}
		}
	}

	// ----------------------------------- ПОЛЕ ДЛЯ ТЕГОВ В АДМИНЕ -------------------------------------------

	/**
	 * Функция, список выбора для пересечений с тегами
	 *
	 * @access	public
	 * @return	array
	 */

	function get_admin_opts($key, $type)
	{
		$this->mass_save($type);
		$opts = array();
		// Компануем и выводим все это ячейку таблицы списка
		$opts['fdd']['tags'] = array(
			'name'          => 'Теги',
			'nodb'          => true,
			'select'        => 'M',
			'options'       => 'ACPL',
			'add_display'   => $this->get_tags_select($key, '', $type),
			'change_display'=> $this->get_tags_select($key, '', $type),
			'cell_func' => array(
				'model' => 'cms_tags',
				'func'  => 'get_tags_select',
				'params'=> $type
			),
			'required'      => false,
			'sort'          => false,
			'help'          => 'Выберите из списка теги.'
		);
		return $opts;
	}

	/**
	 * Функция, список выбора для пересечений с тегами
	 *
	 * @access	public
	 * @return	string
	 */

	function get_tags_select($key, $value, $type)
	{
		// Получаем массивы с данными для формирования селекта
		$select_array = $this->_tags_cross($key, $type);

		// Строим селект
		$this->load->helper('form');
		$fopts = 'class="w300 js-data-ajax-tags-'.$key.'"';
		$form = form_multiselect('tags_'.$key.'[]', $select_array['values'], $select_array['defaults'], $fopts);

		// Получаем js-код для этого поля
		$script = $this->Cms_myedit->get_ajax($key, '/cms_tags/p_tags_generate', 'tags', 1, true);

		return $form.$script;
	}

	/**
	 * Функция, генерирующая список событий для <select> (внешний вызов)
	 *
	 * @access  public
	 * @return  string
	 */
	function p_tags_generate()
	{
		$rights = $this->cms_user->get_user_rights();
		if ( is_array($rights) && ($rights['Adm_tags.php']['edit'] || $rights['Adm_tags.php']['copy'] || $rights['Adm_tags.php']['add']) )
		{
			echo $this->Cms_myedit->get_ajax_query ('tag_id', 'tag_name', 'w_tags', 1);
		}
	}

	/**
	 * Функция, отдающая массивы пересечений ТЕГОВ для формирования селекта с дефолтными значениями
	 *
	 * @access	private
	 * @param   int - id для выборки
	 * @param   string - тип для выборки
	 * @return	array
	 */
	function _tags_cross($key, $type)
	{
		$val_arr = array();
		$val_arr_active = array();

		$this->db->select('tc_id, w_tags_cross.tag_id, item_id, tag_name')
		         ->from('w_tags_cross')
		         ->join('w_tags', 'w_tags.tag_id = w_tags_cross.tag_id')
		         ->where('item_id', $key)
		         ->where('item_type', $type);

		$query  = $this->db->get();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$val_arr[$row->tag_id] = $row->tag_name;
				$val_arr_active[] = $row->tag_id;
			}

			$data = array(
				'values'    => $val_arr,
				'defaults'  => $val_arr_active,
				'total'     => $query->num_rows()
			);

			return $data;
		}
		else {
			$data = array(
				'values'    => array(),
				'defaults'  => array(),
				'total'     => 0
			);
			return $data;
		}
	}

	/**
	 * Массовое сохранение параметров в таблице пересечений
	 *
	 * @access	public
	 * @return	void
	 */
	function mass_save($type)
	{
		if ($this->input->post() && is_array($this->input->post()) && $this->input->post('PME_sys_savelist')) {
			foreach ($this->input->post() as $k => $v) {

				// цифровой рейтинг
				if (preg_match_all('/^tags_([0-9]*)$/', $k, $matches)) {

					// Удаляем старые записи
					$this->db->where('item_id', $matches[1][0]);
					$this->db->where('item_type', $type);
					$this->db->delete('w_tags_cross');

					// Вносим новые записи
					if (is_array($this->input->post($matches[0][0], TRUE))) {
						foreach ($this->input->post($matches[0][0], TRUE) as $value) {

							$value = trim($value);

							if(!preg_int($value)){
								$query_check = $this->db->query('SELECT tag_id AS id FROM w_tags WHERE tag_name = \''.$value.'\' AND tag_lang_id = \''.$this->session->userdata('w_alang').'\' LIMIT 1');
								if ($query_check->num_rows() > 0) {
									$row_check = $query_check->row();
									$value = $row_check->id;
								} else {
									$data = array(
										'tag_id'    => '',
										'tag_name' => $value,
										'tag_lang_id' => $this->session->userdata('w_alang')
									);
									$this->db->insert('w_tags', $data);
									$value = $this->db->insert_id();
								}
							}

							$data = array(
								'tc_id' => '',
								'tag_id' => $value,
								'item_id' => $matches[1][0],
								'item_type' => $type
							);

							$this->db->insert('w_tags_cross', $data);
						}
					}

				}

			}
		}
	}

    // ----------------------------------- ТРИГГЕРЫ -----------------------------------

	/**
	 * Добавляем или изменяем данные в таблице пересечений (для триггеров)
	 *
	 * @access  public
	 * @param   int
	 * @param   string
	 * @return  void
	 */

	function admin_tags_insert_update($id, $type)
	{
		// Удаляем старые записи
		$this->db->where('item_id', $id);
		$this->db->where('item_type', $type);
		$this->db->delete('w_tags_cross');

		foreach ($this->input->post(NULL, TRUE) as $key => $post_data)
		{
			unset($data);

			if (preg_match_all('/^tags_([0-9]*)$/', $key, $matches))
			{
				// Вносим новые записи
				if (is_array($this->input->post($matches[0][0], TRUE))) {
					foreach ($this->input->post($matches[0][0], TRUE) as $value) {

						$value = trim($value);

						if(!preg_int($value)){
							$query_check = $this->db->query('SELECT tag_id AS id FROM w_tags WHERE tag_name = \''.$value.'\' AND tag_lang_id = \''.$this->session->userdata('w_alang').'\' LIMIT 1');
							if ($query_check->num_rows() > 0) {
								$row_check = $query_check->row();
								$value = $row_check->id;
							} else {
								$data = array(
									'tag_id'    => '',
									'tag_name' => $value,
									'tag_lang_id' => $this->session->userdata('w_alang')
								);
								$this->db->insert('w_tags', $data);
								$value = $this->db->insert_id();
							}
						}

						$data = array(
							'tc_id' => '',
							'tag_id' => $value,
							'item_id' => $id,
							'item_type' => $type
						);

						$this->db->insert('w_tags_cross', $data);
					}
				}
			}
		}
	}

	/**
	 * Удаляем данные в таблице пересечений (для триггеров)
	 *
	 * @access  public
	 * @param   int
	 * @param   string
	 * @return  void
	 */

	function admin_tags_delete($id, $type, $last_basket_element = false)
	{
		$query = $this->db->get_where('w_tags_cross', array('item_id' => $id,'item_type' => $type));
		if($last_basket_element === false) $last_basket_element = $this->trigger->get_last_basket_element();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$this->trigger->delete_relative($row->tc_id, $last_basket_element, 'w_tags_cross', 'tc_id', 'Пересечение с тегом', '');
			}
		}
	}

	// ----------------------------------- ВЫБОРКА -----------------------------------

	/**
	 * Возвращаем все теги элемента
	 *
	 * @access  public
	 * @param   int
	 * @param   string
	 * @return  array
	 */

	function get_tags_by_item($id, $type){

		$tags = array();

		$this->db->select('w_tags.tag_id, w_tags.tag_name, w_tags_cross.item_id');
		$this->db->from('w_tags_cross');
		$this->db->join('w_tags', 'w_tags.tag_id = w_tags_cross.tag_id', 'left');
		$this->db->where('w_tags_cross.item_id', $id);
		$this->db->where('w_tags_cross.item_type', $type);
		$this->db->where('w_tags.tag_lang_id', (defined('LANG') ? LANG : $this->session->userdata('w_alang')));
		$this->db->order_by("w_tags.tag_name", "asc");
		$query_tags = $this->db->get();

		if ($query_tags->num_rows() > 0) {
			foreach ( $query_tags->result() as $row_tags ) {
				$tags[$row_tags->tag_id] = $row_tags->tag_name;
			}
		}

		return $tags;
	}

	/**
	 * Возвращаем все теги группы элементов
	 *
	 * @access  public
	 * @param   array
	 * @param   string
	 * @return  array
	 */

	function get_tags_by_group($ids, $type){

		$tags = array();

		$this->db->select('w_tags.tag_id, w_tags.tag_name, w_tags_cross.item_id');
		$this->db->from('w_tags_cross');
		$this->db->join('w_tags', 'w_tags.tag_id = w_tags_cross.tag_id', 'left');
		$this->db->where_in('w_tags_cross.item_id', $ids);
		$this->db->where('w_tags_cross.item_type', $type);
		$this->db->where('w_tags.tag_lang_id', (defined('LANG') ? LANG : $this->session->userdata('w_alang')));
		$this->db->order_by("w_tags.tag_name", "asc");
		$query_tags = $this->db->get();

		if ($query_tags->num_rows() > 0) {
			foreach ( $query_tags->result() as $row_tags ) {
				$tags[$row_tags->tag_id]['name'] = $row_tags->tag_name;
				$tags[$row_tags->tag_id]['count'] = (isset($tags[$row_tags->tag_id]['count'])) ? $tags[$row_tags->tag_id]['count']+1 : 1;
			}
		}

		return $tags;
	}

	/**
	 * Возвращаем все теги типа
	 *
	 * @access  public
	 * @param   string
	 * @return  array
	 */

	function get_tags_by_type($type){

		$tags = array();

		$this->db->select('w_tags.tag_id, w_tags.tag_name, w_tags_cross.item_id');
		$this->db->from('w_tags_cross');
		$this->db->join('w_tags', 'w_tags.tag_id = w_tags_cross.tag_id', 'left');
		$this->db->where('w_tags_cross.item_type', $type);
		$this->db->where('w_tags.tag_lang_id', (defined('LANG') ? LANG : $this->session->userdata('w_alang')));
		$this->db->order_by("w_tags.tag_name", "asc");
		$query_tags = $this->db->get();

		if ($query_tags->num_rows() > 0) {
			foreach ( $query_tags->result() as $row_tags ) {
				$tags[$row_tags->tag_id]['name'] = $row_tags->tag_name;
				$tags[$row_tags->tag_id]['count'] = (isset($tags[$row_tags->tag_id]['count'])) ? $tags[$row_tags->tag_id]['count']+1 : 1;
			}
		}

		return $tags;
	}

	/**
	 * Возвращаем все элементы определенного типа по тегу
	 *
	 * @access  public
	 * @param   int
	 * @param   string
	 * @param   string
	 * @param   string
	 * @param   array
	 * @param   array
	 * @param   array
	 * @return  mixed
	 */

	function get_items_by_tag($id, $type, $table, $field, $where = array(), $order = array(), $cross = array()){

		$result = array();

		$this->db->select('tag_name, '.$table.'.'.$field);
		$this->db->from('w_tags_cross');
		$this->db->join('w_tags', 'w_tags.tag_id = w_tags_cross.tag_id', 'left');
		$this->db->join($table, $table.'.'.$field.' = w_tags_cross.item_id', 'left');
		if(is_array($cross) && count($cross) > 0) {
			$this->db->join( $cross['table'], $cross['table'] . '.' . $cross['field'] . ' = '.$table.'.'.$field, 'left' );
		}
		$this->db->where('w_tags_cross.tag_id', $id);
		$this->db->where('w_tags_cross.item_type', $type);
		if(is_array($where) && count($where) > 0){
			foreach ($where AS $key => $value){
				$this->db->where($key, $value);
			}
		}
		if(is_array($order) && count($order) > 0){
			foreach ($order AS $key => $value){
				$this->db->order_by($key, $value);
			}
		}
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row)
			{
				$result[] = $row->$field;
			}
			return array('result' => $result, 'name' => $row->tag_name);
		} else {
			return false;
		}
	}
}
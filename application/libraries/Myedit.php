<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вызов phpMyEdit
 */

require_once(FCPATH.'application/third_party/Myedit/phpMyEdit.class.php');

class Myedit extends phpMyEdit {

	private $interface;

	/**
	 * Конструктор
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function __construct($data_model = array())
	{
		// Начинаем перехват
		ob_start();

		// Регистрируем ресурсы
		parent::__construct($data_model);

		$this->interface = ob_get_contents();

		// Заканчиваем перехват
		ob_end_clean();
	}
    
    // ------------------------------------------------------------------------
    
	/**
	 * Отдаем интерфейс phpMyEdit
	 *
	 * @access	public
	 * @return	string
	 */
	function get_output()
	{
		return $this->interface;
	}
}
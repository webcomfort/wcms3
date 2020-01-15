<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Функции для модуля поиска
 */

class Search {

	private $CI;

	function __construct()
    {
        $this->CI =& get_instance();
    }

    // ------------------------------------------------------------------------

    /**
    * Подготовка текста для индексирования
    *
    * @access   public
    * @param    string
    * @param    string
    * @return   array
    */
    function index_prepare($text, $lang)
    {
        $this->CI->load->helper( array('text', 'string') );

        $words = mb_split (' ', $text);
        $count = count ($words);

        require_once(APPPATH.'third_party/Morphy/src/common.php');

        $opts = array(
            'storage' => PHPMORPHY_STORAGE_FILE,
            'predict_by_suffix' => true,
            'predict_by_db' => true,
            'graminfo_as_text' => true,
        );

        // Путь до словарей
        $dir = APPPATH.'third_party/Morphy/dicts';

        // Создаем экземпляр класса morphy
        try
        {
            $r_morphy = new phpMorphy($dir, $lang, $opts);
        }
        catch(phpMorphy_Exception $e)
        {
            die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
        }

        for ($i = 0; $i < $count; $i++)
        {
            if (mb_strlen($words[$i]) > 2) // берем только большие слова
            {
                $word = trim(mb_strtoupper($words[$i]));
                $word = $r_morphy->getPseudoRoot($word);

                if ($word[0] != '')
                {
                    $new_words[] = $word[0];
                }
                else
                {
                    $new_words[] = mb_strtoupper($words[$i]);
                }

                $sound[] = soundex (ru2lat($words[$i], 100));
            }
        }

        $words_ready = array(
            'words' => $new_words,
            'sound' => $sound
        );

        return $words_ready;
    }

    // ------------------------------------------------------------------------

    /**
    * Вставка индекса
    *
    * @access   public
    * @param    string
    * @param    string
    * @param    string
    * @param    array
    * @return   void
    */
    function index_insert($url, $title, $short, $words_array, $type, $item_id)
    {
        $words = $words_array['words']; // корни
        $sound = $words_array['sound']; // звучание

        // удаляем старые значения
        $this->CI->db->delete('w_indexing_index', array('url' => $url));
        $this->CI->db->delete('w_indexing_link', array('url' => $url));
        $this->CI->db->delete('w_indexing_word', array('url' => $url));

        // параметры страницы
        $data_link = array(
           'id'      => '',
           'url'     => $url,
           'title'   => htmlspecialchars ($title),
           'short'   => $short,
	       'type'    => $type,
           'item_id' => $item_id
        );

        $this->CI->db->insert('w_indexing_link', $data_link);
        $page_id = $this->CI->db->insert_id();

        $count = count($words);

        for ($i = 0; $i < $count; $i++)
        {
            if ($words[$i] != '' && $words[$i] != NULL)
            {
                $data_word = array(
                   'id'     => '',
                   'url'    => $url,
                   'word'   => $words[$i],
                   'sound'  => $sound[$i],
                   'type'    => $type,
                   'item_id' => $item_id
                );

                $this->CI->db->insert('w_indexing_word', $data_word);

                $this->CI->db->select_max('id');
                $query = $this->CI->db->get('w_indexing_word');
                $row = $query->row();

                $data_index = array(
                   'id'     => '',
                   'url'    => $url,
                   'link'   => $page_id,
                   'word'   => $row->id,
                   'times'  => '1',
                   'type'    => $type,
                   'item_id' => $item_id
                );

                $this->CI->db->insert('w_indexing_index', $data_index);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
    * Удаление индекса по полному урлу
    *
    * @access   public
    * @param    string
    * @return   void
    */
    function index_delete($url)
    {
        $this->CI->db->delete('w_indexing_index', array('url' => $url));
        $this->CI->db->delete('w_indexing_link', array('url' => $url));
        $this->CI->db->delete('w_indexing_word', array('url' => $url));
    }

	/**
	 * Удаление индекса по сегменту урла
	 *
	 * @access   public
	 * @param    string
	 * @return   void
	 */
	function index_delete_by_url($url)
	{
		$this->CI->db->query('DELETE FROM w_indexing_index WHERE url LIKE "%/'.$url.'/%"');
		$this->CI->db->query('DELETE FROM w_indexing_link WHERE url LIKE "%/'.$url.'/%"');
		$this->CI->db->query('DELETE FROM w_indexing_word WHERE url LIKE "%/'.$url.'/%"');
	}

	/**
	 * Удаление индекса по id элемента
	 *
	 * @access   public
	 * @param    int
	 * @param    string
	 * @return   void
	 */
	function index_delete_by_id($id, $type)
	{
		$query = $this->CI->db->get_where('w_indexing_link', array('item_id' => $id, 'type' => $type));
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$this->index_delete($row->url);
		}
	}

	/**
	 * Удаление индекса по типу
	 *
	 * @access   public
	 * @param    string
	 * @return   void
	 */
	function index_delete_by_type($type)
	{
		$this->CI->db->delete('w_indexing_index', array('type' => $type));
		$this->CI->db->delete('w_indexing_link', array('type' => $type));
		$this->CI->db->delete('w_indexing_word', array('type' => $type));
	}

	/**
	 * Обновление индекса по сегменту урла
	 *
	 * @access   public
	 * @param    string
	 * @param    string
	 * @return   void
	 */
	function index_update_by_url($url, $oldurl)
	{
		$this->CI->db->query("UPDATE w_indexing_index SET url = REPLACE(url, '/".$oldurl."/', '/".$url."/')");
		$this->CI->db->query("UPDATE w_indexing_link SET url = REPLACE(url, '/".$oldurl."/', '/".$url."/')");
		$this->CI->db->query("UPDATE w_indexing_word SET url = REPLACE(url, '/".$oldurl."/', '/".$url."/')");
	}
}
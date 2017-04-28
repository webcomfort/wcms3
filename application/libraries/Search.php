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
    function index_insert($url, $title, $short, $words_array)
    {
        $words = $words_array['words']; // корни
        $sound = $words_array['sound']; // звучание

        // удаляем старые значения
        $this->CI->db->delete('w_indexing_index', array('url' => $url));
        $this->CI->db->delete('w_indexing_link', array('url' => $url));
        $this->CI->db->delete('w_indexing_word', array('url' => $url));

        // параметры страницы
        $data_link = array(
           'id'     => '',
           'url'    => $url,
           'title'  => htmlspecialchars ($title),
           'short'  => $short
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
                   'sound'  => $sound[$i]
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
                );

                $this->CI->db->insert('w_indexing_index', $data_index);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
    * Удаление индекса
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
}
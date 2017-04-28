<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода результатов поиска
 */

class Mod_search extends CI_Model {

    private $search;
    private $search_out;

    function __construct()
    {
        parent::__construct();

        $this->lang->load('cms_search', LANGF);
        $this->load->helper(array('text','string'));

        if ($this->input->post('search')) $this->session->set_userdata(array('search'=>mysqli_real_escape_string($this->db->conn_id, $this->input->post('search', TRUE))));
        if ($this->session->userdata('search')) $this->search = $this->session->userdata('search');
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем результат
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        require_once(APPPATH.'third_party/Morphy/src/common.php');

        $opts = array(
            'storage' => PHPMORPHY_STORAGE_FILE,
            'predict_by_suffix' => true,
            'predict_by_db' => true,
            'graminfo_as_text' => true,
        );

        // Путь к словарям
        $dir = APPPATH.'third_party/Morphy/dicts';
        $langs = $this->config->item('cms_lang');
        $lang = $langs[LANG]['search'];

        // Создаем экземпляр
        try
        {
            $morphy = new phpMorphy($dir, $lang, $opts);
        }
        catch(phpMorphy_Exception $e)
        {
            die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
        }

        // Кодировка
        mb_regex_encoding('utf-8');
        mb_internal_encoding('utf-8');

        // Приводим в нужный вид
        $this->search = mb_strtolower(text2words($this->search), 'utf-8');
        $this->search_out = htmlspecialchars($this->search, ENT_QUOTES);

        // Разбиваем на слова
        $words = mb_split (' ', $this->search);

        // Псевдо-корни
        if(is_array($words))
        {
            try
            {
                foreach($words as $word)
                {
                    if (mb_strlen($word) > 2) // only big words
                    {
                        $word = trim(mb_strtoupper($word)); // we need big letters
                        $all = $morphy->getAllForms($word);
                        $pseudo_root = $morphy->getPseudoRoot($word);

                        if (is_array ($all))
                        {
                            $roots[] = $pseudo_root[0];
                        }
                        if (preg_match('/[-a-zA-Z0-9_\/\.]*/',$word))
                        {
                            $roots[] = $word;
                        }
                    }
                }
            }
            catch(phpMorphy_Exception $e)
            {
                die('Error occured while text processing: ' . $e->getMessage());
            }
        }

        if (isset($roots) && is_array($roots))
        {
            $count_roots = count($roots);
            $if_clause = '';

            // Дополнительный SQL
            for($i=0; $i<$count_roots; $i++)
            {
                $if_clause .= "iw.word='".mysqli_real_escape_string($this->db->conn_id, $roots[$i])."'";
                if($i!=$count_roots-1) $if_clause .= " OR ";
            }

            // Страницы
            $this->db->select('il.id');
            $this->db->from('w_indexing_link il, w_indexing_index ii, w_indexing_word iw');
            $this->db->where('('.$if_clause.')');
            $this->db->where('ii.word = iw.id AND il.id=ii.link');
            $this->db->group_by('il.id');
            $this->db->cache_off();
            $query_count = $this->db->get();

            // Лимиты
            $limit = 10;
            $start = (preg_int ($this->uri->segment(2))) ? $this->uri->segment(2) : 0;

            $this->lang->load('cms_pagination', LANGF);
            $this->load->library('pagination');
            $config['base_url']     = '/'.$this->uri->segment(1).'/';
            $config['total_rows']   = $query_count->num_rows();
            $config['per_page']     = $limit;
            $config['first_link']   = $this->lang->line('pagination_first_link');
            $config['last_link']    = $this->lang->line('pagination_last_link');
            $config['next_link']    = $this->lang->line('pagination_next_link');
            $config['prev_link']    = $this->lang->line('pagination_prev_link');
            $config['uri_segment']  = 2;
            $this->pagination->initialize($config);
            $pages = $this->pagination->create_links();

            // Поисковый запрос
            $this->db->select('il.id, il.url AS url, il.title AS title, il.short AS cut, COUNT(DISTINCT iw.id)*1000 + SUM(ii.times) AS rel');
            $this->db->from('w_indexing_link il, w_indexing_index ii, w_indexing_word iw');
            $this->db->where('('.$if_clause.')');
            $this->db->where('ii.word = iw.id AND il.id=ii.link');
            $this->db->group_by('il.id');
            $this->db->order_by("rel", "desc");
            $this->db->limit($limit,$start);
            $query = $this->db->get();
            $this->db->cache_on();

            if ($query->num_rows() > 0)
            {
                $data['search_result'] = true;
                $data['search_phrase'] = $this->search;
                $data['search_count'] = decl_of_num($query_count->num_rows(), array($this->lang->line('search_decl_1'), $this->lang->line('search_decl_2'), $this->lang->line('search_decl_3')));

                foreach ($query->result() as $row)
                {
                    $search_list[] = array (
                        'url'           => $row->url,
                        'title'         => $row->title,
                        'cut'           => $row->cut
                    );
                }

                $data['search_pages'] = $pages;
                $data['search_list'] = $search_list;
            }
            else
            {
                $data['search_phrase'] = $this->search_out;
                $data['search_result'] = false;
            }
        }
        else
        {
            $data['search_phrase'] = $this->search;
            $data['search_result'] = false;
        }

        return $this->load->view('site/search_result', $data, true);
    }
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка текстов, включений и индексация
 *
 * @action	update
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$articles = '';
$this->CI->db->cache_delete_all();

// Права доступа к элементам
$this->CI->cms_user->update_item_rights($id, 'page');

// ------------------------------------------------------------------------

// Пустой пункт в корзину для статей
$data = array(
    'id'			=> '',
    'pid'			=> 0,
    'description'	=> 'Изменение статей на странице '.$newvals['page_name'],
    'updated'		=> date('Y-m-d H:i:s'),
    'user'			=> $this->CI->cms_user->get_user_id(),
    'host'			=> $this->CI->input->ip_address(),
    'operation'		=> 'update',
    'tab'			=> 'w_pages',
    'rowkey'		=> $id,
    'col'			=> '',
    'files'		    => '',
    'oldval'		=> '',
    'newval'		=> ''
);

$this->CI->db->insert('w_changelog', $data);

$last_basket_element = $this->CI->trigger->get_last_basket_element();

// --------------------------------------------------------------------

$this->CI->db->select('article_id');
$this->CI->db->where('article_pid', $id);
$this->CI->db->where('article_pid_type', 'pages');
$query = $this->CI->db->get('w_pages_articles');
$total = $query->num_rows();
$i = 0;

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
	// Статьи

    if (preg_match("/^page_article_order_([1-9][0-9]*)$/", $key, $matches))
	{
		$bg     = $this->CI->input->post('page_article_bg_'.$matches[1]);
		$view   = $this->CI->input->post('page_article_view_'.$matches[1]);
		$place  = $this->CI->input->post('page_article_place_'.$matches[1]);

		$this->CI->db->select('article_id');
        $this->CI->db->where('article_pid', $id);
        $this->CI->db->where('article_pid_type', 'pages');
        $this->CI->db->where('article_order', $value);
        $query = $this->CI->db->get('w_pages_articles');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();

            $this->CI->trigger->change_relative ($row->article_id, $last_basket_element, 'w_pages_articles', 'article_id', 'article_text', 'Изменение статей на странице ', $oldvals['page_name']);

			$data = array(
                'article_order'     => $value,
                'article_bg_id'     => ($bg) ? $bg : '',
                'article_view_id'   => ($view) ? $view : '',
                'article_place_id'  => ($place) ? $place : '',
                'article_text'      => $this->CI->input->post('page_article_'.$matches[1], false)
            );
            $this->CI->db->where('article_id', $row->article_id);
            $this->CI->db->update('w_pages_articles', $data);
		}
		else
		{
            $data = array(
                'article_id' 		=> '',
                'article_pid'	    => $id,
                'article_pid_type'  => 'pages',
                'article_order' 	=> $value,
                'article_bg_id'     => ($bg) ? $bg : '',
                'article_view_id'   => ($view) ? $view : '',
                'article_place_id'  => ($place) ? $place : '',
                'article_text' 		=> $this->CI->input->post('page_article_'.$matches[1])
            );

            $this->CI->db->insert('w_pages_articles', $data);
		}

		$this->CI->cms_utils->update_updated('w_pages', 'page_id', $id);

		// Индексирование статей
		if($this->CI->config->item('cms_site_indexing'))
		{
            $articles .= $this->CI->input->post('page_article_'.$matches[1]);
		}

		$i++;
	}
}

// Если общее число статей уменьшилось
if ($total > $i) {
    for ($j = $i+1; $j <= $total; $j++) {
        // Удаление лишних статей
        $query = $this->CI->db->get_where('w_pages_articles', array('article_pid' => $id, 'article_pid_type' => 'pages'));

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                if($row->article_order == $j) $this->CI->trigger->delete_relative($row->article_id, $last_basket_element, 'w_pages_articles', 'article_id', 'Статья', '');
            }
        }
    }
}

// Подключения
$this->CI->Cms_inclusions->admin_inclusions_insert_update($id, 'pages');

// Индексирование статей
if ($newvals['page_status'] == '0' || $newvals['page_status'] == '3' || $articles == '') $this->CI->search->index_delete_by_id($id, 'page');
if ($newvals['page_url'] != $oldvals['page_url']) $this->CI->search->index_update_by_url($newvals['page_url'], $oldvals['page_url']);

if($this->CI->config->item('cms_site_indexing') && $articles != '' && $newvals['page_status'] != '0' && $newvals['page_status'] != '3'){
    $this->CI->load->library('search');
    $this->CI->load->helper('text');

	$url            = ($newvals['page_url'] == 'index') ? '/' : $this->CI->Cms_page->get_url($id);
    $title 			= $newvals['page_name'];
    $article_words 	= text2words(html_entity_decode($articles));
    $title_words 	= text2words($title);
    $short 			= word_limiter($article_words, 50);
    $lang_array 	= $this->CI->config->item('cms_lang');
    $lang			= $lang_array[$this->CI->session->userdata('w_alang')]['search'];

    $words_array = $this->CI->search->index_prepare($article_words . ' ' . $title_words, $lang);
    $this->CI->search->index_insert($url, $title, $short, $words_array, 'page', $id);
}

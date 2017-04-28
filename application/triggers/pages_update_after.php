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

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
	// Статьи

    if (preg_match("/^page_article_([1-9][0-9]*)$/", $key, $matches))
	{
		$this->CI->db->select('pa_id, article_text');
        $this->CI->db->where('article_page_id', $id);
        $this->CI->db->where('article_id', $matches[1]);
        $query = $this->CI->db->get('w_pages_articles');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();

            $this->CI->trigger->change_relative ($row->pa_id, $this->CI->trigger->get_last_basket_element(), 'w_pages_articles', 'pa_id', 'article_text', 'Изменение статей на странице ', $oldvals['page_name']);

			$data = array( 'article_text' => $value );
			$this->CI->db->where('pa_id', $row->pa_id);
			$this->CI->db->update('w_pages_articles', $data);
		}
		else
		{
			$data = array(
				'pa_id' 			=> '',
				'article_page_id'	=> $id,
				'article_id' 		=> $matches[1],
				'article_text' 		=> $this->CI->input->post($key)
			);

			$this->CI->db->insert('w_pages_articles', $data);
		}

		// Индексирование статей
		if($this->CI->config->item('cms_site_indexing'))
		{
            $articles .= $this->CI->input->post($key);
		}
	}
}

// Подключения
$this->CI->Cms_inclusions->admin_inclusions_insert_update($id, 'pages');

// Индексирование статей
if($this->CI->config->item('cms_site_indexing'))
{
    $this->CI->load->library('search');
    $this->CI->load->helper('text');

    $url 			= ($newvals['page_status'] == '4') ? $url = '/' : '/'.$newvals['page_url'];
    $title 			= $newvals['page_name'];
    $article_words 	= text2words(html_entity_decode($articles));
    $title_words 	= text2words($title);
    $short 			= word_limiter($article_words, 50);
    $lang_array 	= $this->CI->config->item('cms_lang');
    $lang			= $lang_array[$this->CI->session->userdata('w_alang')]['search'];

    if ($newvals['page_status'] == '4' || $newvals['page_status'] == '3' || $newvals['page_url'] != $oldvals['page_url']) $this->CI->search->index_delete('/'.$oldvals['page_url']);

    $words_array = $this->CI->search->index_prepare($article_words . ' ' . $title_words, $lang);
    if ($newvals['page_status'] != '3') $this->CI->search->index_insert($url, $title, $short, $words_array);
}
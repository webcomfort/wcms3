<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Функция очистки вводимого кода. Оставляет только слова.
* Может быть полезно для последующей отправки этого текста на индексацию.
*
* @access	public
* @param    string
* @return   string
**/
if ( ! function_exists('text2words'))
{
	function text2words($text)
	{
		$text = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $text);
		$text = strip_tags ($text);
        $text = htmlspecialchars_decode($text);
		$text = preg_replace('@<script[^>]*?>.*?</script>@si',' ',$text);
		$text = preg_replace('@<style[^>]*?>.*?</style>@si',' ',$text);
		$text = preg_replace('@<[\/\!]*?[^<>]*?>@si',' ',$text);
		$text = preg_replace('@{[\/\!]*?[^<>]*?}@si',' ',$text);
        $text = preg_replace('@\n@si',' ',$text);
        $text = preg_replace('@\s\s@si',' ',$text);
        $text = preg_replace('@\t@si','',$text);
        $text = preg_replace('@\&nbsp;@si',' ',$text);
        $text = preg_replace('@\-@si',' ',$text);

		mb_regex_encoding('utf-8');
		mb_internal_encoding('utf-8');

		$text = mb_strtolower($text, 'utf-8');
		$text = mb_eregi_replace ('\s(.\..\.)', ' ', $text);
		$text = mb_eregi_replace ('\s(.\.)', ' ', $text);
		$text = mb_eregi_replace ('[.?,!()#"\':;|\/]', '', $text);

        return $text;
	}
}
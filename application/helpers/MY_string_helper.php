<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Преобразование русских символов в латинские /translit/
*
* @access	public
* @param    string  - строка для конвертации
* @param    int     - число символов для возврата
* @return	string
*/
if ( ! function_exists('ru2lat'))
{
	function ru2lat($string, $limit=0) {

		$rus = array('ё','ж','ц','ч','ш','щ','ю','я','Ё','Ж','Ц','Ч','Ш','Щ','Ю','Я',' ',',','.','?','&','!',':','—','(',')','«','»','–','"');
		$lat = array('yo','zh','tc','ch','sh','sh','yu','ya','yo','zh','tc','ch','sh','sh','yu','ya','_','','','','','','','','','','','','','');

		$string = str_replace($rus,$lat,$string);

		$rus = array('А','Б','В','Г','Д','Е','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ъ','Ы','Ь','Э','а','б','в','г','д','е','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ъ','ы','ь','э');
		$lat = array('a','b','v','g','d','e','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','','i','','e','a','b','v','g','d','e','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','','i','','e');

		$string = str_replace($rus,$lat,$string);

		$string = trim($string);
		if ($limit != 0) $string = substr($string, 0, $limit);

		return $string;

	}
}

// ------------------------------------------------------------------------

/**
* Склонение слова
*
* @access	public
* @param    int     - число
* @param    array   - массив вариантов ('комментарий','комментария','комментариев')
* @return   string
**/
if ( ! function_exists('decl_of_num'))
{
	function decl_of_num($number, $titles)
	{
		$cases = array (2, 0, 1, 1, 1, 2);
		return "<span>".$number."</span> ".$titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
	}
}
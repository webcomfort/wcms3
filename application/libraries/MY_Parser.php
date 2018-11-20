<?php
class MY_Parser extends CI_Parser {

    /**
    * Парсинг шаблона, в котором можно вызвать модуль
    *
    * @access	public
    * @param    string
    * @return	string
    */

	function parse_modules($template)
	{
		preg_match_all('~<(div|p)>\{@\s?module\s?(.+?)@\}</(div|p)>~', $template, $matches);

		if(count($matches[2]))
		{
			foreach ($matches[2] as $key => $value)
			{
				$value = trim($value);
				$value = preg_replace('!\s+!', ' ', $value);
				$pieces = explode(" ", $value);
				$params = array();

				for ($i = 0, $size = sizeof($pieces); $i < $size; ++$i)
				{
					if($i == 0) $model = $pieces[$i];
					if($i > 0) $params[] = $pieces[$i];
				}

				$template = str_replace($matches[0][$key], module($model,$params), $template);
			}
		}
		return $template;
	}
}
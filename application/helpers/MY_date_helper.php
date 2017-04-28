<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Возвращает русскую дату для вывода на сайт
*
* @access   public
* @param    string - YYYY-MM-DD HH:MM:SS
* @param    string - вид возвращаемой даты
* @return   string
*/

if ( ! function_exists('az_date_format_rus'))
{
	function date_format_rus ( $date, $mode = 'full' )
	{
		$month = array(
			'01' => 'января',
			'02' => 'февраля',
			'03' => 'марта',
			'04' => 'апреля',
			'05' => 'мая',
			'06' => 'июня',
			'07' => 'июля',
			'08' => 'августа',
			'09' => 'сентября',
			'10' => 'октября',
			'11' => 'ноября',
			'12' => 'декабря'
		);

		$day = substr ($date, 8, 2);
		$mon = substr ($date, 5, 2);
		$year = substr ($date, 0, 4);
		$hour = substr ($date, 11, 2);
		$minute = substr ($date, 14, 2);

		if ($mode == 'full')
		{
			$date = $hour . ':' . $minute . ",&nbsp;" . $day . "&nbsp;" . $month[$mon] . " " . $year . "&nbsp;года";
		}

        if ($mode == 'date')
		{
			$date = $day . "&nbsp;" . $month[$mon] . " " . $year . "&nbsp;года";
		}

		return $date;
	}
}
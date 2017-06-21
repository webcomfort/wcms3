<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Возвращает русскую дату для вывода на сайт
*
* @access   public
* @param    string - YYYY-MM-DD HH:MM:SS
* @param    string - вид возвращаемой даты
* @return   string
*/

if ( ! function_exists('date_format_rus'))
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

/**
 * Проверяет, рабочее ли сейчас (в указанную дату) время
 *
 * @access	public
 * @param	string
 * @return	bool
 */
if ( ! function_exists('is_holiday'))
{
    function is_holiday ($date=false)
    {
        $time = ($date) ? strtotime($date) : time();

        $year       = date('Y', $time);
        $mon        = date('n', $time);
        $day        = date('j', $time);
        $hol        = date('N', $time);
        $check_hol  = ($hol == 6 || $hol == 7) ? true : false;
        $hour       = date('G', $time);
        $hour_limit = 18;
        $root       = $_SERVER['DOCUMENT_ROOT'].'/public/';
        $file       = $root."calendar-".$year.".json";
        $old        = $root."calendar-".($year-1).".json";

        if(!is_file($file)){
            if (is_file($old)) unlink($old);
            $calendar = @file_get_contents("http://basicdata.ru/api/json/calend/");
            file_put_contents ($file, $calendar);
        } else {
            $calendar = @file_get_contents($file);
        }

        $calendar = json_decode($calendar);
        $calendar = $calendar->data->$year;

        if(isset($calendar->$mon->$day)) {
            /*
            0 — рабочий день;
            2 — праздничный/нерабочий день;
            3 — сокращенный на 1 час рабочий день.
            */
            $check_hol = ($check_hol && ($calendar->$mon->$day->isWorking != '0' || $calendar->$mon->$day->isWorking != '3')) ? true : false;
            $check_hol = (!$check_hol && $calendar->$mon->$day->isWorking == '2') ? true : false;
            if($calendar->$mon->$day->isWorking == '3') $hour_limit = $hour_limit - 1;
        }

        if($check_hol || $hour >= $hour_limit) return true;
        else return false;
    }
}
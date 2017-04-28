<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Функция перемещения файла в корзину.
*
* @access	public
* @param    string - путь к файлу
* @param    string - имя файла, обычно это id
* @param    int    - id значения в w_changelog
* @return	void
*/
if ( ! function_exists('file_delete'))
{
	function file_delete ($path, $filename, $iid = false)
    {
        $dir = FCPATH.substr($path, 1);
        $trash_path = ($iid) ? FCPATH.substr($path, 1).'trash/'.$iid.'/' : FCPATH.substr($path, 1).'trash/';

        if (!is_dir($trash_path)) mkdir($trash_path, 0750);

        if ($handle = opendir($dir))
        {
            while (false !== ($file = readdir($handle)))
            {
                if (preg_match ("/^".$filename."\.([[:alnum:]])*$/", $file))
                {
                    rename($dir.$file, $trash_path.$file);
                }
            }
        }
	}
}

/**
* Функция перемещения связанных файлов в корзину.
*
* @access	public
* @param    string - путь к файлу
* @param    string - имя файла, обычно это id
* @param    int    - id значения в w_changelog
* @return	void
*/
if ( ! function_exists('files_delete'))
{
	function files_delete ($path, $filename, $iid = false)
    {
        $dir = FCPATH.substr($path, 1);
        $trash_path = ($iid) ? FCPATH.substr($path, 1).'trash/'.$iid.'/' : FCPATH.substr($path, 1).'trash/';

        if (!is_dir($trash_path)) mkdir($trash_path, 0750);

        if ($handle = opendir($dir))
        {
            while (false !== ($file = readdir($handle)))
            {
                if (preg_match ("/^".$filename."\.([[:alnum:]])*$/", $file) || preg_match ("/^".$filename."_([[:alnum:]])*\.([[:alnum:]])*$/", $file))
                {
                    rename($dir.$file, $trash_path.$file);
                }
            }
        }
	}
}
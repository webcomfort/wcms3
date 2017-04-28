<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Проверка хэша
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_hash'))
{
	function preg_hash ($value)
	{
		if (preg_match('/^[a-z0-9]*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на строку, без знаков препинания
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_alpha'))
{
	function preg_alpha ($value)
	{
		if (preg_match('/^[a-zA-Z]*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на строку со знаками препинания
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_ext_alpha'))
{
	function preg_ext_alpha ($value)
	{
		if (preg_match('/^[-a-zA-Z_]*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на строку с числами, без знаков препинания
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_string'))
{
	function preg_string ($value)
	{
		if (preg_match('/^[a-zA-Z0-9]*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на строку с числами и со знаками препинания
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_ext_string'))
{
	function preg_ext_string ($value)
	{
		if (preg_match('/^[-a-zA-Z0-9_]*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на число
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_int'))
{
	function preg_int ($value)
	{
		if (preg_match('/^[0-9]*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на email
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_email'))
{
	function preg_email ($value)
	{
		if (preg_match('/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на стойкость пароля
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_pass'))
{
	function preg_pass ($value)
	{
		if (preg_match('/^(?=^.{6,}$)((?=.*[A-Za-z0-9])(?=.*[A-Z])(?=.*[a-z]))^.*$/', $value)) return true;
		else return false;
	}
}

// ------------------------------------------------------------------------

/**
* Проверка на URL
*
* @access	public
* @param    string
* @return	bool
*/
if ( ! function_exists('preg_url'))
{
	function preg_url ($value)
	{
		if (preg_match('/^(((http|https|ftp):\/\/)?([[a-zA-Z0-9]\-\.])+(\.)([[a-zA-Z0-9]]){2,4}([[a-zA-Z0-9]\/+=%&_\.~?\-]*))*$/', $value)) return true;
		else return false;
	}
}
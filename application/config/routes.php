<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['translate_uri_dashes']  = FALSE;
$route['sitemap\.xml']          = "sitemap"; // ссылка на карту сайта
$route['default_controller']    = "page"; // по умолчанию вызываем page, а он сам ищет стартовую страницу по базе
$route['404_override']          = 'page'; // при 404 вызываем page, а он ищет страницу по базе, отвечающую за вывод 404
$route['(.+)/p_(.+)']       = "pub"; // все ссылки вида /class/p_method вызывают pub - единственный разрешенный публичный вызов функции, в основном для AJAX. Class в данном случае, это модель, которую загружает pub.
$route['admin']                 = "admin"; // все ссылки вида /admin вызывают контроллер admin
$route['admin/(.+)']          = "admin"; // все ссылки вида /admin/... вызывают контроллер admin
$route['.+']                    = "page"; // если ничего из вышеперечисленного не подошло, вызываем page

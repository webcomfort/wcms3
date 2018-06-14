<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Глобально
|--------------------------------------------------------------------------
*/
$lang['global_search'] = '/search-en';
$lang['global_print']  = 'print';
$lang['global_crumbs'] = '<a href="/index-en">Home</a>';

/*
|--------------------------------------------------------------------------
| Контакты
|--------------------------------------------------------------------------
*/
$lang['contacts_title']     = 'Send the message';
$lang['contacts_name']      = 'Name';
$lang['contacts_email']     = 'Email';
$lang['contacts_message']   = 'Message';
$lang['contacts_captcha']   = 'Captcha';
$lang['contacts_submit']    = 'Send message';
$lang['contacts_subject']   = 'Letter from the websites contact form '.$_SERVER["HTTP_HOST"];
$lang['contacts_required_error']    = 'Please, fill in message field';
$lang['contacts_email_error']       = 'Not valid email';
$lang['contacts_captcha_error']     = 'Not valid captcha';
$lang['contacts_send_error']        = 'Message was not sent!';
$lang['contacts_success'] = 'Thank you, your message was sent. We will reply you shortly!';

/*
|--------------------------------------------------------------------------
| Новости
|--------------------------------------------------------------------------
*/
$lang['news_name']          = 'Post';
$lang['news_latest_link']   = 'Read all news';
$lang['news_content_link']  = 'Back to the news list';
$lang['news_page']          = 'page';
$lang['news_404']           = 'Page was not found!';

/*
|--------------------------------------------------------------------------
| Теги
|--------------------------------------------------------------------------
*/
$lang['tags_with']      = ' with tag ';
$lang['tags_cancel']    = 'Reset';
$lang['tags_title']     = 'Tags:';
$lang['tags_all']       = 'All tags';

/*
|--------------------------------------------------------------------------
| Постраничная разбивка
|--------------------------------------------------------------------------
*/
$lang['pagination_first_link'] = '&laquo; First';
$lang['pagination_last_link'] = 'Last &raquo;';
$lang['pagination_next_link'] = 'Next';
$lang['pagination_prev_link'] = 'Prev';

/*
|--------------------------------------------------------------------------
| Поиск
|--------------------------------------------------------------------------
*/
$lang['search_phrase']  = 'Enter search words';
$lang['search_submit']  = 'Search';
$lang['search_decl_1']  = 'match';
$lang['search_decl_2']  = 'matches';
$lang['search_decl_3']  = 'matches';
$lang['search_result']  = 'Search results for "%s"';
$lang['search_error']   = 'No match is found.';
$lang['search_num']     = '%s is found';

/*
|--------------------------------------------------------------------------
| Названия полей форм, кнопок и т.д.
|--------------------------------------------------------------------------
*/

$lang['cms_user_form_1'] = 'Email';
$lang['cms_user_form_2'] = 'Password';
$lang['cms_user_form_3'] = 'Forgot your password?';
$lang['cms_user_form_4'] = 'Remember me on this computer';
$lang['cms_user_form_5'] = 'Enter';
$lang['cms_user_form_6'] = 'Reset your password';
$lang['cms_user_form_7'] = 'Back to entrance';
$lang['cms_user_form_8'] = 'Enter new password';
$lang['cms_user_form_9'] = 'Confirm new password';
$lang['cms_user_form_10'] = 'Change password';
$lang['cms_user_form_11'] = 'Verification code';
$lang['cms_user_form_12'] = 'Captcha';

/*
|--------------------------------------------------------------------------
| Ошибки
|--------------------------------------------------------------------------
*/

$lang['cms_user_error_1'] = 'Incorrect login or password!';
$lang['cms_user_error_2'] = 'Incorrect login!';
$lang['cms_user_error_3'] = 'Incorrect password!';
$lang['cms_user_error_4'] = 'You have no permission for the requested action!';
$lang['cms_user_error_5'] = 'An access code was sent to you via email.';
$lang['cms_user_error_6'] = 'No account found with that email address!';
$lang['cms_user_error_7'] = 'Incorrect email!';
$lang['cms_user_error_8'] = 'Your password was sent to you via email.';
$lang['cms_user_error_9'] = 'Field %s is required';
$lang['cms_user_error_10'] = 'Please check your email.';
$lang['cms_user_error_11'] = 'Incorrect access code.';
$lang['cms_user_error_12'] = 'Your password was successfully changed.';
$lang['cms_user_error_13'] = 'Passwords differ.';
$lang['cms_user_error_14'] = 'Password must be at least 6 characters long.';
$lang['cms_user_error_15'] = 'Password change failed';
$lang['cms_user_error_16'] = 'May be you are robot!';

/*
|--------------------------------------------------------------------------
| Заголовки
|--------------------------------------------------------------------------
*/

// Подтверждение регистрации
$lang['cms_user_reg_conf_subj'] = $_SERVER["HTTP_HOST"].' registration confirmation.';

// После успешной регистрации
$lang['cms_user_reg_subj'] = 'New account registration for '.$_SERVER["HTTP_HOST"];

// Подтверждение сброса пароля
$lang['cms_user_rem_conf_subj'] = 'Password reset link for '.$_SERVER["HTTP_HOST"];

// Письмо с паролями после сброса
$lang['cms_user_pass_subj'] = 'Your account registration information for '.$_SERVER["HTTP_HOST"];

// Подтверждение удаления учетной записи
$lang['cms_user_del_conf_subj'] = 'Confirm the deletion of your account for '.$_SERVER["HTTP_HOST"];

// Уведомление об удалении учетной записи
$lang['cms_user_del_subj'] = 'Your '.$_SERVER["HTTP_HOST"].' account was deleted and is no longer recoverable.';

/*
|--------------------------------------------------------------------------
| Письма
|--------------------------------------------------------------------------
*/

// Подтверждение регистрации
//-------------------------------------------------------------------------
$lang['cms_user_reg_conf_mess'] = "Good day!

Someone requested registration of a new account on ".$_SERVER["HTTP_HOST"].".
If it wasn't you, please ignore this e-mail and no changes will be made to your account.

However, if you have requested account registration, please click the link below.
http://".$_SERVER["HTTP_HOST"]."/%s/register/%s

Thanks,
".$_SERVER["HTTP_HOST"]." team
http://".$_SERVER["HTTP_HOST"];

// Подтверждение сброса пароля
//-------------------------------------------------------------------------
$lang['cms_user_pass_conf_mess'] = "Good day!

Someone (hopefully you) has asked us to reset the password for your ".$_SERVER["HTTP_HOST"]." account.
If you didn't request this password reset, you can go ahead and ignore this email!

Please follow the link below to do so.
http://".$_SERVER["HTTP_HOST"]."/%s/remember/%s

Be careful! This link is valid 24 hours only.

Thanks,
".$_SERVER["HTTP_HOST"]." team
http://".$_SERVER["HTTP_HOST"];

// Письмо с паролем
//-------------------------------------------------------------------------
$lang['cms_user_pass_mess'] = 'Good day!

To access the site '.$_SERVER["HTTP_HOST"].' just use this data:
-------------------------------------
Login:  %s
Password: %s
-------------------------------------
Have a nice day!

Thanks,
'.$_SERVER["HTTP_HOST"].' team
http://'.$_SERVER["HTTP_HOST"];

// Подтверждение удаления учетной записи
//-------------------------------------------------------------------------
$lang['cms_user_del_conf_mess'] = "Good day!

Someone (hopefully you) has asked us to delete your ".$_SERVER["HTTP_HOST"]." account.
If you didn't request this action, you can go ahead and ignore this email!

Please follow the link below to do so.
http://".$_SERVER["HTTP_HOST"]."/%s/delete/%s

Thanks,
".$_SERVER["HTTP_HOST"]." team
http://".$_SERVER["HTTP_HOST"];

// Письмо об удалении
//-------------------------------------------------------------------------
$lang['cms_user_del_mess'] = 'Good day!

Your account data was successfully deleted and is no longer recoverable.

Thanks,
'.$_SERVER["HTTP_HOST"].' team
http://'.$_SERVER["HTTP_HOST"];
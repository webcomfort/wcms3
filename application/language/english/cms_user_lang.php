<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

/*
|--------------------------------------------------------------------------
| Заголовки
|--------------------------------------------------------------------------
*/

// Подтверждение регистрации
$lang['cms_user_reg_conf_subj'] = $_SERVER["HTTP_HOST"].' registration confirmation.';

// После успешной регистрации
$lang['cms_user_reg_subj'] = 'New account registration for '.$_SERVER["HTTP_HOST"].;

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
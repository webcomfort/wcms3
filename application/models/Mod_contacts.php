<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль формы для контактов
 */

class Mod_contacts extends CI_Model {

    function __construct()
    {
        $this->lang->load('cms', $this->session->userdata('LANGF'));
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем блок
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        return $this->load->view('site/contacts', array(), true);
    }

    // ------------------------------------------------------------------------

    function p_send()
    {
        $this->load->model('Cms_page');

        $email  = $this->input->post('contacts_email', TRUE);
        $name = $this->input->post('contacts_name', TRUE);
        $subject = $this->input->post('contacts_subject', TRUE);
        $message = $this->input->post('contacts_message', TRUE);

        if ($this->input->post('g-recaptcha-response', TRUE))
        {
            if ($this->_recaptcha($this->input->post('g-recaptcha-response', TRUE)))
            {
                if ($email != '' && preg_match('/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})*$/', $email))
                {
                    $this->load->model('Cms_page');
                    $mail = $this->Cms_page->get_config('contacts_email');

                    $this->load->library('email');

                    $this->email->from($email, $name);
                    $this->email->to($mail);
                    $this->email->subject(($subject == '') ? $this->lang->line('contacts_subject') : $subject);
                    $this->email->message($message);

                    $response = array();

                    if ($this->email->send()) {
                        $response['error_code'] = 2;
                        $response['error'] = '<div class="alert alert-success" role="alert">' . $this->lang->line('contacts_success') . '</div>';
                        echo json_encode($response);
                    }
                    else {
                        $response['error_code'] = 1;
                        $response['error'] = '<div class="alert alert-danger" role="alert">' . $this->lang->line('contacts_send_error') . '</div>';
                        echo json_encode($response);
                    }
                }
                else {
                    $response['error_code'] = 1;
                    $response['error'] = '<div class="alert alert-danger" role="alert">' . $this->lang->line('contacts_email_error') . '</div>';
                    echo json_encode($response);
                }
            }
            else {
                $response['error_code'] = 1;
                //$response['error'] = '<div class="alert alert-danger" role="alert">' . $this->lang->line('contacts_captcha_error') . '</div>';
                $response['error'] = $this->input->post('g-recaptcha-response', TRUE);
                echo json_encode($response);
            }
        }
        else {
            $response['error_code'] = 1;
            $response['error'] = '<div class="alert alert-danger" role="alert">' . $this->lang->line('contacts_captcha_error') . '</div>';
            echo json_encode($response);
        }
    }

    function _recaptcha($response='')
    {
        $google_url = "https://www.google.com/recaptcha/api/siteverify";
        $secret = $this->Cms_page->get_config('recaptcha');
        $ip = $this->input->ip_address();
        $url = $google_url."?secret=".$secret."&response=".$response."&remoteip=".$ip;

        // POST
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $res = curl_exec($curl);
        curl_close($curl);

        $res= json_decode($res, true);
        if($res['success'])
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}
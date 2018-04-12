<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2017, Derek Zhang
 * Released under the MIT license
 */

namespace Bricker;

function email_address_check($email)
{
    $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    
    if (preg_match($pattern, $email)) {
        // email address
        return true;
    } else {
        return false;
    }
}

/**
 * 邮件发送
 *
 * @param: $name[string]        接收人姓名
 * @param: $email[string]       接收人邮件地址
 * @param: $subject[string]     邮件标题
 * @param: $content[string]     邮件内容
 * @param: $config[array]       邮件服务的配置信息：USE_SMTP, SENDER_NAME, SENDER_EMAIL,
 *                                              SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS
 * @param: $type[int]           0 普通邮件， 1 HTML邮件
 * @param: $notification[bool]  true 要求回执， false 不用回执
 *
 * @return boolean
 */
function send_mail($toName, $toEmail, $subject, $content, $config, $type = 0, $notification=false)
{
    $charset = 'utf-8';
    $useSmtp = $config['USE_SMTP'];
    $useSsl = $config['USE_SSL'];
    $senderName = $config['SENDER_NAME'];
    $senderEmail = $config['SENDER_EMAIL'];
    
    if ($useSmtp) {
        $smtpHost = $config['SMTP_HOST'];
        $smtpPort = $config['SMTP_PORT'];
        $smtpUser = $config['SMTP_USER'];
        $smtpPass = $config['SMTP_PASS'];
    }
    
    /**
     * 使用smtp服务发送邮件
     */
    if ($useSmtp) {
        /* 邮件的头部信息 */
        $content_type = ($type == 0) ?
            'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
        $content   =  base64_encode($content);

        $headers = array();
        $headers[] = 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000';
        $headers[] = 'To: "' . '=?' . $charset . '?B?' . base64_encode($toName) . '?=' . '" <' . $toEmail. '>';
        $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($senderName) . '?='.'" <' . $senderEmail . '>';
        $headers[] = 'Subject: ' . '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
        $headers[] = $content_type . '; format=flowed';
        $headers[] = 'Content-Transfer-Encoding: base64';
        $headers[] = 'Content-Disposition: inline';
        if ($notification)
        {
            $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($senderName) . '?='.'" <' . $senderEmail . '>';
        }

        /* 获得邮件服务器的参数设置 */
        $params['host'] = $smtpHost;
        $params['port'] = $smtpPort;
        $params['user'] = $smtpUser;
        $params['pass'] = $smtpPass;
        $params['smtp_ssl'] = $useSsl;

        if (empty($params['host']) || empty($params['port']))
        {
            return false;
        }
        else
        {
            // 发送邮件
            if (!function_exists('fsockopen'))
            {
                return false;
            }

            include_once(BRICKER_PATH . '/Lib/smtp.php');
            static $smtp;
            
            $send_params['recipients'] = $toEmail;
            $send_params['headers']    = $headers;
            $send_params['from']       = $senderEmail;
            $send_params['body']       = $content;
            
            if (!isset($smtp))
            {
                $smtp = new smtp($params);
            }
            
            if ($smtp->connect() && $smtp->send($send_params))
            {
                return true;
            }
            else
            {
                $err_msg = $smtp->error_msg();
                return false;
            }
        }
    }
    /**
     * 使用服务器内置的 Mail 服务 mail函数发送邮件
     */
    else {
        if (function_exists('mail')) {
            /* 邮件的头部信息 */
            $content_type = ($type == 0) ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
            $headers = array();
            $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($senderName) . '?='.'" <' . $senderEmail . '>';
            $headers[] = $content_type . '; format=flowed';
            if ($notification)
            {
                $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($senderName) . '?='.'" <' . $senderEmail . '>';
            }

            $res = @mail($toEmail, '=?' . $charset . '?B?' . base64_encode($subject) . '?=', $content, implode("\r\n", $headers));

            if ($res) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

///////////// Test Code /////////////////
/*
echo "\nemail.php test begin\n\n";
$email = 'derek@gmail.com.cn';
if (email_address_check($email) === true) {
    $result = 'true';
} else {
    $result = 'false';
}
echo $email . " return " . $result . "\n";
echo "\nemail.php test end\n\n";
// */

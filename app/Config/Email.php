<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail = 'pharmedisync@gmail.com';
public string $fromName  = 'PharMediSync';

public string $protocol   = 'smtp';
public string $SMTPHost   = 'smtp.gmail.com';
public string $SMTPUser   = 'pharmedisync@gmail.com';
public string $SMTPPass   = 'wsavchtqkwuxrmaq';
public int    $SMTPPort   = 587;
public string $SMTPCrypto = 'tls';
public bool   $SMTPKeepAlive = false;

public string $mailType = 'text';
public string $charset  = 'UTF-8';
public bool   $wordWrap = true;

public array $SMTPCryptoOpts = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ],
];

    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';


    /**
     * Which SMTP authentication method to use: login, plain
     */
    public string $SMTPAuthMethod = 'login';



    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 30;


    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;


    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;
}

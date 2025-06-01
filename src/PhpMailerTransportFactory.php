<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\OAuthTokenProvider;

class PhpMailerTransportFactory extends AbstractTransportFactory
{

    public function create(Dsn $dsn): TransportInterface
    {
        $host = $dsn->getHost();
        $port = $dsn->getPort(0);
        $secure = $dsn->getOption('secure', '');
        $transport = new PhpMailerTransport($host, $port, $secure, $this->dispatcher, $this->logger);
        $mailer = $transport->getMailer();
        $mailer->isSMTP();

        // Set up server settings
        $username = $dsn->getUser();
        $password = $dsn->getPassword();
        $mailer->SMTPAuth = $username != "" && $password != "";
        $mailer->Username = $username;
        $mailer->Password = $password;
        if ($dsn->getOption('debug', false)) {
            $mailer->SMTPDebug = 2; // DEBUG_SERVER
        }
        $options = [];
        if ('' !== $dsn->getOption('verify_peer') && !filter_var($dsn->getOption('verify_peer', true), \FILTER_VALIDATE_BOOL)) {
            $options['ssl']['verify_peer'] = false;
            $options['ssl']['verify_peer_name'] = false;
            $options['ssl']['allow_self_signed'] = true;
        }
        if (null !== $peerFingerprint = $dsn->getOption('peer_fingerprint')) {
            $options['ssl']['peer_fingerprint'] = $peerFingerprint;
        }
        $mailer->SMTPOptions = array_merge($options, $dsn->getOption('options', []));
        if (Config('PHPMAILER_OAUTH') instanceof OAuthTokenProvider) {
            $mailer->AuthType = 'XOAUTH2'; // Set AuthType to use XOAUTH2
            $mailer->setOAuth(Config('PHPMAILER_OAUTH'));
        }
        return $transport;
    }

    protected function getSupportedSchemes(): array
    {
        return ['phpmailer'];
    }
}

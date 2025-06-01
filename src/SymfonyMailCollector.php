<?php

namespace PHPMaker2025\ucarsip;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Message;

/**
 * Collects data about sent mail events
 * https://github.com/symfony/mailer
 *
 * Based on DebugBar\Bridge\Symfony\SymfonyMailCollector
 */
class SymfonyMailCollector extends DataCollector implements Renderable, AssetProvider
{
    protected array $messages = [];
    protected bool $showBody = true;

    /**
     * Add failed message
     *
     * @param RawMessage $message
     * @return void
     */
    public function addFailedMessage(RawMessage $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Add sent message
     *
     * @param SentMessage $message
     * @return void
     */
    public function addSentMessage(SentMessage $message)
    {
        $this->messages[] = $message->getOriginalMessage();
    }

    public function getShowBody(): bool
    {
        return $this->showBody;
    }

    public function setShowBody(bool $show)
    {
        $this->showBody = $show;
        return $this;
    }

    public function collect()
    {
        $mails = array();
        foreach ($this->messages as $message) {
            if (!$message instanceof Message) {
                continue;
            }
            $mail = [
                'to' => array_map(function ($address) {
                    /* @var \Symfony\Component\Mime\Address $address */
                    return $address->toString();
                }, $message->getTo()),
                'subject' => $message->getSubject(),
                'headers' => $message->getHeaders()->toString(),
                'body' => null,
                'html' => null,
            ];
            if ($this->showBody) {
                $body = $message->getBody();
                if ($body instanceof AbstractPart) {
                    $mail['html'] = $message->getHtmlBody();
                    $mail['body'] = $message->getTextBody();
                } else {
                    $mail['body'] = $body->bodyToString();
                }
            }
            $mails[] = $mail;
        }
        return array(
            'count' => count($mails),
            'mails' => $mails,
        );
    }
    public function getName()
    {
        return 'symfonymailer_mails';
    }
    public function getWidgets()
    {
        return array(
            'emails' => array(
                'icon' => 'inbox',
                'widget' => 'PhpDebugBar.Widgets.MailsWidget',
                'map' => 'symfonymailer_mails.mails',
                'default' => '[]',
                'title' => 'Mails'
            ),
            'emails:badge' => array(
                'map' => 'symfonymailer_mails.count',
                'default' => 'null'
            )
        );
    }
    public function getAssets()
    {
        return array(
            'css' => 'widgets/mails/widget.css',
            'js' => '../../../../../../jquery/mails.widget.js'
        );
    }
}

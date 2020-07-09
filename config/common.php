<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

return [
    'components' => [
        'mailer' => array_filter([
            'useFileTransport' => false,
            'messageClass' => \hiam\base\Message::class,
            'htmlLayout' => $params['user.seller'] && $params['user.htmlEmails']
                ? "@{$params['user.seller']}/assets/mail/layout/html"
                : '@vendor/hiqdev/hisite/src/views/layouts/mail-html',
            'textLayout' => $params['user.seller'] && $params['user.htmlEmails']
                ? "@{$params['user.seller']}/assets/mail/layout/text"
                : '@vendor/hiqdev/hisite/src/views/layouts/mail-text',
            'messageConfig' => [
                'from' => [$params['supportEmail'] => $params['organization.name']],
            ],
            'transport' => $params['swiftmailer.smtp.host'] ? [
                'class'     => \Swift_SmtpTransport::class,
                'host'      => $params['swiftmailer.smtp.host'],
                'port'      => $params['swiftmailer.smtp.port'],
            ] : null,
        ], function ($v) { return !is_null($v); }),
    ],
    'container' => [
        'singletons' => [
            \hiam\components\TokenRevokerInterface::class => \hiam\components\ActiveRecordTokenRevoker::class,
        ],
    ],
];

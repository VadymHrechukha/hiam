<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

use vintage\recaptcha\helpers\RecaptchaConfig;

return [
    'hiam.authorizedClients' => array_filter([
        'demo' => $_ENV['ENV'] !== 'PROD' ? 'pass' : null,
    ]),

    'logoUrl'           => '/site/back',

    'poweredBy.name'    => 'HIAM',
    'poweredBy.url'     => 'https://github.com/hiqdev/hiam',

    'organization.name' => '',
    'organization.url'  => '',
    'terms.url'         => '',
    'privacy.policy.url'=> '',

    'supportEmail'      => '',

    'db.host'           => '',
    'db.port'           => '',
    'db.name'           => '',
    'db.user'           => '',
    'db.password'       => '',

    'hiam.access_token.lifetime'    => 3600 * 24,
    'hiam.refresh_token.lifetime'   => 3600 * 24 * 31,

    'user.seller'                   => '',
    'user.loginDuration'            => 3600 * 24 * 31,
    'user.passwordResetTokenExpire' => 3600,
    'user.enableAutoLogin'          => true,
    'user.disableSignup'            => false,
    'user.disableRestorePassword'   => false,
    'user.htmlEmails'               => false,
    'user.checkEmailConfirmed'      => true,

    'user.authKeySecret'        => '',
    'user.authKeyCipher'        => 'aes-128-gcm',

    'swiftmailer.smtp.host'     => null,
    'swiftmailer.smtp.port'     => 25,
    'swiftmailer.smtp.authmode' => null,
    'swiftmailer.smtp.username' => null,
    'swiftmailer.smtp.password' => null,

    RecaptchaConfig::SITE_KEY       => null,
    RecaptchaConfig::PRIVATE_KEY    => null,
];

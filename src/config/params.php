<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2017, HiQDev (http://hiqdev.com/)
 */

return [
    'logoUrl'           => '/site/back',

    'poweredBy.name'    => 'HIAM',
    'poweredBy.url'     => 'https://github.com/hiqdev/hiam',

    'supportEmail'      => '',
    'organizationName'  => '',

    'user.loginDuration'            => 3600 * 24 * 31,
    'user.passwordResetTokenExpire' => 3600,
    'user.enableAutoLogin'          => true,
    'user.disableSignup'            => false,
    'user.disableRestorePassword'   => false,
];

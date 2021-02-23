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
    'PARAMS_LOCATION' => dirname(__DIR__, 4) . '/vendor/yiisoft/composer-config-plugin-output/acceptance.php',
    'YII2_CONFIG_LOCATION' => dirname(__DIR__) . '/tests/acceptance/config/suite.php',

    'COMMON_TESTS_LOCATION' => dirname(__DIR__, 4) . '/tests',
    'COMMON_ACCEPTANCE_SUITE' => dirname(__DIR__, 4) . '/tests/acceptance.suite.yml',

    'URL' => getenv('URL'),
    'BROWSER' => 'chrome',
    'SELENIUM_HOST' => getenv('TESTS_ACCEPTANCE_SELENIUM_HOST'),
];

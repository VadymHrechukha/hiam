<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

use Yiisoft\Composer\Config\Builder;
use yii\web\Application;

(function () {
    require __DIR__ . '/../config/bootstrap.php';

    $host = $_SERVER['HTTP_HOST'];
    $type = (defined('HISITE_TEST') && HISITE_TEST) ? 'web-test' : 'web';
    $path = Builder::path($host . '/' . $type);
    if (!file_exists($path)) {
        $path = Builder::path($type);
    }

    $config = require $path;

    (new Application($config))->run();
})();

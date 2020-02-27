<?php
/**
 * Hiam core package
 *
 * @link      https://id.advancedhosting.com/
 * @package   hiam
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\tests\_support\Helper;

/**
 * Class WaitHelper
 * @package hiam\tests\_support\Helper
 */
class WaitHelper extends \Codeception\Module
{
    /**
     * @param int $timeOut
     * @throws \Codeception\Exception\ModuleException
     */
    public function waitForPageUpdate($timeOut = 180): void
    {
        $I = $this->getModule('WebDriver');

        $I->waitForJS('return $.active == 0;', $timeOut);
    }
}

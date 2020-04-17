<?php


namespace hiam\tests\_support\Helper;


use hiam\tests\_support\AcceptanceTester;
use hiqdev\yii2\mfa\Module;
use yii\base\InvalidArgumentException;

/**
 * Trait TotpHelper
 * @package hiam\tests\_support\Helper
 */
trait TotpHelper
{
    use UserSessionTrait;

    /**
     * @param AcceptanceTester $I
     * @return string
     */
    protected function getTotpAuthCode(AcceptanceTester $I): string
    {
        /** @var Module $module */
        $module = \Yii::$app->getModule('mfa');

        /** @var \hiqdev\yii2\mfa\base\Totp $totp */
        $totp = $module->getTotp();

        /** @var \RobThree\Auth\TwoFactorAuth $worker */
        $worker = $totp->getWorker();

        $session = $this->getUsersSession($I);

        return $worker->getCode($session[$module->paramPrefix . 'totp-tmp-secret'], time());
    }
}

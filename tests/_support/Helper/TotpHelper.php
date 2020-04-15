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
    /**
     * Path to session cache in docker environment
     * @var string
     */
    protected $sessionsPath = '/tmp';

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

        $cookie = $I->grabCookie(ini_get('session.name'));
        $sessionFile = file_get_contents($this->sessionsPath . '/sess_'.$cookie);
        $session = $this->sessionUnseriazlize($sessionFile);

        return $worker->getCode($session[$module->paramPrefix . 'totp-tmp-secret'], time());
    }

    /**
     * @param string $session_data
     * @return array
     */
    private function sessionUnseriazlize(string $session_data): array
    {
        $return_data = [];
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new InvalidArgumentException("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
}

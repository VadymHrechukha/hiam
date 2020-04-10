<?php

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\TotpHelper;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Page\Login;
use hiam\tests\_support\Page\TOTP;
use hiam\tests\_support\Page\Transition;

final class MfaCest
{
    use TotpHelper;

    /**
     * @before loginToLockscreen
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkEnable(AcceptanceTester $I): void
    {
        $I->wantTo('enable TOTP');
        $totpPage = new TOTP($I);
        $totpPage->toEnable();

        $data = [
            'code' => $this->getTotpAuthCode($I),
        ];

        $totpPage->tryFillContactInfo($data);
        $totpPage->tryClickSubmitButton();

        $this->transitionActions($I);
    }

    /**
     * @before loginWithMFA
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkDisable(AcceptanceTester $I): void
    {
        $I->wantTo('disable TOTP');
        $totpPage = new TOTP($I);
        $totpPage->toDisable();

        $data = [
            'code' => $this->getTotpAuthCode($I),
        ];

        $totpPage->tryFillContactInfo($data);
        $totpPage->tryClickSubmitButton();

        $this->transitionActions($I);
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    protected function loginToLockscreen(AcceptanceTester $I): void
    {
        $info = $this->getUserInfo();
        $this->login($I, $info);

        $this->transitionActions($I);
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    protected function loginWithMFA(AcceptanceTester $I): void
    {
        $info = $this->getUserInfo();
        $this->login($I, $info);
        $I->wait(2);

        $totpPage = new TOTP($I);
        $I->see('Two-Factor Authentication');

        $data = [
            'code' => $this->getTotpAuthCode($I),
        ];
        $totpPage->tryFillContactInfo($data);
        $totpPage->tryClickSubmitButton();

        $this->transitionActions($I);
    }

    /**
     * @param AcceptanceTester $I
     * @param array $info
     * @throws \Exception
     */
    protected function login(AcceptanceTester $I, array $info): void
    {
        $loginPage = new Login($I);
        $loginPage->tryFillContactInfo($info);
        $loginPage->tryClickSubmitButton();
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    protected function transitionActions(AcceptanceTester $I): void
    {
        $info = $this->getUserInfo();
        $transitionPage = new Transition($I);
        $transitionPage->baseCheck();

        $lockscreen = new Lockscreen($I);
        $I->waitForText($info['username']);
    }

    /**
     * @return array
     */
    private function getUserInfo(): array
    {
        return [
            'username' => 'hipanel_test_user',
            'password' => 'random',
        ];
    }
}

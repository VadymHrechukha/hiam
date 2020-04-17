<?php

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\TotpHelper;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Helper\BasicHiamActions;
use hiam\tests\_support\Page\TOTP;
use hiam\tests\_support\Page\Transition;

final class MfaCest extends BasicHiamActions
{
    use TotpHelper;

    /** @var string */
    private string $username;

    /** @var string */
    private string $password;

    public function __construct()
    {
        $this->username = uniqid() . 'test@test.test';
        $this->password = 'random';
    }

    /**
     * @before signupToLockscreen
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
    protected function signupToLockscreen(AcceptanceTester $I): void
    {
        $info = $this->getUserInfo();
        $this->doSignupActions($I, $info);

        $this->transitionActions($I);
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    protected function loginWithMFA(AcceptanceTester $I): void
    {
        $info = $this->getUserInfo();
        $this->doLogin($I, $info);

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
     * @inheritDoc
     */
    protected function getUserInfo(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}

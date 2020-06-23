<?php

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\BasicHiamActions;

final class ControllerAccessCest extends BasicHiamActions
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkGuestActionsWhileGuest(AcceptanceTester $I): void
    {
        $I->wantTo('check guest actions while guest');

        try {
            $this->checkTextOnPage($I, [
                'signup'            => 'Sign up',
                'login'             => 'Sign in',
                'reset-password'    => 'Failed reset password',
                'restore-password'  => 'Reset password',
            ]);
        } catch (\Exception $e) {
            $this->checkTextOnPage($I, [
                'restore-password'  => 'RESET PASSWORD',
                'terms'             => 'Terms of Service Agreement',
                'privacy-policy'    => 'PRIVACY POLICY',
            ]);
        }
    }

    /**
     * @before login
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkGuestActionsWhileAuthenticated(AcceptanceTester $I): void
    {
        $I->wantTo('check guest actions while authenticated');
        $actions = ['signup', 'login', 'remote-proceed', 'confirm-password', 'restore-password', 'reset-password'];

        $userInfo = $this->getUserInfo();
        foreach ($actions as $action) {
            $I->amOnPage('/site/' . $action);
            $I->waitForText($userInfo['username']);
        }
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function chechAuthenticatedActionsWhileGuest(AcceptanceTester $I): void
    {
        $I->wantTo('check authenticated actions while guest');
        $actions = ['lockscreen', 'resend-verification-email', 'back', 'change-password', 'change-email'];
        foreach ($actions as $action) {
            $I->amOnPage('/site/' . $action);
            $I->waitForText('Sign in');
        }
    }

    /**
     * @before login
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function chechAuthenticatedActionsWhileAuthenticated(AcceptanceTester $I): void
    {
        $I->wantTo('check authenticated actions while authenticated');
        $usersInfo = $this->getUserInfo();

        $this->checkTextOnPage($I, [
             'lockscreen'                => $usersInfo['username'],
             'resend-verification-email' => 'Please confirm your email address!',
             'back'                      => $usersInfo['username'],
        ]);

        $this->checkElementOnPage($I, [
            'change-password'           => 'form[id=change-password-form]',
            'change-email'              => 'form[id=change-email-form]',
        ]);
    }

    private function checkTextOnPage(AcceptanceTester $I, array $data): void
    {
        foreach ($data as $page => $text) {
            $I->amOnPage('/site/' . $page);
            $I->waitForText($text);
        }
    }
    private function checkElementOnPage(AcceptanceTester $I, array $data): void
    {
        foreach ($data as $page => $text) {
            $I->amOnPage('/site/' . $page);
            $I->waitForElement($text);
        }
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    protected function login(AcceptanceTester $I)
    {
        $this->doLogin($I, $this->getUserInfo());
    }

    /**
     * @inheritDoc
     */
    protected function getUserInfo(): array
    {
        return [
            'username' => 'hipanel_test_user',
            'password' => 'random',
        ];
    }
}

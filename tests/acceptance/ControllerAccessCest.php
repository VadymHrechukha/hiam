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
        $actions = [
            'signup'            => 'Sign up',
            'login'             => 'Sign in',
            'restore-password'  => 'Reset password',
            'reset-password'    => 'Failed reset password',
//            'terms'             => 'Terms of Service Agreement',
//            'privacy-policy'    => 'PRIVACY POLICY',
        ];
        foreach ($actions as $action => $text) {
            $I->amOnPage('/site/' . $action);
            $I->waitForText($text);
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

        foreach ([
             'lockscreen'                => $usersInfo['username'],
             'resend-verification-email' => 'Please confirm your email address!',
             'back'                      => $usersInfo['username'],
        ] as $action => $text) {
            $I->amOnPage('/site/' . $action);
            $I->waitForText($text);
        }

        foreach ([
            'change-password'           => 'form[id=change-password-form]',
            'change-email'              => 'form[id=change-email-form]',
        ] as $action => $text) {
            $I->amOnPage('/site/' . $action);
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

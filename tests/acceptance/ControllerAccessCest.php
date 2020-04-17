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
//    public function checkGuestActionsWhileGuest(AcceptanceTester $I): void
//    {
//        $I->wantTo('check authenticated actions while guest');
//        $actions = [
//            'signup', 'login', 'remote-proceed',
//            'confirm-password', 'restore-password', 'reset-password',
//            'terms', 'privacy-policy',
//        ];
//        foreach ($actions as $action) {
//            $I->amOnPage('/site/' . $action);
//            $I->wait(3);
//        }
//    }
//
//    /**
//     * @before login
//     * @param AcceptanceTester $I
//     * @throws \Exception
//     */
//    public function checkGuestActionsWhileAuthenticated(AcceptanceTester $I): void
//    {
//        $I->wantTo('check authenticated actions while guest');
//        $actions = [
//            'signup', 'login', 'remote-proceed',
//            'confirm-password', 'restore-password', 'reset-password',
//            'terms', 'privacy-policy',
//        ];
//        foreach ($actions as $action) {
//            $I->amOnPage('/site/' . $action);
//            $I->wait(3);
//        }
//    }







    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
//    public function chechAuthenticatedActionsWhileGuest(AcceptanceTester $I): void
//    {
//        $I->wantTo('check authenticated actions while guest');
//        $actions = [
//            'lockscreen',
//            'resend-verification-email', 'back',
//            'change-password', 'change-email',
//        ];
//        foreach ($actions as $action) {
//            $I->amOnPage('/site/' . $action);
//            $I->see('Sign in');
//        }
//    }
//
//    /**
//     * @param AcceptanceTester $I
//     * @throws \Exception
//     */
//    public function chechAuthenticatedActionsWhileAuthenticated(AcceptanceTester $I): void
//    {
//        $I->wantTo('check authenticated actions while guest');
//        $actions = [
//            'lockscreen',
//            'resend-verification-email', 'back',
//            'change-password', 'change-email',
//        ];
//        foreach ($actions as $action) {
//            $I->amOnPage('/site/' . $action);
//            $I->see('Sign in');
//        }
//    }

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

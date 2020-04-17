<?php

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\BasicHiamActions;
use hiam\tests\_support\Helper\TokenHelper;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Page\Transition;

class ConfirmEmailCest extends BasicHiamActions
{
    /**
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkEmailConfirm(AcceptanceTester $I): void
    {
        $I->wantTo('check email confirm');
        [$user,] = $this->getUserInfo();

        $this->doSignupActions($I, $user);
        $this->doEmailConfirmCheck($I, $user, 'confirm-sign-up-email');

        $lockscreen = new Lockscreen($I);
        $I->see($user['username']);
    }

    /**
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkEmailConfirmAfterLogout(AcceptanceTester $I): void
    {
        $I->wantTo('check email confirm after logout');
        [$user,] = $this->getUserInfo();

        $this->doSignupActions($I, $user);
        $this->doLogout($I);
        $this->doEmailConfirmCheck($I, $user, 'confirm-sign-up-email');

        $lockscreen = new Lockscreen($I);
        $I->see('Sign in to Advanced Hosting');
    }

    /**
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkEmailConfirmWhenAnotherUserIsLoggedIn(AcceptanceTester $I): void
    {
        $I->wantTo('check email confirm when another user is logged in');
        [$user1, $user2] = $this->getUserInfo();

        $this->doSignupActions($I, $user1);
        $this->doLogout($I);
        $this->doSignupActions($I, $user2);
        $this->doEmailConfirmCheck($I, $user2, 'confirm-sign-up-email');

        $lockscreen = new Lockscreen($I);
        $I->waitForText($user2['username']);
    }

    /**
     * @param AcceptanceTester $I
     * @param array $user
     * @param string $action
     * @throws \Exception
     */
    private function doEmailConfirmCheck(AcceptanceTester $I, array $user, string $action): void
    {
        $token = TokenHelper::findTokenByActionAndName($action, $user['username']);
        $I->assertNotEmpty($token, 'token exists');
        $I->amOnPage("/site/$action?token=$token");

        $transitionPage = new Transition($I);
        $I->waitForText('Your email was confirmed!');
        $transitionPage->baseCheck();
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    private function doLogout(AcceptanceTester $I): void
    {
        $lockscreenPage = new Lockscreen($I);
        $lockscreenPage->tryLogout();
    }

    /**
     * @inheritDoc
     */
    protected function getUserInfo(): array
    {
        return [
            [
                'username' => uniqid() . 'test@test.test',
                'password' => 'password',
            ],
            [
                'username' => uniqid() . 'test1@test1.test1',
                'password' => 'password1',
            ],
        ];
    }
}

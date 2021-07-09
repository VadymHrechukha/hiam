<?php

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\BasicHiamActions;
use hiam\tests\_support\Helper\TokenHelper;
use hiam\tests\_support\Helper\UserSessionTrait;
use hiam\tests\_support\Page\ChangeEmail;
use hiam\tests\_support\Page\ChangePassword;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Page\Transition;

final class ChangeRoutineActionsCest extends BasicHiamActions
{
    use UserSessionTrait;

    /**
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkChangePassword(AcceptanceTester $I): void
    {
        $I->wantTo('change password');

        $info = $this->getUserInfo();
        $this->doSignupActions($I, $info);

        $changePassword = new ChangePassword($I);
        $changePassword->tryFillContactInfo($info);
        $changePassword->tryClickSubmitButton();

        $transition = new Transition($I);
        $I->waitForText('Password has been successfully changed');
        $transition->baseCheck();

        $info['password'] = $info['new_password'];
        $this->doLogin($I, $info);
    }

    /**
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkChangeEmail(AcceptanceTester $I): void
    {
        $I->wantTo('change email');

        $info = $this->getUserInfo();
        $this->doSignupActions($I, $info);

        $changeEmail = new ChangeEmail($I);
        $changeEmail->tryFillContactInfo($info);
        $changeEmail->tryClickSubmitButton();

        $transition = new Transition($I);
        $I->waitForText('Email has been successfully changed');
        $transition->baseCheck();

        $this->doEmailConfirmCheck($I, $info, 'confirm-email');

        $lockscreen = new Lockscreen($I);
        $changeEmail->tryLogout();

        $info['username'] = $info['new_username'];
        $this->doLogin($I, $info);
    }

    /**
     * @inheritDoc
     */
    protected function doLogin(AcceptanceTester $I, array $info): void
    {
        parent::doLogin($I, $info);

        $lockscreen = new Lockscreen($I);
        $I->waitForText($info['username']);
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

        codecept_debug(['CURRENT_SESSION' => $this->getUsersSession($I)]);
        codecept_debug(['ALL_SESSIONS' => $this->getAllSessions()]);

        $I->amOnPage("/site/$action?token=$token");

        $transitionPage = new Transition($I);
        $I->waitForText('Your email was confirmed!');
        $transitionPage->baseCheck();
    }

    /**
     * @inheritDoc
     */
    protected function getUserInfo(): array
    {
        return [
            'username' => uniqid() . 'test@test.test',
            'new_username'=> uniqid() . 'new_test_user@test.test',
            'password' => 'random',
            'new_password' => 'random1',
        ];
    }
}

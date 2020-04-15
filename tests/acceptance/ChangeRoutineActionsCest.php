<?php

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\BasicHiamActions;
use hiam\tests\_support\Helper\TokenHelper;
use hiam\tests\_support\Page\ChangeEmail;
use hiam\tests\_support\Page\ChangePassword;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Page\Login;
use hiam\tests\_support\Page\SignUp;
use hiam\tests\_support\Page\Transition;
use yii\helpers\FileHelper;

final class ChangeRoutineActionsCest extends BasicHiamActions
{
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

        $lockscreen = new Lockscreen($I);
        $lockscreen->tryLogout();

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

        $changePassword = new ChangeEmail($I);
        $changePassword->tryFillContactInfo($info);
        $changePassword->tryClickSubmitButton();

        $transition = new Transition($I);
        $I->waitForText('Email has been successfully changed');
        $transition->baseCheck();

        $this->doEmailConfirmCheck($I);

        $lockscreen = new Lockscreen($I);
        $lockscreen->tryLogout();

        $info['username'] = $info['new_username'];
        $this->doLogin($I, $info);
    }

    /**
     * @inheritDoc
     */
    protected function doLogin(AcceptanceTester $I, array $info): void
    {
        parent::doLogin($I, $info);

//        $lockscreen = new Lockscreen($I);
//        $I->waitForText($info['username']);
    }

    /**
     * @param AcceptanceTester $I
     * @param array $user
     * @throws \Exception
     */
    private function doEmailConfirmCheck(AcceptanceTester $I): void
    {
        $token = TokenHelper::findLastToken();
        $I->assertNotEmpty($token, 'token exists');
        $I->amOnPage('/site/confirm-email?token=' . $token);
        $I->waitForText('Your email was confirmed!');
    }

    /**
     * @return array
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

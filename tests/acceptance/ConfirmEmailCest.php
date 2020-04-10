<?php

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\TokenHelper;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Page\SignUp;
use hiam\tests\_support\Page\Transition;
use yii\helpers\FileHelper;

class ConfirmEmailCest
{
    /**
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function checkEmailConfirm(AcceptanceTester $I): void
    {
        $I->wantTo('check email confirm');
        [$user,] = $this->getUsersInfo();

        $this->doSignupActions($I, $user);
        $this->doEmailConfirmCheck($I);

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
        [$user,] = $this->getUsersInfo();

        $this->doSignupActions($I, $user);
        $this->doLogout($I);
        $this->doEmailConfirmCheck($I);

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
        [$user1, $user2] = $this->getUsersInfo();

        $this->doSignupActions($I, $user1);
        $this->doLogout($I);
        $this->doSignupActions($I, $user2);
        $this->doEmailConfirmCheck($I);

        $lockscreen = new Lockscreen($I);
        $I->waitForText($user2['username']);
    }

    /**
     * @param AcceptanceTester $I
     * @param array $user
     * @throws \Exception
     */
    private function doSignupActions(AcceptanceTester $I, array $user): void
    {
        $signupPage = new SignUp($I);
        $signupPage->tryFillContactInfo($user);
        $signupPage->tryClickAdditionalCheckboxes();
        $signupPage->tryClickAgreeTermsPrivacy();
        $signupPage->tryClickSubmitButton();
        $I->waitForPageUpdate();

        $transitionPage = new Transition($I);
        $transitionPage->baseCheck();

        $lockscreen = new Lockscreen($I);
        $I->waitForText($user['username']);
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
        $I->amOnPage('/site/confirm-sign-up-email?token=' . $token);
    }

    private function doLogout(AcceptanceTester $I): void
    {
        $lockscreenPage = new Lockscreen($I);
        $lockscreenPage->tryLogout();
    }

    protected function cleanUp(AcceptanceTester $I): void
    {
        try {
            FileHelper::removeDirectory($I->getMailsDir());
            FileHelper::removeDirectory(TokenHelper::getTokensDir());
        } catch (\Throwable $exception) {
            // seems to be already removed. it's fine
        }
    }

    /**
     * @return array
     */
    private function getUsersInfo(): array
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

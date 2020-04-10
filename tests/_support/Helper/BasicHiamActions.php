<?php


namespace hiam\tests\_support\Helper;


use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Page\Login;
use hiam\tests\_support\Page\SignUp;
use hiam\tests\_support\Page\Transition;
use yii\helpers\FileHelper;

abstract class BasicHiamActions
{
    /**
     * @param AcceptanceTester $I
     * @param array $user
     * @throws \Exception
     */
    protected function doSignupActions(AcceptanceTester $I, array $user): void
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
     * @param array $info
     * @throws \Exception
     */
    protected function doLogin(AcceptanceTester $I, array $info): void
    {
        $loginPage = new Login($I);
        $loginPage->tryFillContactInfo($info);
        $loginPage->tryClickSubmitButton();
        $I->wait(1);

        $lockscreen = new Lockscreen($I);
        $I->waitForText($info['username']);
    }

    /**
     * @param AcceptanceTester $I
     */
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
    abstract protected function getUserInfo(): array;
}

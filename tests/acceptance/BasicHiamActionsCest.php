<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;
use hiam\tests\_support\Helper\BasicHiamActions;
use hiam\tests\_support\Helper\TokenHelper;
use hiam\tests\_support\Page\Lockscreen;
use hiam\tests\_support\Page\ResetPassword;
use hiam\tests\_support\Page\RestorePassword;
use hiam\tests\_support\Page\Transition;

class BasicHiamActionsCest extends BasicHiamActions
{
    /** @var string */
    private string $username;

    /** @var string */
    private string $password = '123456';

    /** @var string */
    private string $identity;

    public function __construct()
    {
        $this->username = mt_rand(100000, 999999) . '+testuser@example.com';
    }

    /**
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function signup(AcceptanceTester $I): void
    {
        $I->wantTo('signup to hiam');
        $info = $this->getUserInfo();
        $this->doSignupActions($I, $info);
    }

    /**
     * @before cleanUp
     * @depends signup
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function login(AcceptanceTester $I): void
    {
        $I->wantTo('login to hiam');
        $info = $this->getUserInfo();
        $this->doLogin($I, $info);

        $transitionPage = new Transition($I);
        $transitionPage->baseCheck();

        $lockscreen = new Lockscreen($I);
        $I->waitForText($info['username']);

        $this->identity = $I->grabCookie('_identity');
        $I->assertNotEmpty($this->identity, 'cookie grabbed');
    }

    /**
     * @depends login
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function logout(AcceptanceTester $I): void
    {
        $I->wantTo('Logout from hiam');
        $I->setCookie('_identity', $this->identity);
        $lockscreenPage = new Lockscreen($I);
        $I->see($this->username);
        $lockscreenPage->tryLogout();
        $I->see('Sign in');
    }

    /**
     * @depends logout
     * @before cleanUp
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function restorePassword(AcceptanceTester $I): void
    {
        $I->wantTo('Restore password');
        $restorePasswordPage = new RestorePassword($I);
        $info = $this->getUserInfo();
        $restorePasswordPage->tryFillContactInfo($info);
        $restorePasswordPage->tryClickSubmitButton();
        $I->waitForPageUpdate();
        $resetTokenLink = TokenHelper::getTokenUrl($I);
        $resetPasswordPage = new ResetPassword($I, $resetTokenLink);
        $resetPasswordPage->tryFillContactInfo($info);
        $resetPasswordPage->tryClickSubmitButton();
        $I->waitForText('New password was saved.');
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

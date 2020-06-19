<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\tests\_support\Page;

use hiam\tests\_support\AcceptanceTester;

/**
 * Class SignUp
 * @package hiam\tests\_support\Page
 */
class SignUp extends AbstractHiamPage
{
    /**
     * SignUp constructor.
     * @param AcceptanceTester $I
     */
    public function __construct(AcceptanceTester $I)
    {
        parent::__construct($I);
        $I->amOnPage('/site/signup');
    }

    /**
     * @inheritDoc
     */
    public function tryFillContactInfo(array $info): void
    {
        $I = $this->tester;
        $I->fillField(['name' => 'SignupForm[email]'], $info['username']);
        $I->fillField(['name' => 'SignupForm[password]'], $info['password']);
        try {
            $I->fillField(['name' => 'SignupForm[first_name]'], $info['username']);
            $I->fillField(['name' => 'SignupForm[last_name]'], $info['username']);
            $I->fillField(['name' => 'SignupForm[password_retype]'], $info['password']);
        } catch (\Exception $e) {
        }
    }

    /**
     * @throws \Exception
     */
    public function tryClickAdditionalCheckboxes(): void
    {
        $I = $this->tester;
        try {
            $I->clickWithLeftButton(['css' => '.field-signupform-i_agree']);
            $I->clickWithLeftButton(['css' => '.field-signupform-i_agree_privacy_policy']);
        } catch (\Exception $e) {
        }
    }

    /**
     * @throws \Exception
     */
    public function tryClickAgreeTermsPrivacy(): void
    {
        $I = $this->tester;
        try {
            $I->clickWithLeftButton(['css' => 'label[for=\'i_agree_terms_and_privacy-email\']']);
        } catch (\Exception $e) {
        }
        try {
            $I->clickWithLeftButton(['css' => 'input[name*=i_agree_terms_and_privacy][type=checkbox]']);
        } catch (\Exception $e) {
        }
    }
}

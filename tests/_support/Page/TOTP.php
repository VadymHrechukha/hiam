<?php


namespace hiam\tests\_support\Page;

/**
 * Class TOTP
 * @package hiam\tests\_support\Page
 */
class TOTP extends AbstractHiamPage
{
    public function toEnable(): void
    {
        $this->tester->amOnPage('/mfa/totp/enable');
    }

    public function toDisable(): void
    {
        $this->tester->amOnPage('/mfa/totp/disable');
    }

    /**
     * @inheritDoc
     */
    public function tryFillContactInfo(array $info): void
    {
        $this->tester->fillField(['name' => 'InputForm[code]'], $info['code']);
    }
}

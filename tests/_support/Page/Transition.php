<?php


namespace hiam\tests\_support\Page;


use hiam\tests\_support\AcceptanceTester;

/**
 * Class Transition
 * @package hiam\tests\_support\Page
 */
class Transition
{
    public const PAGE_DELAY = 3;

    /**
     * @var AcceptanceTester
     */
    private $tester;

    /**
     * Transition constructor.
     * @param AcceptanceTester $I
     */
    public function __construct(AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function baseCheck()
    {
        $this->tester->wait(self::PAGE_DELAY);
    }
}

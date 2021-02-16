<?php
declare(strict_types=1);

namespace hiam\event;

use yii\base\Event;

class BeforeEmailConfirmedEvent extends Event
{
    public string $newEmail;
}

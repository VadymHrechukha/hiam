<?php
declare(strict_types=1);

namespace hiam\event;

use hiam\models\ProxyModel;
use yii\base\Event;

class BeforeEmailConfirmedEvent extends Event
{
    public ProxyModel $user;

    public string $newEmail;
}

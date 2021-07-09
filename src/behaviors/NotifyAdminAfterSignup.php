<?php
declare(strict_types=1);

namespace hiam\behaviors;

use hiam\base\User;
use Yii;
use yii\base\Behavior;
use yii\mail\MailerInterface;
use yii\web\UserEvent;

class NotifyAdminAfterSignup extends Behavior
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer, $config = [])
    {
        parent::__construct($config);

        $this->mailer = $mailer;
    }

    public function events()
    {
        return [
            User::EVENT_AFTER_SIGNUP => 'afterSignup',
        ];
    }

    public function afterSignup(UserEvent $event)
    {
        $params = Yii::$app->params;

        return $this->mailer->compose()
            ->renderHtmlBody('userSignup', ['user' => $event->identity])
            ->setTo($params['signupEmail'] ?? $params['supportEmail'] ?? $params['adminEmail'])
            ->send();
    }
}

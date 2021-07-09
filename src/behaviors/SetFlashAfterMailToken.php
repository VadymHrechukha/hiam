<?php
declare(strict_types=1);

namespace hiam\behaviors;

use hiam\actions\ConfirmEmail;
use hiam\models\Identity;
use hiqdev\yii2\confirmator\Event\AfterMailTokenEvent;
use hiqdev\yii2\confirmator\Service;
use Yii;
use yii\base\Behavior;
use yii\web\Session;

class SetFlashAfterMailToken extends Behavior
{
    private Session $session;

    public function __construct(Session $session, $config = [])
    {
        parent::__construct($config);

        $this->session = $session;
    }

    public function events()
    {
        return [
            Service::EVENT_AFTER_MAIL_TOKEN => 'afterMailToken',
        ];
    }

    public function afterMailToken(AfterMailTokenEvent $event): void
    {
        if (!in_array($event->action, ['confirm-sign-up-email', 'restore-password', 'confirm-email'])) {
            return;
        }

        $handler = $this->provideHandler($event->action);

        $handler($event->identity);
    }

    private function provideHandler(string $action): \Closure
    {
        if ($action === 'restore-password') {
            return function (Identity $identity) {
                $this->session->setFlash(
                    'success',
                    Yii::t('hiam', 'Check your email {maskedMail} for further instructions.'),
                    ['m' => $identity->getMaskedEmail()]
                );
            };
        }

        return function (Identity $identity) {
            $this->session->set(ConfirmEmail::SESSION_VAR_NAME, $identity->email);
            $this->session->setFlash('warning',  Yii::t('hiam', 'Please confirm your email address!') . '<br/>' .
                Yii::t(
                    'hiam',
                    'An email with confirmation instructions was sent to <b>{email}</b>',
                    ['email' => $identity->email_confirmed ?? $identity->email]
                ),
            );
        };
    }
}

<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\behaviors;

use hiam\base\User;
use Yii;
use yii\web\UserEvent;

/**
 * CheckEmailConfirmed behavior for the [\yii\web\User] component
 * Prevents login if user email is not confirmed
 */
class CheckEmailConfirmed extends \yii\base\Behavior
{
    public function events()
    {
        return [
            User::EVENT_BEFORE_LOGIN => 'beforeLogin',
        ];
    }

    public function beforeLogin(UserEvent $event)
    {
        $identity = $event->identity;

        if ($event->cookieBased
            || $identity->isEmailConfirmed()
            || $identity->state === 'ok'
            || empty($identity->email)
        ) {
            return;
        }

        Yii::$app->session->setFlash('warning',
            Yii::t('hiam', 'Please confirm your email address!') . '<br/>' .
            Yii::t('hiam', 'An email with confirmation instructions was sent to <b>{email}</b>', ['email' => $identity->email])
        );

        Yii::$app->response->redirect(Yii::$app->getHomeUrl());
        Yii::$app->end();
    }
}

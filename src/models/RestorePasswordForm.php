<?php

/*
 * Identity and Access Management server providing OAuth2, RBAC and logging
 *
 * @link      https://github.com/hiqdev/hiam-core
 * @package   hiam-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2016, HiQDev (http://hiqdev.com/)
 */

namespace hiam\models;

use Yii;

/**
 * Password password form.
 */
class RestorePasswordForm extends \yii\base\Model
{
    public $email;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => Yii::t('hiam', 'Email'),
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        $user = Yii::$app->user->findByEmail($this->email);

        if (!$user) {
            return false;
        }

        if (Yii::$app->has('authManager')) {
            $auth = Yii::$app->authManager;
            if ($auth->getItem('restore-password') && !$auth->checkAccess($user->id, 'restore-password')) {
                return false;
            }
        }

        $token = Yii::$app->confirmator->issueToken([
            'action'    => 'restore-password',
            'email'     => $this->email,
            'username'  => $user->username,
            'notAfter'  => '+ 3 days',
        ])->toString();

        return Yii::$app->mailer->compose()
            ->renderHtmlBody('passwordResetToken', compact('user', 'token'))
            ->setTo($this->email)
            ->send()
        ;
    }
}

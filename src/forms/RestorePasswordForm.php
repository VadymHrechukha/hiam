<?php

/*
 * Identity and Access Management server providing OAuth2, RBAC and logging
 *
 * @link      https://github.com/hiqdev/hiam-core
 * @package   hiam-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2016, HiQDev (http://hiqdev.com/)
 */

namespace hiam\forms;

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
}
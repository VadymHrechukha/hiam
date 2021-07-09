<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\forms;

use hiam\validators\LoginValidatorInterface;
use hiam\validators\PasswordValidatorInterface;
use Yii;
use yii\base\Model;

/**
 * Login form.
 */
class LoginForm extends Model
{
    public $username;

    public $password;

    public $remember_me = true;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'strtolower'],
            ['username', LoginValidatorInterface::class],
            ['password', PasswordValidatorInterface::class],
            [['username', 'password'], 'trim'],
            [['username', 'password'], 'required'],
            ['remember_me', 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('hiam', 'Login or Email'),
            'password' => Yii::t('hiam', 'Password'),
            'remember_me' => Yii::t('hiam', 'Remember me'),
        ];
    }
}

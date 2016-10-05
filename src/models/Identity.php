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

use filsh\yii2\oauth2server\models\OauthAccessTokens;
use OAuth2\Storage\UserCredentialsInterface;
use Yii;
use yii\web\IdentityInterface;

/**
 * Identity model.
 *
 * @property integer $id
 * @property string $type
 * @property string $name
 * @property string $state
 * @property string $email
 * @property string $seller
 * @property string $password
 * @property string $username
 * @property string $last_name
 * @property string $first_name
 * @property string $auth_key
 */
class Identity extends \yii\base\Model implements IdentityInterface, UserCredentialsInterface
{
    public $id;
    public $type;
    public $name;
    public $state;
    public $email;
    public $seller;
    public $password;
    public $username;
    public $last_name;
    public $first_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['id',              'integer'],
            ['seller_id',       'integer'],

            ['name',            'trim'],
            ['name',            'string', 'min' => 2, 'max' => 64],

            ['username',        'trim'],
            ['username',        'string', 'min' => 2, 'max' => 64],

            ['seller',          'trim'],
            ['seller',          'string', 'min' => 2, 'max' => 64],

            ['email',           'trim'],
            ['email',           'email'],

            ['password',        'trim'],
            ['password',        'string', 'min' => 2, 'max' => 64],

            [['type', 'state'], 'string', 'min' => 2, 'max' => 10],
        ];
    }

    public function getUserDetails($username)
    {
        $data = $this->findByUsername($username)->toArray();
        if (empty($data)) {
            return false;
        }

        $data['user_id'] = $data['id'];
        return $data;
    }

    public function checkUserCredentials($username, $password)
    {
        $check = $this->findByUsername($username, $password);

        return (bool) $check->id;
    }

    public static function findByUsername($username, $password = null)
    {
        $cond = ['username' => $username];
        if ($password) {
            $cond['password'] = $password;
        }

        return static::findOne($cond);
    }

    public static function findByEmail($email)
    {
        return static::findOne(compact('email'));
    }

    public static function findOne($cond)
    {
        $class = static::getStorageClass();
        $store = $class::findOne($cond);

        if (!$store) {
            return null;
        }

        $model = new static;
        $model->setAttributes($store->getAttributes($model->attributes()));

        return $model;
    }

    public function save()
    {
        $store = static::findByUsername($this->username);
        if (!$store) {
            $class = static::getStorageClass();
            $store = new $class;
        }
        $store->setAttributes($this->getAttributes());

        return $store->save();
    }

    public static function getStorageClass()
    {
        return Yii::$app->user->storageClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findByUsername($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($access_token, $type = null)
    {
        $token = OauthAccessTokens::findOne(compact('access_token'));

        return static::findByUsername($token->user_id);
    }

    /**
     * Finds user through RemoteUser.
     */
    public static function findIdentityByAuthClient($client)
    {
        $provider   = RemoteUser::toProvider($client->getId());
        if (!$provider) {
            return null;
        }
        $attributes = $client->getUserAttributes();
        $remoteid   = $attributes['id'];
        $email      = $attributes['email'];
        $remote     = RemoteUser::findOne(compact('provider', 'remoteid'));
        if ($remote) {
            return static::findByUsername($remote->client_id);
        }
        $user = static::findByEmail($email);
        if (!$user) {
            return null;
        }
        if (RemoteUser::isTrustedEmail($provider, $email)) {
            return RemoteUser::set($client, $user);
        }

        return null;
    }

    /**
     * Finds out if password reset token is valid.
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return 'DUMMY';
        //return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password.
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        //die(var_dump( Yii::$app->security->validatePassword($password, $this->password_hash)));
        //return Yii::$app->security->validatePassword($password, $this->password_hash);
        $model = static::findByUsername($this->username, $password);
        return (bool) $model->id;
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        //$this->auth_key = Yii::$app->security->generateRandomString();
        $this->auth_key = 'DUMMY';
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}
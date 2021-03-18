<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\models;

use filsh\yii2\oauth2server\models\OauthAccessTokens;
use hiam\components\AuthKeyGenerator;
use hiqdev\yii2\mfa\base\ApiMfaIdentityInterface;
use hiqdev\yii2\mfa\base\MfaIdentityInterface;
use OAuth2\Storage\UserCredentialsInterface;
use Yii;
use yii\helpers\StringHelper;
use yii\web\IdentityInterface;

/**
 * Identity model.
 *
 * @property integer $id
 * @property string $type
 * @property string $state
 * @property string $email
 * @property string $password
 * @property string $username
 * @property string $last_name
 * @property string $first_name
 */
class Identity
    extends ProxyModel
    implements MfaIdentityInterface, ApiMfaIdentityInterface, UserCredentialsInterface
{
    public $id;
    public $type;
    public $state;
    public $email;
    public $password;
    public $username;
    public $last_name;
    public $first_name;
    public $password_hash;

    public $allowed_ips;
    public $totp_secret;
    public $tmp_totp_secret;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['id',              'integer'],

            [['username', 'email', 'password', 'first_name', 'last_name'], 'filter', 'filter' => 'trim'],
            [['username', 'email'], 'filter', 'filter' => 'strtolower'],
            [['username', 'password', 'first_name', 'last_name'], 'string', 'min' => 2, 'max' => 64],
            ['email', 'email'],

            [['type', 'state'], 'string', 'min' => 2, 'max' => 10],

            ['allowed_ips',     'string'],
            [['totp_secret', 'tmp_totp_secret'],     'string'],

            ['password_hash',        'string'],
        ];
    }

    public function getName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * This function is called from OAuth2 server implementation.
     * @param string $username
     * @return array|false user data array or false if not found
     */
    public function getUserDetails($username)
    {
        $data = $this->findIdentityByUsername($username)->toArray();
        if (empty($data)) {
            return false;
        }

        $data['user_id'] = $data['id'];

        return $data;
    }

    public function checkUserCredentials($username, $password)
    {
        $check = $this->findIdentityByCredentials($username, $password);

        return (bool) $check->id;
    }

    /**
     * Finds an identity by the given ID.
     * @param string|integer $id
     * @return IdentityInterface|null the identity object that matches the given ID or null if not found
     */
    public static function findIdentity($id)
    {
        return static::findActive(['id' => $id]);
    }

    /**
     * Finds an identity by the given credentials.
     * @param string $username
     * @param string $password
     * @return IdentityInterface|null the identity object that matches the given credentials.
     */
    public static function findIdentityByCredentials($username, $password)
    {
        return static::findActive([
            'username' => $username,
            'password' => $password,
        ]);
    }

    public static function findIdentityByEmail($email)
    {
        return static::findActive(['email' => $email]);
    }

    public static function findIdentityByUsername($username)
    {
        return static::findActive(['username' => $username]);
    }

    /**
     * This function is here for redifining to change behaviour.
     */
    public static function findActive($cond)
    {
        return static::findOne($cond);
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($access_token, $type = null)
    {
        $token = OauthAccessTokens::findOne(['access_token' => $access_token]);

        return static::findIdentity($token->user_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AuthKeyGenerator
     */
    protected function getAuthKeyGenerator()
    {
        return Yii::createObject(AuthKeyGenerator::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->getAuthKeyGenerator()->generateForUser($this->id, $this->password_hash);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKeyGenerator()->validateForUser($this->id, $this->password_hash, $authKey);
    }

    /**
     * Validates password.
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $model = static::findIdentityByCredentials($this->username, $password);

        return (bool) $model->id;
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * This function is here for redifining to change behaviour.
     * @see beforeLogin
     */
    public function isEmailConfirmed()
    {
        return true;
    }

    public function setConfirmedEmail(string $email)
    {
        return true;
    }

    public function setNewUnconfirmedEmail(string $newEmail): bool
    {
        return true;
    }

    public function changePassword(string $newPassword): bool
    {
        $this->password = $newPassword;

        return true;
    }

    public function __sleep()
    {
        return $this->attributes();
    }

    /**
     * @inheritDoc
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getTotpSecret(): string
    {
        return $this->totp_secret ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getAllowedIps(): array
    {
        return array_map('trim', StringHelper::explode($this->allowed_ips));
    }

    /**
     * @inheritDoc
     */
    public function setTotpSecret(string $secret)
    {
        $this->totp_secret = $secret;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addAllowedIp(string $allowedIp)
    {
        $this->allowed_ips .= ($this->getAllowedIps() ? ',' : '') . $allowedIp;

        return $this;
    }

    public function getTemporarySecret(): ?string
    {
        return $this->tmp_totp_secret;
    }

    public function setTemporarySecret(?string $secret)
    {
        $this->tmp_totp_secret = $secret;

        return $this;
    }
}

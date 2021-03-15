<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\base;

use hiam\forms\ChangeEmailForm;
use hiam\forms\ChangePasswordForm;
use hiam\models\Identity;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\InvalidConfigException;
use yii\web\IdentityInterface;
use yii\web\Session;

class User extends \yii\web\User
{
    public $storageClasses = [];

    public $remoteUserClass;

    public $disableSignup = false;

    public $disableRestorePassword = false;

    public $loginDuration = 3600 * 24 * 31;

    private $session;

    public function __construct(Session $session, $config = [])
    {
        parent::__construct($config);

        $this->session = $session;
    }

    public function login(IdentityInterface $identity, $duration = null)
    {
        return parent::login($identity, $duration ?? $this->loginDuration);
    }

    public function logout($destroySession = true)
    {
        $backUrl = $this->session->get($this->returnUrlParam);
        $res = parent::logout($destroySession);
        $this->session->set($this->returnUrlParam, $backUrl);

        return $res;
    }

    /**
     * Registers new user.
     * @return Identity|null the saved identity or null if saving fails
     */
    public function signup($model)
    {
        if (!$model->validate()) {
            return null;
        }
        $class = $this->identityClass;
        $user = Yii::createObject($class);
        $user->setAttributes($model->getAttributes());
        $user->username = $model->username ?? $model->email;

        if ($user->save()) {
            $this->notifySignup($user);
            $this->login($user);

            return $user;
        }

        return null;
    }

    protected function notifySignup($user)
    {
        $params = Yii::$app->params;

        return Yii::$app->mailer->compose()
            ->renderHtmlBody('userSignup', compact('user'))
            ->setTo($params['signupEmail'] ?? $params['supportEmail'] ?? $params['adminEmail'])
            ->send();
    }

    /**
     * @return Identity|null
     */
    public function findIdentity($id)
    {
        $class = $this->identityClass;

        return $class::findIdentity($id);
    }

    /**
     * @return Identity|null
     */
    public function findIdentityByCredentials($username, $password)
    {
        $class = $this->identityClass;

        return $class::findIdentityByCredentials($username, $password);
    }

    /**
     * @return Identity|null
     */
    public function findIdentityByEmail($email)
    {
        $class = $this->identityClass;

        return $class::findIdentityByEmail($email);
    }

    /**
     * @return Identity|null
     */
    public function findIdentityByUsername($username)
    {
        $class = $this->identityClass;

        return $class::findIdentityByUsername($username);
    }

    /**
     * Finds user through RemoteUser.
     * @return Identity
     */
    public function findIdentityByAuthClient(ClientInterface $client)
    {
        $remote = $this->getRemoteUser($client);
        if (!$remote->provider) {
            return null;
        }
        if ($remote->client_id) {
            return $this->findIdentity($remote->client_id);
        }
        $email = $client->getUserAttributes()['email'];
        $user = $this->findIdentityByEmail($email);
        if (!$user) {
            return null;
        }
        if ($remote->isTrustedEmail($email)) {
            return $this->setRemoteUser($client, $user);
        }

        return null;
    }

    /**
     * Inserts or updates RemoteUser.
     * @return IdentityInterface user
     */
    public function setRemoteUser(ClientInterface $client, IdentityInterface $user)
    {
        $model = $this->getRemoteUser($client);
        $model->client_id = $user->getId();
        $model->save();

        return $user;
    }

    public function getRemoteUser(ClientInterface $client)
    {
        $class = $this->remoteUserClass;

        return $class::findOrCreate($client->getId(), $client->getUserAttributes()['id']);
    }

    public function getStorageClass($name)
    {
        if ($name === $this->identityClass) {
            $name = 'identity';
        } elseif ($name === $this->remoteUserClass) {
            $name = 'remoteUser';
        }
        if (!isset($this->storageClasses[$name])) {
            throw new InvalidConfigException("not configured storage class for $name");
        }

        return $this->storageClasses[$name];
    }

    public function changePassword(ChangePasswordForm $model): bool
    {
        if (!$model->validate()) {
            return false;
        }
        $user = $this->findIdentityByUsername($model->login);
        $user->password = $model->new_password;
        if ($user->save()) {
            return true;
        }

        return false;
    }

    public function changeEmail(ChangeEmailForm $model): bool
    {
        if (!$model->validate()) {
            return false;
        }
        $class = $this->getStorageClass('identity');
        $user = $class::find()->whereId(Yii::$app->user->id)->one();

        return $user->updateEmail($model->email);
    }
}

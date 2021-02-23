<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\actions;

use hiam\event\BeforeEmailConfirmedEvent;
use hiqdev\php\confirmator\ServiceInterface;
use hiqdev\php\confirmator\Token;
use Yii;
use yii\base\Action;
use yii\web\Session;
use yii\web\User;

/**
 * Class ConfirmEmail
 * @package hiam\actions
 *
 * @property-read \hiam\controllers\SiteController $controller
 */
class ConfirmEmail extends Action
{
    const EVENT_BEFORE_EMAIL_CONFIRMED = 'beforeEmailConfirmed';

    const SESSION_VAR_NAME = 'confirming_email';

    /**
     * @var string
     */
    public $actionAttributeName = 'action';

    /**
     * @var string
     */
    public $actionAttributeValue = 'confirm-email';

    /**
     * @var string
     */
    public $usernameAttributeName = 'username';

    /**
     * @var string
     */
    public $successMessage;

    /**
     * @var string
     */
    public $errorMessage;

    /**
     * @var ServiceInterface
     */
    protected $confirmator;

    /**
     * @var User|\hiam\base\User
     */
    protected $user;

    /**
     * @var Session
     */
    protected $session;

    public function __construct($id, $controller, ServiceInterface $confirmator, User $user, Session $session, $config = [])
    {
        parent::__construct($id, $controller, $config);
        $this->confirmator = $confirmator;
        $this->user = $user;
        $this->session = $session;
    }

    /** {@inheritdoc} */
    protected function beforeRun()
    {
        $identity = $this->user->identity ?? null;
        if ($identity) {
            if ($identity->email !== $this->session->get(static::SESSION_VAR_NAME)) {
                $this->user->logout();
            }
        }
        return parent::beforeRun();
    }

    public function run()
    {
        /** @var Token $token */
        $token = $this->confirmator->findToken(Yii::$app->request->get('token'));
        if ($token && $token->check([$this->actionAttributeName => $this->actionAttributeValue])) {
            $user = $this->user->findIdentityByUsername($token->get($this->usernameAttributeName));
        }
        if (!isset($user)) {
            $this->session->addFlash('error', $this->getErrorMessage());
        } else {
            $newEmail = $token->get('email');
            $this->trigger(self::EVENT_BEFORE_EMAIL_CONFIRMED, new BeforeEmailConfirmedEvent([
                'user' => $user,
                'newEmail' => $newEmail
            ]));
            $user->setConfirmedEmail($newEmail);
            $token->remove();
            $this->session->addFlash('success', $this->getSuccessMessage());
            if ($user->email === $this->session->get(static::SESSION_VAR_NAME)) {
                $this->user->login($user);
            }
        }
        return $this->controller->actionTransition();
    }

    private function getSuccessMessage(): string
    {
        return $this->successMessage ?: Yii::t('hiam', 'Your email was confirmed!');
    }

    private function getErrorMessage(): string
    {
        return $this->errorMessage ?: Yii::t('hiam', 'Failed confirm email. Please start over.');
    }
}

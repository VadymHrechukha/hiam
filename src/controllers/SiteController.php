<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\controllers;

use hiam\actions\ConfirmEmail;
use hiam\actions\OpenapiAction;
use hiam\base\User;
use hiam\behaviors\CaptchaBehavior;
use hiam\behaviors\RevokeOauthTokens;
use hiam\components\OauthInterface;
use hiam\forms\ChangeEmailForm;
use hiam\forms\ChangePasswordForm;
use hiam\forms\ConfirmPasswordForm;
use hiam\forms\LoginForm;
use hiam\forms\ResetPasswordForm;
use hiam\forms\RestorePasswordForm;
use hiam\forms\SignupForm;
use hiam\models\Identity;
use hiqdev\php\confirmator\ServiceInterface;
use hiqdev\yii2\mfa\filters\ValidateAuthenticationFilter;
use hisite\actions\RedirectAction;
use hisite\actions\RenderAction;
use hisite\actions\ValidateAction;
use vintage\recaptcha\helpers\RecaptchaConfig;
use vintage\recaptcha\validators\InvisibleRecaptchaValidator;
use Yii;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * Site controller.
 *
 * @property User $user
 */
class SiteController extends \hisite\controllers\SiteController
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'lockscreen';

    /**
     * @var ServiceInterface
     */
    private $confirmator;

    /**
     * @var OauthInterface
     */
    private $oauth;

    // XXX Disabled CSRF to allow external links to resend confirmation, change email/password...
    // XXX TO BE FIXED
    public $enableCsrfValidation = false;

    /**
     * Identifier which shows success login state to be used in CaptchaBehavior.
     *
     * @var bool $actionSubmitOccurred
     */
    private $actionSubmitOccurred = false;

    public function __construct($id, $module, ServiceInterface $confirmator, OauthInterface $oauth, $config = [])
    {
        parent::__construct($id, $module, $config = []);

        $this->confirmator = $confirmator;
        $this->oauth = $oauth;
    }

    public function behaviors()
    {
        $guestActions = [
            'signup', 'login', 'remote-proceed',
            'confirm-password', 'restore-password', 'reset-password',
            'terms', 'privacy-policy',
        ];
        $authenticatedActions = [
            'lockscreen', 'privacy-policy', 'terms',
            'resend-verification-email', 'back',
            'change-password', 'change-email',
        ];

        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'only' => array_merge($authenticatedActions, $guestActions),
                'denyCallback' => function () {
                    return $this->user->getIsGuest() ? $this->redirect(['login']) : $this->goBack();
                },
                'rules' => [
                    // ? - guest
                    [
                        'actions' => $guestActions,
                        'roles' => ['?'],
                        'allow' => true,
                    ],
                    // @ - authenticated
                    [
                        'actions' => $authenticatedActions,
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
            'validateAuthentication' => [
                'class' => ValidateAuthenticationFilter::class,
                'only' => ['lockscreen', 'change-password', 'change-email'],
            ],
            'captchaFilter' => [
                'class' => CaptchaBehavior::class,
                'isDisabled' => empty(Yii::$app->params[RecaptchaConfig::SITE_KEY]) || YII_ENV_DEV,
                'only' => ['signup', 'login', 'restore-password'],
                'limitPerAction' => [
                    'signup' => [1, CaptchaBehavior::PER_DAY],
                    'restore-password' => [2, CaptchaBehavior::PER_DAY],
                    'login' => [2, CaptchaBehavior::PER_DAY],
                ],
                'conditionalIncrement' => [
                    'login' => function (): bool {
                        return Yii::$app->request->getIsPost() && $this->actionSubmitOccurred;
                    },
                    'restore-password' => function (): bool {
                        return Yii::$app->request->getIsPost();
                    },
                ],
            ],
            'token-revoker' => [
                'class' => RevokeOauthTokens::class,
                'only' => ['logout'],
            ],
        ]);
    }

    public function actions()
    {
        return array_merge(parent::actions(), [
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => function (ClientInterface $client) {
                    $user = $this->user->findIdentityByAuthClient($client);
                    if ($user) {
                        $this->user->login($user);
                    }
                },
            ],
            'lockscreen' => [
                'class' => RenderAction::class,
            ],
            'terms' => [
                'class' => RedirectAction::class,
                'url' => Yii::$app->params['terms.url'],
            ],
            'privacy-policy' => [
                'class' => RedirectAction::class,
                'url' => Yii::$app->params['privacy.policy.url'],
            ],
            'signup-validate' => [
                'class' => ValidateAction::class,
                'form' => SignupForm::class,
            ],
            'confirm-email' => [
                'class' => ConfirmEmail::class,
            ],
            'confirm-sign-up-email' => [
                'class' => ConfirmEmail::class,
                'actionAttributeValue' => 'confirm-sign-up-email',
            ],
            'openapi.yaml' => [
                'class' => OpenapiAction::class,
            ],
            'openapi.yml' => [
                'class' => OpenapiAction::class,
            ],
        ]);
    }

    public function actionLogin($username = null)
    {
        $client = Yii::$app->authClientCollection->getActiveClient();
        if ($client) {
            return $this->redirect(['remote-proceed']);
        }

        return $this->doLogin(Yii::createObject(['class' => LoginForm::class]), 'login', $username);
    }

    protected function doLogin($model, $view, $username = null)
    {
        $model->username = $username;
        $isCaptchaRequired = $this->isCaptchaRequired();

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $this->isCaptchaChecked($isCaptchaRequired)) {
            $identity = $this->user->findIdentityByCredentials($model->username, $model->password);
            if ($identity && $this->login($identity, $model->remember_me)) {
                return $this->goBack();
            }
            $this->actionSubmitOccurred = true;

            $model->addError('password', Yii::t('hiam', 'Incorrect password.'));
            $model->password = null;
        }

        return $this->render($view, compact('model', 'isCaptchaRequired'));
    }

    /**
     * Logs user in and preserves return URL.
     */
    private function login(Identity $identity, $sessionDuration = 0): bool
    {
        $returnUrl = $this->user->getReturnUrl();

        $result = $this->user->login($identity, $sessionDuration ? null : 0);
        if ($result && $returnUrl && $returnUrl !== '/') {
            $this->user->setReturnUrl($returnUrl);
        }

        return $result;
    }

    public function actionConfirmPassword()
    {
        $client = Yii::$app->authClientCollection->getActiveClient();
        if (!$client) {
            return $this->redirect(['login']);
        }

        try {
            $email = $client->getUserAttributes()['email'];
            $user = $this->user->findIdentityByEmail($email);
        } catch (\Exception $e) {
            return $this->redirect(['logout']);
        }

        $res = $this->doLogin(new ConfirmPasswordForm(), 'confirmPassword', $user ? $user->email : null);
        $user = $this->user->getIdentity();
        if ($user) {
            $this->user->setRemoteUser($client, $user);
        }

        return $res;
    }

    public function actionRemoteProceed()
    {
        $client = Yii::$app->authClientCollection->getActiveClient();
        if (!$client) {
            return $this->redirect(['login']);
        }

        try {
            $email = $client->getUserAttributes()['email'];
            $user = $this->user->findIdentityByEmail($email);
        } catch (\Exception $e) {
            return $this->redirect(['logout']);
        }

        if ($user) {
            return $this->redirect(['confirm-password']);
        }

        return $this->redirect(['signup']);
    }

    public function actionSignup($scenario = SignupForm::SCENARIO_DEFAULT)
    {
        if ($this->user->disableSignup) {
            Yii::$app->session->setFlash('error', Yii::t('hiam', 'Sorry, signup is disabled.'));

            return $this->redirect(['login']);
        }

        if ($scenario === SignupForm::SCENARIO_SOCIAL) {
            return $this->redirect(['site/auth', 'authclient' => 'google']);
        }

        $client = Yii::$app->authClientCollection->getActiveClient();

        $model = new SignupForm(compact('scenario'));
        $isCaptchaRequired = $this->isCaptchaRequired();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $this->isCaptchaChecked($isCaptchaRequired)) {
            if ($user = $this->user->signup($model)) {
                if ($client) {
                    $this->user->setRemoteUser($client, $user);
                }
                $this->sendConfirmEmail($user, 'confirm-sign-up-email');

                return $this->redirect('transition');
            }
        } else {
            if ($client) {
                try {
                    $data = $client->getUserAttributes();
                } catch (\Exception $e) {
                    return $this->redirect(['logout']);
                }
                $model->load([$model->formName() => $data]);
            }
            if ($username = Yii::$app->request->get('username')) {
                $model->email = $username;
            }
        }

        return $this->render('signup', compact('model', 'isCaptchaRequired'));
    }

    public function actionRestorePassword($username = null)
    {
        if ($this->user->disableRestorePassword) {
            Yii::$app->session->setFlash('error', Yii::t('hiam', 'Sorry, password restore is disabled.'));

            return $this->redirect(['login']);
        }


        $isCaptchaRequired = $this->isCaptchaRequired();
        $model = new RestorePasswordForm();
        $model->username = $username;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $this->isCaptchaChecked($isCaptchaRequired)) {
            $user = $this->user->findIdentityByUsername($model->username);
            if ($this->confirmator->mailToken($user, 'restore-password')) {
                Yii::$app->session->setFlash('success',
                    Yii::t('hiam', 'Check your email {maskedMail} for further instructions.', [
                        'maskedMail' => $model->maskEmail($user->email),
                    ])
                );
            } else if ($this->isCaptchaChecked($isCaptchaRequired)) {
                Yii::$app->session->setFlash('error', Yii::t('hiam', 'Sorry, we are unable to reset password for the provided username or email. Try to contact support team.'));
            } else {
                 Yii::$app->session->setFlash('error', Yii::t('hiam', 'Failed check captcha.'));
            }

            return $this->redirect('login');
        }

        return $this->render('restorePassword', compact('model', 'isCaptchaRequired'));
    }

    public function actionResetPassword($token = null)
    {
        $model = new ResetPasswordForm();
        $reset = $this->resetPassword($model, $token);

        if (isset($reset)) {
            if ($reset) {
                Yii::$app->session->setFlash('success', Yii::t('hiam', 'New password was saved.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('hiam', 'Failed reset password. Please start over.'));
            }

            return $this->redirect('login');
        }

        return $this->render('resetPassword', compact('model', 'token'));
    }

    public function actionChangePassword()
    {
        $model = Yii::createObject(['class' => ChangePasswordForm::class], [$this->user->getIdentity()]);

        return $this->changeRoutine($model);
    }

    public function actionChangeEmail()
    {
        $model = new ChangeEmailForm($this->user->getIdentity());

        return $this->changeRoutine($model);
    }

    public function actionResendVerificationEmail()
    {
        $user = $this->user->getIdentity();
        $this->sendConfirmEmail($user, 'confirm-sign-up-email');

        return $this->goBack();
    }

    public function resetPassword($model, $token)
    {
        $token = $this->confirmator->findToken($token);
        if (!$token || !$token->check(['action' => 'restore-password'])) {
            return false;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = $this->user->findIdentityByUsername($token->get('username'));
            if (!$user) {
                return false;
            }
            $user->password = $model->password;
            $res = $user->save();
            if ($res) {
                $token->remove();
            }

            return $res;
        }

        return null;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function isCaptchaRequired(): bool
    {
        return (Yii::$app->request->getBodyParams()['captchaIsRequired'] ?? false) && (YII_ENV !== 'test');
    }

    /**
     * @param ChangePasswordForm|ChangeEmailForm $model
     */
    private function changeRoutine($model)
    {
        $map = [
            ChangePasswordForm::class => [
                'view' => 'change-password',
                'label' => Yii::t('hiam', 'Password'),
            ],
            ChangeEmailForm::class => [
                'view' => 'change-email',
                'label' => Yii::t('hiam', 'Email'),
            ],
        ];
        $sender = $map[get_class($model)];
        $request = Yii::$app->request;

        if (!$request->isPost) {
            return $this->render($sender['view'], ['model' => $model]);
        }

        if ($model->load($request->post())
            && $model->validate()
            && $model->apply()
            && $model->save()
        ) {
            Yii::$app->session->setFlash('success', Yii::t('hiam', '{label} has been successfully changed', ['label' => $sender['label']]));
            if ($model instanceof ChangeEmailForm) {
                $this->sendConfirmEmail($this->user->getIdentity(), 'confirm-email', $model->email);
            }

            return $this->redirect('transition');
        }

        $errors = implode("; \n", $model->getFirstErrors());
        if (!$errors) {
            $errors = Yii::t('hiam', '{label} has not been changed: {message}',
                [
                    'label' => $sender['label'],
                    'message' => $errors,
                ]
            );
        }
        Yii::$app->session->setFlash('error', $errors);

        return $this->render($sender['view'], ['model' => $model]);
    }

    public function actionBack()
    {
        return $this->goBack(Yii::$app->params['site_url']);
    }

    public function goBack($defaultUrl = null)
    {
        $response = $this->oauth->goBack() ?? parent::goBack($defaultUrl);
        $this->addSuccessParamToResponseUrl($response);
        return $response;
    }

    /**
     * @param Response|string|null $response
     */
    private function addSuccessParamToResponseUrl($response)
    {
        if (empty($response)) {
            return;
        }
        if (Yii::$app->session->hasFlash('success')) {
            $separator = strpos($response->headers['location'], '?') ? '&' : '?';
            $response->getHeaders()->add('Location', $separator . 'success=true');
        }
    }

    protected function sendConfirmEmail($user, $action, $newEmail = null)
    {
        if ($this->confirmator->mailToken($user, $action, ['email' => $newEmail ?? $user->email])) {
            Yii::$app->session->setFlash('warning',
                Yii::t('hiam', 'Please confirm your email address!') . '<br/>' .
                Yii::t('hiam',
                    'An email with confirmation instructions was sent to <b>{email}</b>',
                    ['email' => $user->email_confirmed ?? $user->email]
                )
            );
            Yii::$app->session->set(ConfirmEmail::SESSION_VAR_NAME, $user->email);
        } else {
            Yii::error('Failed to send email confirmation letter', __METHOD__);
        }
    }

    private function isCaptchaChecked(bool $isCaptchaRequired = false) : bool
    {
        if ($isCaptchaRequired !== true) {
            return true;
        }

        try {
            $isChecked = InvisibleRecaptchaValidator::validateInline(Yii::$app->request->post(), Yii::$app->request->userIP);
        } catch (\yii\base\InvalidConfigException $e) {
            return false;
        }

        return $isChecked;
    }
}

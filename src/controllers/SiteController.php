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
use hiam\components\Request;
use hiam\forms\ChangeEmailForm;
use hiam\forms\ChangePasswordForm;
use hiam\forms\ConfirmPasswordForm;
use hiam\forms\LoginForm;
use hiam\forms\ResetPasswordForm;
use hiam\forms\RestorePasswordForm;
use hiam\forms\SignupForm;
use hiqdev\yii2\confirmator\Service;
use hiqdev\yii2\mfa\filters\ValidateAuthenticationFilter;
use hisite\actions\RedirectAction;
use hisite\actions\RenderAction;
use hisite\actions\ValidateAction;
use vintage\recaptcha\helpers\RecaptchaConfig;
use Yii;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
use yii\base\Model;
use yii\filters\AccessControl;

/**
 * Site controller.
 *
 * @property User $user
 */
class SiteController extends \hisite\controllers\SiteController
{
    public $defaultAction = 'lockscreen';

    private Service $confirmator;
    private OauthInterface $oauth;

    // XXX Disabled CSRF to allow external links to resend confirmation, change email/password...
    // XXX TO BE FIXED
    public $enableCsrfValidation = false;

    /**
     * Identifier which shows success login state to be used in CaptchaBehavior.
     */
    private $actionSubmitOccurred = false;

    public function __construct($id, $module, Service $confirmator, OauthInterface $oauth, $config = [])
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

        return $this->doLogin(new LoginForm(), 'login', $username);
    }

    protected function doLogin($model, $view, $username = null)
    {
        /** @var Request $request */
        $request = Yii::$app->request;
        $model->username = $username;
        $isCaptchaRequired = $request->isCaptchaRequired();

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($model->load($request->post()) && $model->validate() && $request->validateCaptcha()) {
            $identity = $this->user->findIdentityByCredentials($model->username, $model->password);
            $returnUrl = $this->user->getReturnUrl();
            $this->user->login($identity, $model->remember_me ? null : 0);
            if ($this->user->getReturnUrl() !== '/') {
                $this->user->setReturnUrl($returnUrl);
            }
            return $this->goBack();
        } else {
            $this->actionSubmitOccurred = true;
        }

        $model->password = null;

        return $this->render($view, compact('model', 'isCaptchaRequired'));
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

        /** @var Request $request */
        $request = Yii::$app->request;
        $model = new SignupForm(compact('scenario'));

        $isCaptchaRequired = $request->isCaptchaRequired();

        if ($model->load($request->post()) && $model->validate() && $request->validateCaptcha()) {
            $identity = $this->user->signup($model);
            $this->confirmator->mailToken($identity, 'confirm-sign-up-email');
            $this->user->login($identity);

            if ($client) {
                $this->user->setRemoteUser($client, $identity);
            }

            return $this->redirect('transition');
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

        /** @var Request $request */
        $request = Yii::$app->request;
        $isCaptchaRequired = $request->isCaptchaRequired();
        $model = new RestorePasswordForm();
        $model->username = $username;
        if ($model->load($request->post()) && $model->validate() && $request->validateCaptcha()) {
            $this->confirmator->mailToken(
                $this->user->findIdentityByUsername($model->username),
                'restore-password',
            );

            return $this->redirect('login');
        }

        return $this->render('restorePassword', compact('model', 'isCaptchaRequired'));
    }

    public function actionResetPassword($token = null)
    {
        $model = new ResetPasswordForm();

        $token = $this->confirmator->findToken($token);

        if (
            $token
            && $token->check(['action' => 'restore-password'])
            && $model->load(Yii::$app->request->post())
            && $model->validate()
        ) {
            $user = $this->user->findIdentityByUsername($token->get('username'));
            $user->password = $model->password;
            $user->save();
            $token->remove();

            Yii::$app->session->setFlash('success', Yii::t('hiam', 'New password was saved.'));
        }

        return $this->render('resetPassword', compact('model', 'token'));
    }

    public function actionChangePassword()
    {
        return $this->changeRoutine(
            new ChangePasswordForm($this->user->getIdentity()),
            'change-password',
            Yii::t('hiam', 'Password')
        );
    }

    public function actionChangeEmail()
    {
        return $this->changeRoutine(
            new ChangeEmailForm($this->user->getIdentity()),
            'change-email',
            Yii::t('hiam', 'Email'),
        );
    }

    /**
     * @param ChangePasswordForm|ChangeEmailForm $model
     */
    private function changeRoutine(Model $model, string $view, string $label)
    {
        if (Yii::$app->request->isPost
            && $model->load(Yii::$app->request->post())
            && $model->validate()
            && $model->apply()
            && $model->save()
        ) {
            Yii::$app->session->setFlash(
                'success',
                Yii::t('hiam', '{label} has been successfully changed', ['label' => $label])
            );
            if ($model instanceof ChangeEmailForm) {
                $this->confirmator->mailToken(
                    $this->user->getIdentity(),
                    'confirm-email',
                    ['email' => $this->user->getIdentity()->email_new]
                );
                $this->confirmator->mailToken(
                    $this->user->getIdentity(),
                    'confirm-email',
                    [
                        'email' => $this->user->getIdentity()->email_new,
                        'to' => $this->user->getIdentity()->email_new,
                    ]
                );
            }

            return $this->redirect('transition');
        }

        return $this->render($view, ['model' => $model]);
    }

    public function actionResendVerificationEmail()
    {
        $user = $this->user->getIdentity();
        $action = empty($user->email_confirmed) ? 'confirm-sign-up-email' : 'confirm-email';
        $this->confirmator->mailToken($user, $action, ['email' => $user->email_new]);
        $this->confirmator->mailToken($user, $action, ['email' => $user->email_new, 'to' => $user->email_new]);

        return $this->goBack();
    }

    public function actionBack()
    {
        return $this->goBack(Yii::$app->params['site_url'] ?? Yii::getAlias('@HIPANEL_SITE', false));
    }

    public function goBack($defaultUrl = null)
    {
        $response = $this->oauth->goBack() ?? parent::goBack($defaultUrl);

        if (Yii::$app->session->hasFlash('success')) {
            $separator = strpos($response->headers['location'], '?') ? '&' : '?';
            $response->getHeaders()->add('Location', $separator . 'success=true');
        }

        return $response;
    }
}

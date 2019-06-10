<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiam\controllers;

use hiam\models\AuthorizeRequest;
use hiam\models\TokenRequest;
use Yii;
use yii\helpers\Url;
use yii\base\Model;

/**
 * Demo controller.
 */
class DemoController extends \yii\web\Controller
{
    public function getAuthorizeDefaults()
    {
        return [
            'client_id'     => 'demo',
            'redirect_uri'  => Url::to('/demo/index#token-form', true),
            'response_type' => 'code',
            'scopes'        => 'email',
            'state'         => 'x',
        ];
    }

    public function getTokenDefaults()
    {
        return [
            'client_id'     => 'demo',
            'client_secret' => 'pass',
            'redirect_uri'  => Url::to('/demo/index#resource-form', true),
            'grant_type'    => 'profile',
            'code'          => '',
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'authorizeRequest' => $this->getAuthorizeRequest(),
            'tokenRequest' => $this->getTokenRequest(),
        ]);
    }

    private function getAuthorizeRequest()
    {
        return $this->loadModel(new AuthorizeRequest($this->getAuthorizeDefaults()));
    }

    private function getTokenRequest()
    {
        return $this->loadModel(new TokenRequest($this->getTokenDefaults()));
    }

    private function loadModel(Model $model)
    {
        $model->load(Yii::$app->request->get(), '');

        return $model;
    }

    public function actionGo()
    {
        return $this->redirect($this->buildAuthorizeUrl());
    }

    private function buildAuthorizeUrl()
    {
        return Url::to(array_merge(['/oauth/authorize'], $this->getAuthorizeDefaults()), true);
    }
}

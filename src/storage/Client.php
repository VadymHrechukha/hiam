<?php

/*
 * Identity and Access Management server providing OAuth2, RBAC and logging
 *
 * @link      https://github.com/hiqdev/hiam-core
 * @package   hiam-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2016, HiQDev (http://hiqdev.com/)
 */

namespace hiam\storage;

use Yii;

/**
 * Client model.
 *
 * @property string $seller_id
 * @property string $password
 * @property string $email
 */
class Client extends \yii\db\ActiveRecord
{
    public $id;
    public $type;
    public $name;
    public $state;
    public $seller;
    public $username;
    public $last_name;
    public $first_name;

    public function init()
    {
        parent::init();
        $this->on(static::EVENT_BEFORE_INSERT, function ($event) {
            $seller = static::findByUsername(Yii::$app->params['user.seller']);
            $model = $event->sender;
            $model->login = $model->username;
            $model->seller_id = $seller->id;
        });
        $this->on(static::EVENT_AFTER_INSERT, function ($event) {
            $model = $event->sender;
            $model->id = $model->obj_id;
            $contact = Contact::findOne($model->id);
            $contact->setAttributes($model->getAttributes());
            $contact->save();
        });
    }

    public static function find()
    {
        return new ClientQuery(get_called_class());
    }
}

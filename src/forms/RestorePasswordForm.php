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

use Yii;

/**
 * Password password form.
 */
class RestorePasswordForm extends \yii\base\Model
{
    public $username;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'string', 'min' => 2, 'max' => 128],
            ['username', 'required', 'message' => 'Email can\'t be blank'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('hiam', 'Login or email'),
        ];
    }
}

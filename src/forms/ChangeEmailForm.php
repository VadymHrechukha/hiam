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

use hiam\models\Identity;
use Yii;
use yii\base\Model;

class ChangeEmailForm extends Model
{
    /**
     * @var string
     */
    public $email;

    private Identity $identity;

    /**
     * ChangeEmailForm constructor.
     *
     * @param Identity $identity the identity, email is being changed for
     * @param array $config
     */
    public function __construct(Identity $identity, $config = [])
    {
        $this->identity = $identity;

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['email', 'email'],
            [['email'], 'required'],
            [['email'], 'validateEmail'],
        ];
    }

    public function validateEmail($attribute, $params)
    {
        $existing = $this->identity::findOne(['username' => $this->{$attribute}]);
        if (!empty($existing)) {
            $this->addError($attribute, Yii::t('hiam', '{attribute} has already been taken',
                [
                    'attribute' => $attribute,
                ]
            ));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email' => Yii::t('hiam', 'Email'),
        ];
    }

    public function apply(): bool
    {
        return $this->identity->setNewUnconfirmedEmail($this->email);
    }

    public function save(): bool
    {
        return $this->identity->save();
    }
}

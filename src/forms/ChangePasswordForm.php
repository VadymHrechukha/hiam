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
use hiam\validators\ChangePasswordValidatorInterface;
use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    /**
     * @var string
     */
    public $current_password;

    /**
     * @var string
     */
    public $new_password;

    /**
     * @var string
     */
    public $confirm_password;

    private Identity $identity;

    /**
     * @param Identity $identity user's identity, who's password is being changed
     * @param array $config
     */
    public function __construct(Identity $identity, $config = [])
    {
        parent::__construct($config);
        $this->identity = $identity;
    }

    public function getLogin(): string
    {
        return $this->identity->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['current_password', 'new_password', 'confirm_password'], 'string'],
            [['current_password', 'new_password', 'confirm_password'], 'required'],
            ['current_password', ChangePasswordValidatorInterface::class],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'current_password' => Yii::t('hiam', 'Current password'),
            'new_password' => Yii::t('hiam', 'New password'),
            'confirm_password' => Yii::t('hiam', 'Confirm password'),
        ];
    }

    public function apply(): bool
    {
        return $this->identity->changePassword($this->new_password);
    }

    public function save(): bool
    {
        return $this->identity->save();
    }
}

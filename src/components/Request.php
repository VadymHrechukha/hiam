<?php
declare(strict_types=1);

namespace hiam\components;

use vintage\recaptcha\validators\InvisibleRecaptchaValidator;

class Request extends \yii\web\Request
{
    public function isCaptchaRequired(): bool
    {
        return $this->getBodyParam('captchaIsRequired') && (YII_ENV !== 'test');
    }

    public function validateCaptcha() : bool
    {
        if ($this->isCaptchaRequired() !== true) {
            return true;
        }

        try {
            $isChecked = InvisibleRecaptchaValidator::validateInline($this->post(), $this->userIP);
        } catch (\yii\base\InvalidConfigException $e) {
            return false;
        }

        return $isChecked;
    }
}

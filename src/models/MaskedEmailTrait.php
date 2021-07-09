<?php
declare(strict_types=1);

namespace hiam\models;

trait MaskedEmailTrait
{
    public function getMaskedEmail()
    {
        $email = $this->email;

        if (empty($email)) {
            return '';
        }

        $result = mb_substr($email, 0, 1); // First letter
        $result .= str_repeat('*', rand(5, 10)); // Mask
        $localLength = mb_strpos($email, '@'); // When login is longer than 3 chars - show
        if ($localLength > 3) {
            $result .= mb_substr($email, $localLength-1, 1);
        }
        $result .= '@';
        $result .= mb_substr($email, mb_strpos($email, '@') + 1, 1);
        $result .= mb_substr($email, mb_strlen($email));
        $result .= str_repeat('*', rand(2, 5));
        $result .= mb_substr($email, mb_strrpos($email, '.'));

        return $result;
    }
}

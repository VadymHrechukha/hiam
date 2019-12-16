<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\models;

/**
 * AuthorizeRequest model.
 * Used for demo page.
 */
class AuthorizeRequest extends \yii\base\Model
{
    public $client_id;
    public $redirect_uri;
    public $response_type;
    public $scopes;
    public $state;
    public $prefer_signup;

    public function rules()
    {
        return [
            ['client_id',       'string'],
            ['redirect_uri',    'string'],
            ['response_type',   'string'],
            ['scopes',          'string'],
            ['state',           'string'],
            ['prefer_signup',   'string'],
        ];
    }

    public function formName()
    {
        return '';
    }
}

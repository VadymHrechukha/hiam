<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   proprietary
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hiam\validators;

use Closure;
use yii\base\Model;

interface InlineableValidatorInterface
{
    /**
     * Provides inline validator closure for given model.
     *
     * @param Model $model
     * @return Closure ($attribute, $params, $validator)
     */
    public function inlineFor(Model $model): Closure;
}

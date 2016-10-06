<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var hiam\models\User $user */
/** @var yii\mail\MessageInterface $message */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $token]);

$message->setSubject('Password reset for ' . Yii::$app->params['organizationName']);

$message->renderTextBody('passwordResetToken-text', compact('user', 'resetLink'));

?>
<div class="password-reset">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Follow the link below to reset your password:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>

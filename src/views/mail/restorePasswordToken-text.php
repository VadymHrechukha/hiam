<?php

/** @var yii\web\View $this */
/** @var yii\web\IdentityInterface $user */
/** @var string $resetLink */
?>
<?= Yii::t('hiam', 'Hello {name},', ['name' => $user->username]) ?>

<?= Yii::t('hiam', 'Follow the link below to reset your password:') ?>

<?= $resetLink ?>

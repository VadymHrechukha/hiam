<?php

/**
 * Theme main layout.
 *
 * @var frontend\components\View $this View
 * @var string $content Content
 */

use Yii;
use yii\helpers\Html;
use frontend\components\widgets\Alert;
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->render('//layouts/head') ?>
</head>
<!-- ADD THE CLASS fixed TO GET A FIXED HEADER AND SIDEBAR LAYOUT -->
<body class="skin-blue">
<?php $this->beginBody(); ?>
<!-- Site wrapper -->
<div class="wrapper">

    <header class="main-header">
        <a href="<?= Yii::$app->homeUrl; ?>" class="logo"><b>HiP</b>anel</a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-custom-menu">
                <?= $this->render('navbar-custom-menu'); ?>
            </div>
        </nav>
    </header>

    <!-- =============================================== -->

    <!-- Left side column. contains the sidebar -->

    <? /* $this->render('sidebar'); */ ?>

    <!-- =============================================== -->

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                <?= $this->title; ?>
                <?php if (isset($this->params['subtitle'])) : ?>
                    <small><?= $this->params['subtitle'] ?></small>
                <?php endif; ?>
            </h1>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <?= $content ?>
                </div>
            </div>
        </section>
    </div><!-- /.content-wrapper -->

    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 1.0
        </div>
        <strong>Copyright &copy; 2014-2015 <?= Html::a(Yii::$app->name, Yii::$app->homeUrl); ?>.</strong> All rights reserved.
    </footer>
</div><!-- ./wrapper -->
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>

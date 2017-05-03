<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo @$page_title; ?></title>
        <link href="http://<?php echo @$_SERVER['HTTP_HOST']; ?>/<?php echo @$page_canonical; ?>" rel="canonical" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="keywords" content="<?php echo @$page_keywords; ?>">
        <meta name="description" content="<?php echo @$page_description; ?>">
        <meta name="author" content="WebComfort">

        <link href="/public/site/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="/public/site/css/site.css" rel="stylesheet">
		
		<!-- Scripts -->
		<script src="/public/site/js/jquery-1.10.2.min.js"></script>
		<script src="/public/site/bootstrap/js/bootstrap.min.js"></script>
		<script src="/public/site/js/site.js"></script>
        
        <!--[if lt IE 9]>
			<script src="/public/site/bootstrap/js/html5shiv.js"></script>
			<script src="/public/site/bootstrap/js/respond.min.js"></script>
        <![endif]-->
        
        <link rel="shortcut icon" href="/public/site/bootstrap/ico/favicon.png">
		<link rel="apple-touch-icon" sizes="57?57" href="/public/site/bootstrap/ico/apple-touch-icon-57-precomposed.png">
		<link rel="apple-touch-icon" sizes="72?72" href="/public/site/bootstrap/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon" sizes="114?114" href="/public/site/bootstrap/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon" sizes="144?144" href="/public/site/bootstrap/ico/apple-touch-icon-144-precomposed.png">
        
        <?php echo @$page_head; ?>

        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>

    <body>
    
    <div class="container">

        <div class="row mt10 mb10">
            <div class="col-xs-8"><h3><a href="/">Webcomfort CMS</a></h3></div>
            <div class="col-xs-4 pt10"><?php echo @view('search_form', array(), false, 'site/'); ?></div>
        </div>
		
		<div class="row mt20">
			<div class="col-xs-12">
        
				<nav class="navbar navbar-default" role="navigation">
					<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					  <span class="sr-only">Menu</span>
					  <span class="icon-bar"></span>
					  <span class="icon-bar"></span>
					  <span class="icon-bar"></span>
					</button>
					</div>
					
					<a class="navbar-brand" href="#"><?php echo @$page_name; ?></a>
					
					<div class="collapse navbar-collapse navbar-ex1-collapse">
						<?php echo @module('mod_menu_top', array(1)); ?>
					</div>
				</nav>
				
            </div>
        </div>
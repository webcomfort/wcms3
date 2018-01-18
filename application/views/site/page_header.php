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

        <!-- Icons -->
        <link rel="shortcut icon" href="/public/admin/img/favicon.png">
        <link rel="apple-touch-icon" sizes="57?57" href="/public/admin/img/apple-touch-icon-57-precomposed.png">
        <link rel="apple-touch-icon" sizes="72?72" href="/public/admin/img/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon" sizes="114?114" href="/public/admin/img/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon" sizes="144?144" href="/public/admin/img/apple-touch-icon-144-precomposed.png">

        <!-- CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
        <link href="/public/site/css/site.css" rel="stylesheet">
		
		<!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <script src="/public/site/js/site.js"></script>
        
        <?php echo @$page_head; ?>
    </head>

    <body>
    
    <div class="container">

        <div class="row mt-2 mb-2">
            <div class="col-md-8"><h3><a href="/">Webcomfort CMS</a></h3></div>
            <div class="col-md-4 pt-2"><?php echo @view('search_form', array(), false, 'site/'); ?></div>
        </div>
		
		<div class="row mt-2">
			<div class="col-md-12">

                <nav class="navbar navbar-expand-md navbar-light bg-light">
                    <a class="navbar-brand" href="#"><?php echo @$page_name; ?></a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
	                    <?php echo @module('mod_menu_top', array(1)); ?>
                    </div>
                </nav>
				
            </div>
        </div>

    </div>
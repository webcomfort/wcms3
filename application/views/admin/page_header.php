<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Webcomfort CMS</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="WebComfort">

        <!-- Bootstrap CSS -->
        <link href="/public/admin/third_party/bootstrap/css/bootstrap.css" rel="stylesheet">
        <!-- JQuery UI CSS -->
        <link href="/public/admin/third_party/jquery-ui/jquery-ui.min.css" rel="stylesheet">
        <!-- Chosen CSS -->
        <link href="/public/admin/third_party/select2/css/select2.min.css" rel="stylesheet" />
        <!-- JsTree CSS -->
        <link href="/public/admin/third_party/jstree/dist/themes/default/style.min.css" rel="stylesheet" />
        <!-- Font Awesome -->
        <link href="/public/admin/third_party/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <!-- Custom developer CSS -->
        <link href="/public/admin/css/style.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <!-- Icons -->
        <link rel="shortcut icon" href="/public/admin/img/favicon.png">
		<link rel="apple-touch-icon" sizes="57?57" href="/public/admin/img/apple-touch-icon-57-precomposed.png">
		<link rel="apple-touch-icon" sizes="72?72" href="/public/admin/img/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon" sizes="114?114" href="/public/admin/img/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon" sizes="144?144" href="/public/admin/img/apple-touch-icon-144-precomposed.png">

        <script>
            (function(w,d,u){w.readyQ=[];w.bindReadyQ=[];function p(x,y){if(x=="ready"){w.bindReadyQ.push(y);}else{w.readyQ.push(x);}};var a={ready:p,bind:p};w.$=w.jQuery=function(f){if(f===d||f===u){return a}else{p(f)}}})(window,document)
        </script>

        <!-- Admin meta -->
        <?php echo @$admin_meta; ?>

        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>

    <body>
    
    <div class="container-fluid">

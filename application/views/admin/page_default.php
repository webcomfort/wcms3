        <div class="row">
			<div class="col-md-4">
                <a href="/" class="ml10"><img src="/public/admin/img/logo.png" width="235" height="32" /></a>
            </div>
            <div class="col-md-8">
                <ul class="nav nav-pills pull-right">
                    <li>
                        <a class="white" href="/public/docs.docx" target="_blank"><span class="glyphicon glyphicon-info-sign icon-white"></span>&nbsp;&nbsp;Справка</a>
                    </li>
                    <li>
                        <a class="white" href="/admin/<?php echo $admin_page_id; ?>/clear"><span class="glyphicon glyphicon-refresh icon-white"></span>&nbsp;&nbsp;Очистить кэш</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle white" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-globe icon-white"></span>&nbsp;&nbsp;Язык сайта:&nbsp;<?php echo $admin_langs[$admin_active_lang]['name']; ?>&nbsp;&nbsp;<span class="caret icon-white"></span></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($admin_langs AS $key => $value) { ?>
                            <li role="presentation"<?php if ($admin_active_lang == $key) echo ' class="active"'; ?>><a role="menuitem" tabindex="-1" href="/admin/<?php echo $admin_page_id; ?>/lang/<?php echo $key; ?>"><?php echo $value['name']; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle white" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user icon-white"></span>&nbsp;&nbsp;<?php echo $user_name; ?>&nbsp;&nbsp;<span class="caret icon-white"></span></a>
                        <ul class="dropdown-menu">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="/admin/exit"><span class="glyphicon glyphicon-off"></span> Выйти</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
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
				  
				  <div class="collapse navbar-collapse navbar-ex1-collapse">
					<?php echo module('mod_admin_menu'); ?>
			      </div>
				</nav>
				
			</div>
		</div>

		<div class="row m0 ui-block-panel">
			<div class="col-xs-12"><h4><?php echo $admin_name; ?></h4></div>
		</div>
		
        <div id="admin_filters"><?php echo $admin_filters; ?></div>
        <div id="admin_interface"><?php echo $admin_interface; ?></div>

		<footer class="mt20">
			<p>&copy; <a href="http://www.webcomfort.ru" class="white">Webcomfort CMS</a></p>
		</footer>

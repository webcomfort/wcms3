		<div class="row">
            <div class="col-xs-8">
                <?php echo @module('mod_menu_crumbs', array(1)); ?>
                <?php echo @$page_article_1; ?>
                <?php echo @$inc_module_1; ?>
                <?php echo @$inc_module_2; ?>
                <?php echo @$inc_module_3; ?>
            </div>
            <div class="col-xs-4">
                <?php echo @module('mod_menu_second', array(1, 'nav nav-pills nav-stacked')); ?>
				<hr class="mt20">
                <?php echo @module('mod_news_latest', array(1, 2, 'news_latest')); ?>
            </div>
        </div>
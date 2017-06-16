		<div class="row">
            <div class="col-xs-8">
                <?php echo @module('mod_menu_crumbs', array(1)); ?>
                <?php
                if(isset($page_articles) && is_array($page_articles) && isset($page_articles[0])){
                    foreach ($page_articles[0] as $value) echo @$value;
                }
                ?>
                <?php echo @$inc_module_1; ?>
                <?php echo @$inc_module_2; ?>
                <?php echo @$inc_module_3; ?>
            </div>
            <div class="col-xs-4">
                <?php echo @module('mod_menu_second', array(1, 'nav nav-pills nav-stacked')); ?>
				<hr class="mt20">
                <?php echo @module('mod_news_latest', array(1, 2, 'news_latest')); ?>
                <?php
                if(isset($page_articles) && is_array($page_articles) && isset($page_articles[1])){
                    foreach ($page_articles[1] as $value) echo @$value;
                }
                ?>
            </div>
        </div>
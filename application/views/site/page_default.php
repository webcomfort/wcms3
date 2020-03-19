    <div class="container">
		<div class="row mt-2">
            <div class="col-md-8">
	            <?php echo $page_crumbs; ?>
                <?php
                if(isset($page_articles) && is_array($page_articles) && isset($page_articles[0])){
                    foreach ($page_articles[0] as $value) echo @$value;
                }
                ?>
                <?php echo @$inc_module_1; ?>
                <?php echo @$inc_module_2; ?>
                <?php echo @$inc_module_3; ?>
            </div>
            <div class="col-md-4">

                <?php echo @module('mod_menu_second', array(1, '')); ?>

				<?php echo @$inc_module_5; ?>
            </div>
        </div>
    </div>

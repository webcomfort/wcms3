        <div class="container">
            <div class="row">
                <div class="col-md-12 mt-2">
                    <?php echo @module('mod_menu_crumbs', array(1)); ?>
                </div>
            </div>
        </div>

        <?php
        if(isset($page_articles) && is_array($page_articles) && isset($page_articles[0])){
            foreach ($page_articles[0] as $value) echo @$value;
        }
        ?>

        <div class="container">
            <div class="row">
                <div class="col-md-12">
			        <?php echo @$inc_module_1; ?>
			        <?php echo @$inc_module_2; ?>
			        <?php echo @$inc_module_3; ?>
                </div>
            </div>
        </div>
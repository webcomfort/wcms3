        <div class="row mt10">
            <div class="col-xs-8">
                
				<div class="row">
                    <div class="col-xs-12">
                        <?php echo @$inc_module_3; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <?php echo @$page_article_1; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <?php echo @$page_article_2; ?>
                    </div>
                    <div class="col-xs-6">
                        <?php echo @$page_article_3; ?>
                    </div>
                </div>
            </div>
            
			<div class="col-xs-4">
                <?php echo @module('mod_news_latest', array(1, 2, 'news_latest')); ?>
            </div>
        </div>
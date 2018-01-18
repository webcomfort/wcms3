    <div class="container">
        <div class="row mt-2">
            <div class="col-md-8">
				<div class="row">
                    <div class="col-md-12 mb-2">
                        <?php echo @$inc_module_3; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        if(isset($page_articles) && is_array($page_articles) && isset($page_articles[0])){
                            foreach ($page_articles[0] as $value) echo @$value;
                        }
                        ?>
                    </div>
                </div>
            </div>
            
			<div class="col-md-4">
                <?php echo @module('mod_news_latest', array(1, 2, 'news_latest')); ?>
                <?php
                if(isset($page_articles) && is_array($page_articles) && isset($page_articles[1])){
	                foreach ($page_articles[1] as $value) echo @$value;
                }
                ?>
            </div>
        </div>
    </div>
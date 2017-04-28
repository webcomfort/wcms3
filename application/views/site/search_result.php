<div class="search_result">

	<h3><?php echo sprintf(lang('search_result'), $search_phrase); ?></h3>

	<?php if ($search_result) { ?>

    <h4><?php echo sprintf(lang('search_num'), $search_count); ?></h4>
    
    <hr>

    <?php foreach ($search_list as $value) { ?>

		<h5><a href="<?php echo $value['url']; ?>" title="<?php echo $value['title']; ?>"><?php echo $value['title']; ?></a></h5>
        
        <p><?php echo $value['cut']; ?></p>
		
        <hr>

	<?php } ?>

	<?php echo $search_pages; ?>

    <?php } else { ?>
        
        <?php echo lang('search_error'); ?>
    
    <?php } ?>

</div>
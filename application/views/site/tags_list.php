<section class="catalog-tags" id="catalog-tags">

	<?php $i = 1; ?>
    <h4><?php echo lang('tags_title'); ?></h4>

    <div class="row">
    <?php foreach ($tags as $key => $value) { ?>
        <div class="tag-container col-lg-6 col-md-6 col-sm-6 col-xs-6<?php if($i > $tags_limit){ echo ' d-none'; } ?>"><a href="<?php echo $page; ?>?tag=<?php echo $key; ?>" class="tag-item"><?php echo $value['name']; ?></a><span class="tag-count"><?php echo $value['count']; ?></span></div>
	    <?php $i++; ?>
	<?php } ?>
    </div>

    <?php if($i > $tags_limit){ ?>
        <a href="javascript:void(0)" class="btn btn-dark all-tags"><?php echo lang('tags_all'); ?></a>
    <?php } ?>

</section>
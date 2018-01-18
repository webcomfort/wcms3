<?php if (isset($gallery_images['_big'])) { ?>
<div id="gal_<?php echo $gallery_id; ?>" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
	    <?php $i = 0; foreach ($gallery_images['_big'] as $key => $value) { ?>
        <li data-target="#gal_<?php echo $gallery_id; ?>" data-slide-to="<?php echo $i; ?>"<?php if ($i == 0) echo ' class="active"'; ?>></li>
		<?php $i++; } ?>
    </ol>
    <div class="carousel-inner">
	    <?php $i = 0; foreach ($gallery_images['_big'] as $key => $value) { ?>
        <div class="carousel-item<?php if ($i == 0) echo ' active'; ?>">
            <a href="<?php echo $value['link']; ?>"><?php echo $value['img']; ?></a>
            <div class="carousel-caption d-none d-md-block">
	            <?php if($value['text']) echo $value['text']; else echo '<p>'.$value['name'].'</p>'; ?>
            </div>
        </div>
		<?php $i++; } ?>
    </div>
    <a class="carousel-control-prev" href="#gal_<?php echo $gallery_id; ?>" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only"><?php echo lang('pagination_prev_link'); ?></span>
    </a>
    <a class="carousel-control-next" href="#gal_<?php echo $gallery_id; ?>" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only"><?php echo lang('pagination_next_link'); ?></span>
    </a>
</div>
<?php } ?>
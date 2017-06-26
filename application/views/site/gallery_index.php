<?php if (isset($gallery_images['_big'])) { ?>
<div id="gal_<?php echo $gallery_id; ?>" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
        <?php $i = 0; foreach ($gallery_images['_big'] as $key => $value) { ?>
        <li data-target="#gal_<?php echo $gallery_id; ?>" data-slide-to="<?php echo $i; ?>"<?php if ($i == 0) echo ' class="active"'; ?>></li>
        <?php $i++; } ?>
    </ol>
    <!-- Carousel items -->
    <div class="carousel-inner">
        <?php $i = 0; foreach ($gallery_images['_big'] as $key => $value) { ?>
        <div class="<?php if ($i == 0) echo 'active '; ?>item"><a href="<?php echo $value['link']; ?>"><?php echo $value['img']; ?></a><div class="carousel-caption"><?php if($value['text']) echo $value['text']; else echo '<p>'.$value['name'].'</p>'; ?></div></div>
        <?php $i++; } ?>
    </div>
    <!-- Carousel nav -->
    <a class="left carousel-control" href="#gal_<?php echo $gallery_id; ?>" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
    <a class="right carousel-control" href="#gal_<?php echo $gallery_id; ?>" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
</div>
<?php } ?>
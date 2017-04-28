<ul class="breadcrumb">
	<li><a href="/"><img src="/public/site/img/icon_home.png" /></a></li>
    <?php
    foreach ($crumbs_array as $value) {
    if ($value['page_id'] != PAGE_ID) {
    ?>
    <li><a href="/<?php echo $value['page_url']; ?>" title="<?php echo $value['page_name']; ?>"><?php echo $value['page_name']; ?></a></li>
    <?php } else { ?>
	<li class="active"><?php echo $value['page_name']; ?></li>
	<?php }} ?>
</ul>
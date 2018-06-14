<nav aria-label="breadcrumb">
    <ol class="breadcrumb">

        <li class="breadcrumb-item"><?php echo lang('global_crumbs'); ?></li>

		<?php
		$i = 1;
		foreach ($crumbs_array as $value) {
			if (count($crumbs_array) != $i) {
				?>
                <li class="breadcrumb-item"><a href="/<?php echo $value['page_url']; ?>" title="<?php echo $value['page_name']; ?>"><?php echo $value['page_name']; ?></a></li>
			<?php } else { ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $value['page_name']; ?></li>
			<?php }
			$i++;
		} ?>
    </ol>
</nav>
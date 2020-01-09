<nav aria-label="breadcrumb">
    <ol class="breadcrumb">

        <li class="breadcrumb-item"><?php echo lang('global_crumbs'); ?></li>

		<?php
		$i = 1;
		$url = '';
		foreach ($crumbs_array as $key => &$value) {
			if (count($crumbs_array) != $i) {
				if($value['page_status'] == 3 && array_key_exists($key + 1, $crumbs_array)){
					$url .= '/'.$value['page_url'].'/'.$crumbs_array[$key + 1]['page_url'];
					$crumbs_array[$key + 1]['page_url'] = '';
				} else {
					$url .= '/'.$value['page_url'];
                }
				?>
                <li class="breadcrumb-item"><a href="<?php echo $url; ?>" title="<?php echo $value['page_name']; ?>"><?php echo $value['page_name']; ?></a></li>
			<?php } else { ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $value['page_name']; ?></li>
			<?php }
			$i++;
		} ?>
    </ol>
</nav>
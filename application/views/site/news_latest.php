<h3 class="m0"><a href="<?php echo $news_latest_url; ?>" title="<?php echo lang('news_latest_link'); ?>"><?php echo $news_latest_cat; ?> &raquo;</a></h3>

<?php foreach ($news_latest as $value) { ?>

    <h5><a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php echo $value['news_name']; ?></a></h5>
    
    <p><small><em><?php echo $value['news_date']; ?></em></small></p>
    
    <p><a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php echo $value['news_img']['_thumb']; ?></a></p>
    
    <p><?php echo word_limiter(strip_tags($value['news_cut']), 20); ?></p>
    
    <hr>

<?php } ?>

<p><a class="btn btn-default" href="<?php echo $news_latest_url; ?>" title="<?php echo lang('news_latest_link'); ?>"><?php echo lang('news_latest_link'); ?> &raquo;</a></p>
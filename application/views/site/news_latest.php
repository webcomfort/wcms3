<h3 class="mb-3"><a href="<?php echo $news_latest_url; ?>" title="<?php echo lang('news_latest_link'); ?>"><?php echo $news_latest_cat; ?> &raquo;</a></h3>

<?php foreach ($news_latest as $value) { ?>

    <div class="card mb-2">
        <a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php echo $value['news_img']['_thumb']; ?></a>
        <div class="card-body">
            <h5 class="card-title"><a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php echo $value['news_name']; ?></a></h5>
            <p class="card-text"><small><em><?php echo $value['news_date']; ?></em></small></p>
            <p class="card-text"><?php echo word_limiter(strip_tags($value['news_cut']), 20); ?></p>
        </div>
    </div>

<?php } ?>
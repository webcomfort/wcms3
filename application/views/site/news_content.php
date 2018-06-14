<?php if (@$news_error)
{
    echo '<p>';
    echo lang('news_404');
    echo '</p>';
}
else
{
?>

<div class="news-content">

    <h4><?php echo $news_name; ?></h4>
    <p><em><?php echo $news_date; ?></em></p>
    <p><?php echo @$news_img['_big']; ?></p>
    <?php
    if(isset($news_articles) && is_array($news_articles) && isset($news_articles[0])){
        foreach ($news_articles[0] as $value) echo @$value;
    }
    ?>

	<?php if (isset($news_tags) && is_array($news_tags) && count($news_tags)) { ?>
        <div class="tags">
			<?php foreach ($news_tags as $tag_id => $tag_name) { ?>
                <a href="<?php echo $news_list_url; ?>?tag=<?php echo $tag_id; ?>" class="badge badge-success" title="<?php echo $tag_name; ?>"><?php echo $tag_name; ?></a>
			<?php } ?>
        </div>
	<?php } ?>

    <?php echo @$inc_module_1; ?>
    <?php echo @$inc_module_3; ?>

</div>

<?php } ?>
<?php if (@$news_error)
{
    echo '<p>';
    echo lang('news_404');
    echo '</p>';
}
else
{
?>
<h2><?php echo PAGE_NAME; ?><?php if($tag_name){ echo '<small>'.lang('tags_with').'&laquo;'.$tag_name.'&raquo;'.' [<a href="'.$news_list_url.'">'.lang('tags_cancel').'</a>]</small>'; } ?></h2>

<?php foreach ($news_list as $value) { ?>

    <h4><a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php echo $value['news_name']; ?></a></h4>
    <p><small><em><?php echo $value['news_date']; ?></em></small></p>
    <p><a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php if (isset($value['news_img']['_big'])) echo $value['news_img']['_big']; ?></a></p>
    <p><?php echo word_limiter(strip_tags($value['news_cut']), 20); ?></p>

    <?php if (isset($value['news_tags']) && is_array($value['news_tags']) && count($value['news_tags'])) { ?>
        <div class="tags">
		<?php foreach ($value['news_tags'] as $tag_id => $tag_name) { ?>
            <a href="<?php echo $news_list_url; ?>/0/tag/<?php echo $tag_id; ?>" class="badge badge-success" title="<?php echo $tag_name; ?>"><?php echo $tag_name; ?></a>
		<?php } ?>
        </div>
	<?php } ?>
    
    <hr>

<?php } ?>

<?php echo $news_list_pages; ?>

<?php } ?>
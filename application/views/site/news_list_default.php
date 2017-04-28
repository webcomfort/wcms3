<?php if (@$news_error)
{
    echo '<p>';
    echo lang('news_404');
    echo '</p>';
}
else
{
?>
<h2><?php echo PAGE_NAME; ?></h2>

<?php foreach ($news_list as $value) { ?>

    <h4><a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php echo $value['news_name']; ?></a></h4>
    <p><small><em><?php echo $value['news_date']; ?></em></small></p>
    <p><a href="<?php echo $value['news_url']; ?>" title="<?php echo $value['news_name']; ?>"><?php if (isset($value['news_img']['_big'])) echo $value['news_img']['_big']; ?></a></p>
    <p><?php echo word_limiter(strip_tags($value['news_cut']), 20); ?></p>
    
    <hr>

<?php } ?>

<?php echo $news_list_pages; ?>

<?php } ?>
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
    <p><?php echo $news_date; ?></p>
    <p><?php echo @$news_img['_big']; ?></p>
    <?php
    if(isset($news_articles) && is_array($news_articles) && isset($news_articles[0])){
        foreach ($news_articles[0] as $value) echo @$value;
    }
    ?>

    <?php echo @$inc_module_1; ?>
    <?php echo @$inc_module_3; ?>

</div>

<?php } ?>
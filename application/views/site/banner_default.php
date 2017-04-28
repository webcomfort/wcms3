<?php
if ($banner['code'] != '') {
    echo $banner['code'];
}
else {
    if($banner['url'] != '') {
        echo '<a href="'.$banner['url'].'"';
        if ($banner['blank']) echo ' target="_blank"';
        echo '>';
        echo $banner['img'];
        echo '</a>';
    }
    else {
        echo $banner['img'];
    }
}
?>
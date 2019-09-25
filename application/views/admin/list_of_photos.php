<style>
	.gal-photo-header{
		text-align: center;
		margin: 5px 5px 20px 5px;
		padding: 8px 5px;
		background-color: #757c82;
		color: #fff;
		border-radius: 3px;
		font-size: 14px;
	}
	#sortable .col-md-2 {
		cursor: move;
	}
</style>
<div class="row" id="sortable">
<?php
/*
[qf1] => photo_id
[qf3] => photo_name
[qf6] => photo_active
[qf7] => photo_sort
*/
foreach ($myEditResult as $value){
	//echo '<pre>'.print_r($value, true).'</pre>';
	?>
	<div class="col-sm-3 col-md-2" id="item-<?=$value['qf1']?>">
		<div class="thumbnail">
			<div class="gal-photo-header"><?=$value['qf3']?></div>
			<?=$value['file']?>
			<div class="caption">
				<p class="text-center"><?=$value['change_button']?> <?=$value['copy_button']?> <?=$value['delete_button']?></p>
			</div>
		</div>
	</div>
	<?php
}

?>
</div>
<script>
    $(document).ready(function () {
        $( "#sortable" ).sortable({
            update: function (event, ui) {
                var data = $(this).sortable('serialize');
                $.ajax({
                    method: "GET",
                    url: "/adm_gallery_photos/p_sortable/",
                    data: data
                }).done(function(result) {
                    console.log(result);
                });
            }
        });
    });
</script>

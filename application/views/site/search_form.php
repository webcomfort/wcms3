<?php echo form_open('/search',array('class' => 'form-search pull-right')); ?>
	<div class="input-group">
	  <input type="text" name="search" class="form-control">
	  <span class="input-group-btn">
		<button class="btn btn-default" type="submit"><?php echo lang('search_submit'); ?></button>
	  </span>
	</div>
</form>
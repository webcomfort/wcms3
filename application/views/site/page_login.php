<div class="container text-center">

<?php if($mode == 1){ ?>
	<?php echo form_open('/'.(PAGE_URL != '-') ? PAGE_URL : '', array('class' => 'form-signin', 'id' => 'login_form')); ?>
	<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
	<?php if ($error && $error != 4) echo '<div class="alert alert-danger">'.lang('cms_user_error_'.$error).'</div>'; ?>
	<?php if ($error && $error == 4) echo '<div class="alert alert-success">'.lang('cms_user_error_'.$error).'</div>'; ?>
    <label for="w_login" class="sr-only"><?=lang('cms_user_form_1')?></label>
    <input type="email" id="w_login" name="w_login" class="form-control i1" placeholder="<?=lang('cms_user_form_1')?>" required autofocus>
    <label for="w_pass" class="sr-only"><?=lang('cms_user_form_2')?></label>
    <input type="password" id="w_pass" name="w_pass" class="form-control i2" placeholder="<?=lang('cms_user_form_2')?>" required>
    <div class="checkbox mb-3">
        <label>
            <input type="checkbox" name="w_remember" value="1"> <?=lang('cms_user_form_4')?>
        </label>
    </div>
    <button type="submit" data-sitekey="<?php echo @file_conf('cms_recaptcha_sitekey'); ?>" data-callback="onSubmit" class="g-recaptcha btn btn-primary btn-block"><?php echo lang('cms_user_form_5'); ?></button>
    <p class="mt-3"><a href="/<?php echo PAGE_URL; ?>/remember"><?php echo lang('cms_user_form_3'); ?></a></p>
    <p class="mt-5 mb-3 text-muted"><?=lang('global_copy')?></p>
</form>
<?php } ?>

<?php if($mode == 2){ ?>
	<?php echo form_open('/'.PAGE_URL.'/remember', array('class' => 'form-signin', 'id' => 'login_form')); ?>
	<?php echo validation_errors('<div class="alert alert-danger mt-5 mb-5">', '</div>'); ?>
	<?php if ($error) echo '<div class="alert alert-danger mt-5 mb-5">'.lang('cms_user_error_'.$error).'</div>'; ?>

    <label for="w_email" class="sr-only"><?php echo lang('cms_user_form_1'); ?></label>
    <input type="text" class="form-control" placeholder="<?php echo lang('cms_user_form_1'); ?>" name="w_email" required autofocus>
    <button data-sitekey="<?php echo @file_conf('cms_recaptcha_sitekey'); ?>" data-callback="onSubmit" class="g-recaptcha btn btn-primary btn-block mt-3"><?php echo lang('cms_user_form_6'); ?></button>
    <p class="mt-3"><a href="/<?php echo (PAGE_URL != '-') ? PAGE_URL : ''; ?>"><?php echo lang('cms_user_form_7'); ?></a></p>
    <p class="mt-5 mb-3 text-muted"><?=lang('global_copy')?></p>

    </form>
<?php } ?>

<?php if($mode == 3){ ?>

    <div class="form-signin">
        <?php if ($error) echo '<div class="alert alert-warning mt-5 mb-5">'.lang('cms_user_error_'.$error).'</div>'; ?>
        <div class="wrapper-center mt-5"><a href="/<?php echo (PAGE_URL != '-') ? PAGE_URL : ''; ?>"><?php echo lang('cms_user_form_7'); ?></a></div>
    </div>

<?php } ?>

<?php if($mode == 4){ ?>
	<?php echo form_open('/'.PAGE_URL.'/change', array('class' => 'form-signin', 'id' => 'login_form')); ?>
	<?php echo validation_errors('<div class="alert alert-danger mt-5 mb-5">', '</div>'); ?>
	<?php if ($error) echo '<div class="alert alert-danger mt-5 mb-5">'.lang('cms_user_error_'.$error).'</div>'; ?>

    <input type="hidden" name="w_hash" value="<?php echo $hash; ?>">
    <label for="w_pass_new" class="sr-only"><?php echo lang('cms_user_form_8'); ?></label>
    <input type="password" class="form-control i1" placeholder="<?php echo lang('cms_user_form_8'); ?>" name="w_pass_new" value="<?php echo set_value('w_pass_new'); ?>" required autofocus>
    <label for="w_pass_confirm" class="sr-only"><?php echo lang('cms_user_form_9'); ?></label>
    <input type="password" class="form-control i2" placeholder="<?php echo lang('cms_user_form_9'); ?>" name="w_pass_confirm" value="<?php echo set_value('w_pass_confirm'); ?>">
    <button data-sitekey="<?php echo @file_conf('cms_recaptcha_sitekey'); ?>" data-callback="onSubmit" class="g-recaptcha btn btn-primary btn-block mt-3"><?php echo lang('cms_user_form_10'); ?></button>
    <p class="mt-3"><a href="/<?php echo (PAGE_URL != '-') ? PAGE_URL : ''; ?>"><?php echo lang('cms_user_form_7'); ?></a></p>

    </form>

<?php } ?>

<script>
    function onSubmit(token) {
        document.getElementById("login_form").submit();
    }
</script>
</div>

        <div class="logo"><img src="/public/admin/img/logo.png" width="235" height="32" /></div>
        
        <?php if($mode == 1){ ?>
        <?php echo form_open('', array('class' => 'form-signin ui-block p20', 'id' => 'login_form')); ?>
        <?php echo validation_errors('<span class="label label-danger block my5 py5">', '</span>'); ?>
        <?php if ($error) echo '<span class="label label-danger block my5 py5">'.lang('cms_user_error_'.$error).'</span>'; ?>
            
        <input type="text" class="form-control" placeholder="<?php echo lang('cms_user_form_1'); ?>" name="w_login" value="<?php echo set_value('w_login'); ?>" autofocus>
        <input type="password" class="form-control mt5" placeholder="<?php echo lang('cms_user_form_2'); ?>" name="w_pass" value="<?php echo set_value('w_pass'); ?>">
        
		<div class="pull-left pl20">
		<label class="checkbox" class="ml10">
          <input type="checkbox" value="1" name="w_remember" <?php echo set_checkbox('w_remember', '1'); ?>> <?php echo lang('cms_user_form_4'); ?>
        </label>
		</div>
		<div class="pull-right mt10"><a href="/admin/remember"><?php echo lang('cms_user_form_3'); ?></a></div>
        <button data-sitekey="<?php echo @conf('recaptcha'); ?>" data-callback="onSubmit" class="g-recaptcha btn btn-primary btn-block mt20"><?php echo lang('cms_user_form_5'); ?></button>

        </form>
        
        <?php } ?>
        
        <?php //--------------------------------------------------------------------------------- ?>
        
        <?php if($mode == 2){ ?>
        <?php echo form_open('/admin/remember', array('class' => 'form-signin ui-block p20', 'id' => 'login_form')); ?>
        <?php echo validation_errors('<span class="label label-danger block my5 py5">', '</span>'); ?>
        <?php if ($error) echo '<span class="label label-danger block my5 py5">'.lang('cms_user_error_'.$error).'</span>'; ?>
            
        <input type="text" class="form-control" placeholder="<?php echo lang('cms_user_form_1'); ?>" name="w_email">                
        <button data-sitekey="<?php echo @conf('recaptcha'); ?>" data-callback="onSubmit" class="g-recaptcha btn btn-primary btn-block mt10"><?php echo lang('cms_user_form_6'); ?></button>
        <div class="wrapper-center mt10"><a href="/admin"><?php echo lang('cms_user_form_7'); ?></a></div>
            
        </form>
        
        <?php } ?>
        
        <?php //--------------------------------------------------------------------------------- ?>
        
        <?php if($mode == 3){ ?>
        
        <div class="form-signin ui-block p20 alc">
            <?php if ($error) echo '<span class="label label-success block my5 py5">'.lang('cms_user_error_'.$error).'</span>'; ?>
            <div class="wrapper-center mt10"><a href="/admin"><?php echo lang('cms_user_form_7'); ?></a></div>
        </div>
        
        <?php } ?>

        <?php //--------------------------------------------------------------------------------- ?>

        <?php if($mode == 4){ ?>
            <?php echo form_open('/admin/change', array('class' => 'form-signin ui-block p20', 'id' => 'login_form')); ?>
            <?php echo validation_errors('<span class="label label-danger block my5 py5">', '</span>'); ?>
            <?php if ($error) echo '<span class="label label-danger block my5 py5">'.lang('cms_user_error_'.$error).'</span>'; ?>

            <input type="hidden" name="w_hash" value="<?php echo $hash; ?>">
            <input type="password" class="form-control" placeholder="<?php echo lang('cms_user_form_8'); ?>" name="w_pass_new" value="<?php echo set_value('w_pass_new'); ?>">
            <input type="password" class="form-control mt5" placeholder="<?php echo lang('cms_user_form_9'); ?>" name="w_pass_confirm" value="<?php echo set_value('w_pass_confirm'); ?>">
            <button data-sitekey="<?php echo @conf('recaptcha'); ?>" data-callback="onSubmit" class="g-recaptcha btn btn-primary btn-block mt10"><?php echo lang('cms_user_form_10'); ?></button>
            <div class="wrapper-center mt10"><a href="/admin"><?php echo lang('cms_user_form_7'); ?></a></div>

            </form>

        <?php } ?>

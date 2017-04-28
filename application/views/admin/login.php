        <div class="logo"><img src="/public/admin/img/logo.png" width="235" height="32" /></div>
        
        <?php if($mode == 1){ ?>
        <?php echo form_open('', array('class' => 'form-signin ui-block p20')); ?>
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
        <button class="btn btn-primary btn-block mt20" type="submit"><?php echo lang('cms_user_form_5'); ?></button>

        </form>
        
        <?php } ?>
        
        <?php //--------------------------------------------------------------------------------- ?>
        
        <?php if($mode == 2){ ?>
        <?php echo form_open('/admin/remember', array('class' => 'form-signin ui-block p20')); ?>
        <?php echo validation_errors('<span class="label label-danger block my5 py5">', '</span>'); ?>
        <?php if ($error) echo '<span class="label label-danger block my5 py5">'.lang('cms_user_error_'.$error).'</span>'; ?>
            
        <input type="text" class="form-control" placeholder="<?php echo lang('cms_user_form_1'); ?>" name="w_email">                
        <button class="btn btn-primary btn-block mt10" type="submit"><?php echo lang('cms_user_form_6'); ?></button>
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
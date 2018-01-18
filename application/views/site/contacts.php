<h3><?php echo lang('contacts_title'); ?></h3>
<div id="contacts_error"></div>
<?php echo form_open('',array('class' => '', 'id' => 'contacts_form')); ?>
    <div class="row">
        <div class="col">
            <div class="form-group">
                <input class="form-control" type="text" name="contacts_name" value="" placeholder="<?php echo lang('contacts_name'); ?>"  required>
                <div class="invalid-feedback">
                    Please choose a username.
                </div>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <input class="form-control" type="email" name="contacts_email" value="" placeholder="<?php echo lang('contacts_email'); ?>"  required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group">
                <textarea class="form-control" name="contacts_message" placeholder="<?php echo lang('contacts_message'); ?>" required></textarea>
            </div>
            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="<?php echo @file_conf('cms_recaptcha_sitekey'); ?>" data-size="invisible" data-callback="onSubmit"></div>
                <button type="submit" class="btn btn-default"><?php echo lang('contacts_submit'); ?></button>
            </div>
        </div>
    </div>
</form>
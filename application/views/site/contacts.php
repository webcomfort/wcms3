<div class="contact-form">
    <h3><?php echo lang('contacts_title'); ?></h3>
    <div id="contacts_error"></div>
    <?php echo form_open('#',array('class' => '', 'id' => 'contacts_form')); ?>
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <input class="inputbox require field-name" type="text" name="contacts_name" value="" placeholder="<?php echo lang('contacts_name'); ?>"/>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <input class="inputbox email field-email" type="text" name="contacts_email" value="" placeholder="<?php echo lang('contacts_email'); ?>*"/>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <input class="inputbox require field-subject" type="text" name="contacts_subject" value="" placeholder="<?php echo lang('contacts_subject_2'); ?>"/>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <textarea class="inputbox none field-message" name="contacts_message" placeholder="<?php echo lang('contacts_message'); ?>"></textarea>
                </div>
                <div class="form-group">
                    <button class="g-recaptcha button btn btn-default" data-sitekey="<?php echo @file_conf('cms_recaptcha_sitekey'); ?>" data-callback="onSubmit"><?php echo lang('contacts_submit'); ?></button>
                </div>
            </div>

        </div>
    </form>
</div>
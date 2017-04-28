<h6 class="m0 mb10"><?php echo $filter_name; ?></h6>
<?php echo form_open($filter_action, array('class' => 'm0')); ?>
    <select name="<?php echo $filter_field; ?>" class="span12 m0 form-control<?php echo $filter_class; ?>" onChange="this.form.submit();">
        <?php foreach ($filter_values AS $key => $value) { ?>
        <option value="<?php echo $key; ?>"<?php if ($filter_active == $key) echo ' selected'; ?>><?php echo $value; ?></option>
        <?php } ?>
    </select>
</form>
<div class="my_meta_control" id="clientdetails">
<!-- Intake Form -->
	<?php global $wpalchemy_media_access; ?>
	<label>Intake Form</label>
    <?php $mb->the_field('intake'); ?>
    <?php $wpalchemy_media_access->setGroupName('nn')->setInsertButtonLabel('Insert this PDF')->setTab('type'); ?>
 
    <p>
        <?php echo $wpalchemy_media_access->getField(array('name' => $mb->get_the_name(), 'value' => $mb->get_the_value())); ?>
        <?php echo $wpalchemy_media_access->getButton(array('label' => 'Upload PDF')); ?>
    </p>
<!-- Diagnosis -->
	<label>Diagnosis</label>
	<?php $metabox->the_field('diagnosis'); ?>
	<textarea name="<?php $metabox->the_name(); ?>" rows="3"><?php $metabox->the_value(); ?></textarea>
<!-- Triggers -->
	<label>Triggers</label>
	<?php $metabox->the_field('triggers'); ?>
	<textarea name="<?php $metabox->the_name(); ?>" rows="3"><?php $metabox->the_value(); ?></textarea>
<!-- Activities -->
	<label>Activities</label>
	<?php $metabox->the_field('activities'); ?>
	<textarea name="<?php $metabox->the_name(); ?>" rows="3"><?php $metabox->the_value(); ?></textarea>
<!-- Behavior Goals -->
	<fieldset>
		<legend><?php _e('Behavior Goals', 'wpaesm'); ?></legend>
		<p class="explain"><?php _e('Enter as many behavior goals as you need.  You can mark behavior goals as active to make them appear in employees\'s documentation reports.', 'wpaesm'); ?></p>

		<a href="#" class="dodelete-goal button"><?php _e('Remove All Behavior Goals', 'wpaesm'); ?></a>
	 
		<?php while($mb->have_fields_and_multi('goal')): ?>
		<?php $mb->the_group_open(); ?>
			<div class="goal">
				<?php $mb->the_field('goal_name'); ?>
				<label><?php _e('Goal', 'wpaesm'); ?></label>
				<input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
				<?php $mb->the_field('active'); ?>
				<label><input type="checkbox" name="<?php $mb->the_name(); ?>" value="active" <?php echo $mb->is_value('active')?' checked="checked"':''; ?>> Active</label>
				<a href="#" class="dodelete button">Remove Goal</a>
	 		</div>

		<?php $mb->the_group_close(); ?>
		<?php endwhile; ?>
		<p style="margin-bottom:15px; padding-top:5px;"><a href="#" class="docopy-goal button"><?php _e('Add Another Behavior Goal', 'wpaesm'); ?></a></p>
	</fieldset>
<!-- Sub Plan -->
	<label>Sub Plan</label>
    <?php
        $mb->the_field('sub');
        $mb_content = html_entity_decode($mb->get_the_value(), ENT_QUOTES, 'UTF-8');
        $mb_editor_id = sanitize_key($mb->get_the_name());
        $mb_settings = array('textarea_name'=>$mb->get_the_name(),'textarea_rows' => '5',);
        wp_editor( $mb_content, $mb_editor_id, $mb_settings );
    ?> 
<!-- Crisis Plan -->
	<label>Crisis Plan</label>
    <?php
        $mb->the_field('crisis');
        $mb_content = html_entity_decode($mb->get_the_value(), ENT_QUOTES, 'UTF-8');
        $mb_editor_id = sanitize_key($mb->get_the_name());
        $mb_settings = array('textarea_name'=>$mb->get_the_name(),'textarea_rows' => '5',);
        wp_editor( $mb_content, $mb_editor_id, $mb_settings );
    ?> 
</div>
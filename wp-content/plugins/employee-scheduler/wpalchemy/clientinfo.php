<div class="my_meta_control" id="clientcontact">

<table class="form-table">
	<tr>
		<th scope="row"><label><?php _e('School', 'wpaesm'); ?></label></th>
		<td>
			<input id="school" type="text" name="<?php $metabox->the_name('school'); ?>" value="<?php $metabox->the_value('school'); ?>"/>
		</td>
	</tr>
</table>

<a href="#" class="dodelete-address button"><?php _e('Remove All Addresses', 'wpaesm'); ?></a>
 
		<?php while($mb->have_fields_and_multi('address')): ?>
		<?php $mb->the_group_open(); ?>

<table class="form-table">

	<tr>
		<th scope="row"><label><?php _e('Address Description', 'wpaesm'); ?></label></th>
		<td>
			<input id="addressname" type="text" name="<?php $metabox->the_name('addressname'); ?>" value="<?php $metabox->the_value('addressname'); ?>"/>
			<p><span>Examples: home, school, weekend, etc.</span></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label><?php _e('Street Address', 'wpaesm'); ?></label></th>
		<td>
			<input id="address" type="text" name="<?php $metabox->the_name('address'); ?>" value="<?php $metabox->the_value('address'); ?>"/>
		</td>
	</tr>

	<tr>
		<th scope="row"><label><?php _e('City', 'wpaesm'); ?></label></th>
		<td>
			<input id="city" type="text" name="<?php $metabox->the_name('city'); ?>" value="<?php $metabox->the_value('city'); ?>"/>
		</td>
	</tr>

	<tr>
		<th scope="row"><label><?php _e('State', 'wpaesm'); ?></label></th>
		<td>
			<input id="state" type="text" name="<?php $metabox->the_name('state'); ?>" value="<?php $metabox->the_value('state'); ?>"/>
		</td>
	</tr>

	<tr>
		<th scope="row"><label><?php _e('Zip', 'wpaesm'); ?></label></th>
		<td>
			<input id="zip" type="text" name="<?php $metabox->the_name('zip'); ?>" value="<?php $metabox->the_value('zip'); ?>"/>
		</td>
	</tr>


</table>	

<a href="#" class="dodelete button"><?php _e('Remove This Address', 'wpaesm'); ?></a>
		<hr />

		<?php $mb->the_group_close(); ?>
		<?php endwhile; ?>
		<p><a href="#" class="docopy-address button"><?php _e('Add Another Address', 'wpaesm'); ?></a></p>

	<fieldset class="half left">
		<legend><?php _e('Phone Number(s)', 'wpaesm'); ?></legend>

		<a href="#" class="dodelete-clientphone button"><?php _e('Remove All Phone Numbers', 'wpaesm'); ?></a>
 
		<?php while($mb->have_fields_and_multi('clientphone')): ?>
		<?php $mb->the_group_open(); ?>
			<?php $mb->the_field('phonenumber'); ?>
			<label><?php _e('Phone Number', 'wpaesm'); ?></label>
			<p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>

			<?php $mb->the_field('phonetype'); ?>
			<label><?php _e('Phone Number Type', 'wpaesm'); ?></label>
			<?php $selected = ' selected="selected"'; ?>
			<select name="<?php $mb->the_name(); ?>">
				<option value=""></option>
				<option value="Cell" <?php $mb->the_select_state('cell'); ?>>Cell</option>
				<option value="Home" <?php $mb->the_select_state('home'); ?>>Home</option>
				<option value="Work" <?php $mb->the_select_state('work'); ?>>Work</option>
				<option value="School" <?php $mb->the_select_state('school'); ?>>School</option>
				<option value="Other" <?php $mb->the_select_state('other'); ?>>Other</option>
			</select>

		<a href="#" class="dodelete button"><?php _e('Remove This Phone Number', 'wpaesm'); ?></a>
		<hr />

		<?php $mb->the_group_close(); ?>
		<?php endwhile; ?>
		<p><a href="#" class="docopy-clientphone button"><?php _e('Add Another Phone Number', 'wpaesm'); ?></a></p>
	</fieldset>

	<fieldset class="half">
		<legend><?php _e('Email Address(es)', 'wpaesm'); ?></legend>
		<a href="#" class="dodelete-clientemail button"><?php _e('Remove All Email Addresses', 'wpaesm'); ?></a>
 
		<?php while($mb->have_fields_and_multi('clientemail')): ?>
		<?php $mb->the_group_open(); ?>
			<?php $mb->the_field('emailaddress'); ?>
			<label><?php _e('Email Address', 'wpaesm'); ?></label>
			<p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>
			
			<?php $mb->the_field('emailtype'); ?>
			<label><?php _e('Email Address Type', 'wpaesm'); ?></label>
			<?php $selected = ' selected="selected"'; ?>
			<select name="<?php $mb->the_name(); ?>">
				<option value=""></option>
				<option value="Personal" <?php $mb->the_select_state('personal'); ?>>Personal</option>
				<option value="School" <?php $mb->the_select_state('school'); ?>>School</option>
				<option value="Work" <?php $mb->the_select_state('work'); ?>>Work</option>
				<option value="Other" <?php $mb->the_select_state('other'); ?>>Other</option>
			</select>

		<a href="#" class="dodelete button"><?php _e('Remove This Email Address', 'wpaesm'); ?></a>
		<hr />

		<?php $mb->the_group_close(); ?>
		<?php endwhile; ?>
		<p><a href="#" class="docopy-clientemail button"><?php _e('Add Another Email Address', 'wpaesm'); ?></a></p>
	</fieldset>

<div style="clear:both"></div>


<!-- Parents/Guardians -->
<fieldset>
	<legend><?php _e('Parents/Guardians', 'wpaesm'); ?></legend>
	<p class="explain"><?php _e('You can enter as many parents/guardians as you need', 'wpaesm'); ?></p>

	<a href="#" class="dodelete-parent button"><?php _e('Remove All Parents/Guardians', 'wpaesm'); ?></a>
	<?php while($mb->have_fields_and_multi('parent')): ?>
	<?php $mb->the_group_open(); ?>

		<div class="parent clearfix">

		<?php $mb->the_field('name'); ?>
		<label><?php _e('Name', 'wpaesm'); ?></label>
		<p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>

		<?php $mb->the_field('relation'); ?>
		<label><?php _e('Relation to Client', 'wpaesm'); ?></label>
		<p class="explain"><?php _e('Mother, Father, Nanny, etc.', 'wpaesm'); ?></p>
		<p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>

		<fieldset class="half left">
			<legend><?php _e('Phone Number(s)', 'wpaesm'); ?></legend>

			<a href="#" class="dodelete-phone button"><?php _e('Remove All Phone Numbers', 'wpaesm'); ?></a>
	 
			<?php while($mb->have_fields_and_multi('phone')): ?>
			<?php $mb->the_group_open(); ?>
				<?php $mb->the_field('phonenumber'); ?>
				<label><?php _e('Phone Number', 'wpaesm'); ?></label>
				<p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>

				<?php $mb->the_field('phonetype'); ?>
				<label><?php _e('Phone Number Type', 'wpaesm'); ?></label>
				<?php $selected = ' selected="selected"'; ?>
				<select name="<?php $mb->the_name(); ?>">
					<option value=""></option>
					<option value="Cell" <?php $mb->the_select_state('cell'); ?>>Cell</option>
					<option value="Home" <?php $mb->the_select_state('home'); ?>>Home</option>
					<option value="Work" <?php $mb->the_select_state('work'); ?>>Work</option>
					<option value="Other" <?php $mb->the_select_state('other'); ?>>Other</option>
				</select>

			<a href="#" class="dodelete button"><?php _e('Remove This Phone Number', 'wpaesm'); ?></a>
			<hr />

			<?php $mb->the_group_close(); ?>
			<?php endwhile; ?>
			<p><a href="#" class="docopy-phone button"><?php _e('Add Another Phone Number', 'wpaesm'); ?></a></p>
		</fieldset>

		<fieldset class="half">
			<legend><?php _e('Email Address(es)', 'wpaesm'); ?></legend>
			<a href="#" class="dodelete-email button"><?php _e('Remove All Email Addresses', 'wpaesm'); ?></a>
	 
			<?php while($mb->have_fields_and_multi('email')): ?>
			<?php $mb->the_group_open(); ?>
				<?php $mb->the_field('emailaddress'); ?>
				<label><?php _e('Email Address', 'wpaesm'); ?></label>
				<p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>
				
				<?php $mb->the_field('emailtype'); ?>
				<label><?php _e('Email Address Type', 'wpaesm'); ?></label>
				<?php $selected = ' selected="selected"'; ?>
				<select name="<?php $mb->the_name(); ?>">
					<option value=""></option>
					<option value="Personal" <?php $mb->the_select_state('personal'); ?>>Personal</option>
					<option value="Work" <?php $mb->the_select_state('work'); ?>>Work</option>
					<option value="Other" <?php $mb->the_select_state('other'); ?>>Other</option>
				</select>

			<a href="#" class="dodelete button"><?php _e('Remove This Email Address', 'wpaesm'); ?></a>
			<hr />

			<?php $mb->the_group_close(); ?>
			<?php endwhile; ?>
			<p><a href="#" class="docopy-email button"><?php _e('Add Another Email Address', 'wpaesm'); ?></a></p>
		</fieldset>

		<p class="clearfix"><a href="#" class="dodelete button"><?php _e('Remove This Parent/Guardian', 'wpaesm'); ?></a></p>
 
		</div>
	<?php $mb->the_group_close(); ?>
	<?php endwhile; ?>
	<p style="margin-bottom:15px; padding-top:5px; clear=both; width: 100%"><a href="#" class="docopy-parent button"><?php _e('Add Another Parent/Guardian', 'wpaesm'); ?></a></p>





</div>
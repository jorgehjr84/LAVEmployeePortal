<div class="my_meta_control" id="clientcontact">
 
	<a href="#" class="dodelete-ratings button">Remove All</a>
 
	<?php while($mb->have_fields_and_multi('ratings')): ?>
	<?php $mb->the_group_open(); ?>

		<div class="goalrating">
 
	 		<table class="form-table">
				<tr><?php $mb->the_field('goal'); ?>
					<th scope="row"><label>Behavior Goal</label></th>
					<td>
						<input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
					</td>
				</tr>
				<tr><?php $mb->the_field('score'); ?>
					<th scope="row"><label>Score</label></th>
					<td>
						<input type="radio" name="<?php $mb->the_name(); ?>" value="N/A"<?php echo $mb->is_value('1')?' checked="checked"':''; ?>/> N/A
						<input type="radio" name="<?php $mb->the_name(); ?>" value="1"<?php echo $mb->is_value('1')?' checked="checked"':''; ?>/> 1
						<input type="radio" name="<?php $mb->the_name(); ?>" value="2"<?php echo $mb->is_value('2')?' checked="checked"':''; ?>/> 2
						<input type="radio" name="<?php $mb->the_name(); ?>" value="3"<?php echo $mb->is_value('3')?' checked="checked"':''; ?>/> 3
						<input type="radio" name="<?php $mb->the_name(); ?>" value="4"<?php echo $mb->is_value('4')?' checked="checked"':''; ?>/> 4
						<input type="radio" name="<?php $mb->the_name(); ?>" value="5"<?php echo $mb->is_value('5')?' checked="checked"':''; ?>/> 5
			 		</td>
			 	</tr>
			</table>
			<a href="#" class="dodelete button">Remove Rating</a>

		</div>
 
	<?php $mb->the_group_close(); ?>
	<?php endwhile; ?>
 
	<p style="margin-bottom:15px; padding-top:5px;"><a href="#" class="docopy-ratings button">Add Rating</a></p>


</div>
jQuery(document).ready(function() {
 	jQuery('#thisdate').datetimepicker({
 		timepicker:false,
 		mask: true,
 		format:'Y-m-d'
 	});
 	jQuery('#thisdate2').datetimepicker({
 		timepicker:false,
 		mask: true,
 		format:'Y-m-d'
 	});
	jQuery('.esod-date').datetimepicker({
		timepicker:false,
		mask: false,
		format:'Y-m-d'
	});
 	jQuery('#repeatuntil').datetimepicker({
 		timepicker:false,
 		mask: true,
 		format:'Y-m-d'
 	});
 	jQuery('#starttime').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	jQuery('#endtime').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	jQuery('.starttime').datetimepicker({
		datepicker:false,
		// mask: true,
		format:'H:i',
		step: 15,
	});
	jQuery('.endtime').datetimepicker({
		datepicker:false,
		// mask: true,
		format:'H:i',
		step: 15,
	});
	jQuery('#clockin').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	jQuery('#clockout').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	if(jQuery("#repeat").is(':checked'))
    	jQuery("#repeatfields").show();  // checked
	else
	    jQuery("#repeatfields").hide();  // unchecked
		jQuery('#repeat').onchange = function() {
	    jQuery('#repeatfields').style.display = this.checked ? 'block' : 'none';
	};

	var availabilityTemplate = jQuery('#availability-template').html();

	// Add a new repeating section on user profile (only used by On Demand add-on)
	jQuery('.repeat-availability').click(function(e){
		e.preventDefault();
		var repeating = jQuery(availabilityTemplate);
		var lastRepeatingGroup = jQuery('.repeating-availability').last();
		var idx = lastRepeatingGroup.index();
		var attrs = ['for', 'id', 'name'];
		var tags = repeating.find('input, label, select');
		tags.each(function() {
			var section = jQuery(this);
			jQuery.each(attrs, function(i, attr) {
				var attr_val = section.attr(attr);
				if (attr_val) {
					section.attr( attr, attr_val.replace( /\[\d+\]\[/, '\['+( idx + 1 )+'\]\[' ) );
				}
			})
		});

		lastRepeatingGroup.after(repeating);
		repeating.find('.starttime').datetimepicker({
			datepicker:false,
			format:'H:i',
			step: 15,
		});
		repeating.find('.endtime').datetimepicker({
			datepicker:false,
			format:'H:i',
			step: 15,
		});
	});

	jQuery('body').on('click', 'a.remove-availability', function(e){
		e.preventDefault();
		jQuery(this).closest('.repeating-availability').remove();
	});

	jQuery('#esod-employer-work-request').on( 'submit', function (e){
		jQuery('#esod-loading').show();
	});

});




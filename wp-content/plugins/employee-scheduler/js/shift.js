jQuery(document).ready(function ($) {

    // apply changes to all of the shifts in this series
    $('#subsequent').on('click', function (e) {
        
        var url = shiftajax.ajaxurl;
        var shift = $('#post_ID').val();

        var data = {
            'action': 'wpaesp_apply_changes_to_series',
            'shift': shift,
        };
        console.log(shift);

        $.post(url, data, function (response) {
            $("#results").html(response);

        });

    });
});
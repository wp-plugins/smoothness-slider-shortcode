jQuery(document).ready(function(){
    var $ = jQuery.noConflict();

	$('#add_browse_button').click(function(){
		$('#add_browse_button').before('<br /><input type="file" name="images[]" />');
	});

	$('#example').dataTable();

	$('.change-animation').change(function(){
		var id = $(this).attr('anim_id');

		$('#animation_form'+id).submit();
	});

    $('#addLinkDialog').dialog({
        'autoOpen': false,
        'buttons': {
            'Add': function(){
                jQuery.ajax({
                    url: "admin-ajax.php",
                    type: "POST",
                    data: 'action=add_link&id=' + $('#imageID').val() + '&link=' + $('#imageLink').val(),
                    success: function(response){
                        if(response == 1 || response == 0)
                            $( '#addLinkDialog' ).dialog( "close" );
                        else{
                            alert('Error adding link');
                            $( '#addLinkDialog' ).dialog( "close" );
                        }
                    }
                });
            },
            'Close': function(){
                $( this ).dialog( "close" );
            }
        }
    });

    $('.add-link').click(function(e){
        e.preventDefault();

        var image_id = $(this).attr('image_id');
        var current_link = $(this).attr('current_link');

        $('#imageID').val(image_id);
        $('#imageLink').val(current_link);
        $('#addLinkDialog').dialog('open');
    });

    $('.image-title').keydown(function(e){
        var image_id = $(this).attr('image_id');
        var title = $(this).val();

        if(e.keyCode == 13){
            jQuery.ajax({
                url: "admin-ajax.php",
                type: "POST",
                data: 'action=set_title&id=' + image_id + '&title=' + title,
                success: function(response){
                }
            });
        }
    });
});

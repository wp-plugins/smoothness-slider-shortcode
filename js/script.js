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
});

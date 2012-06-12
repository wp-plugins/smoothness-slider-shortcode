<?php
require_once '../../../../wp-load.php';

$show_direction_nav = get_option('sss-show_direction_nav');
$show_control_nav = get_option('sss-show_control_nav');
?>

jQuery(function() {
	jQuery('#sss-slider').nivoSlider({
		directionNav: <?php echo ($show_direction_nav === false || $show_direction_nav === '1' ? 'true' : 'false') ?>,
		controlNav: <?php echo ($show_control_nav === false || $show_control_nav === '1' ? 'true' : 'false') ?>
	});

	jQuery("a.fancybox").fancybox();
});

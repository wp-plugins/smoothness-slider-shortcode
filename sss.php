<?php
/* 
Plugin Name: Smoothness Slider Shortcode
Plugin URI: http://www.interlacelab.com/wordpress-smooth-slider-shortcode/ 
Version: v1.1.4
Author: <a href="http://www.interlacelab.com">Noel Jarencio.</a>
Description: Smoothness Slider Shortcode is a WordPress Plugin for creating dynamic slider for posts and pages. You can place the slider to any post(s) or page(s) you want by placing the slider shortcode. Powerful features includes searchable photo upload, show/hide images in slider, and each image can be customize with different animation.
 
Copyright 2012 InterlaceLab.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

if (!class_exists("SmoothnessSliderShortcode")) {
	class SmoothnessSliderShortcode {
		var $table;

		function SmoothnessSliderShortcode() { //constructor
			$this->table = 'sss_slider';
		}

		function init() {
			global $wpdb;

			$wpdb->query("
				CREATE TABLE IF NOT EXISTS `{$this->table}` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`filename` varchar(255) NOT NULL,
					`active` tinyint(1) unsigned NOT NULL DEFAULT '1',
					`animation` varchar(100) NOT NULL DEFAULT 'random',
					`date_uploaded` datetime NOT NULL,
					PRIMARY KEY (`id`),
					KEY `filename` (`filename`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			");

			update_option('sss-slider_width', 980);
			update_option('sss-slider_height', 330);
			update_option('sss-gallery_img_width', 280);
			update_option('sss-gallery_img_height', 180);
		}

		function admin_css_js(){
			wp_register_style('jquery-ui-style', plugins_url('css/smoothness/jquery-ui-1.8.16.custom.css', __FILE__));
			wp_enqueue_style( 'jquery-ui-style');

			wp_register_style('dataTables-style', plugins_url('css/jquery.dataTables.css', __FILE__));
			wp_enqueue_style( 'dataTables-style');

			wp_register_style('sss-style', plugins_url('css/style.css', __FILE__));
			wp_enqueue_style( 'sss-style');

			wp_enqueue_script(
				'jquery-ui',
				'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
				array('jquery')
			);
			wp_enqueue_script(
				'dataTables',
				plugins_url('js/jquery.dataTables.min.js', __FILE__),
				array('jquery')
			);
			wp_enqueue_script(
				'sss-script',
				plugins_url('js/script.js', __FILE__),
				array('jquery')
			);
		}

		function add_front_css(){
			wp_register_style('nivo-style', plugins_url('js/nivoslider/nivo-slider.css', __FILE__));
			wp_enqueue_style( 'nivo-style');

			wp_register_style('nivo-theme', plugins_url('js/nivoslider/themes/default/default.css', __FILE__));
			wp_enqueue_style( 'nivo-theme');

			wp_register_style('shadow', plugins_url('css/shadows.css', __FILE__));
			wp_enqueue_style( 'shadow');

			wp_register_style('lightbox-style', plugins_url('js/fancybox/jquery.fancybox-1.3.4.css', __FILE__));
			wp_enqueue_style( 'lightbox-style');

			wp_register_style('front-style', plugins_url('css/front.css', __FILE__));
			wp_enqueue_style( 'front-style');

			echo '<!--[if IE 8]>
				<link rel="stylesheet" href="'.plugins_url('shadows-for-ie/shadows-ie.css', __FILE__).'" />
			<![endif]-->';
		}

		function add_front_js(){
			wp_enqueue_script(
				'nivo-slider',
				plugins_url('js/nivoslider/jquery.nivo.slider.pack.js', __FILE__),
				array('jquery')
			);

			wp_enqueue_script(
				'sss-front-script',
				plugins_url('js/front.php', __FILE__),
				array('jquery')
			);

			wp_enqueue_script(
				'fancybox-script',
				plugins_url('js/fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__),
				array('jquery')
			);

			wp_enqueue_script(
				'jquery-mousewheel',
				plugins_url('js/fancybox/jquery.mousewheel-3.0.4.pack.js', __FILE__),
				array('jquery')
			);

			echo '<!--[if IE 8]>
				<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>

				<script src="'.plugins_url('js/jquery.transform-0.9.3.min.js', __FILE__).'"></script>
				<script src="'.plugins_url('js/shadows-ie.js', __FILE__).'"></script>
			<![endif]-->';
		}

        function slider_settings() {
			global $wpdb;

			require_once 'lib/phpthumb/ThumbLib.inc.php';

			set_time_limit(0);

			if(isset($_POST['upload'])){
				foreach ($_FILES["images"]["error"] as $key => $error) {
					if ($error == UPLOAD_ERR_OK) {
						$tmp_name = $_FILES["images"]["tmp_name"][$key];
						$name = $_FILES["images"]["name"][$key];

						$filename_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE filename = '$name'" ) );

						if(!$filename_exists){
							$slider_width = get_option('sss-slider_width');
							$slider_height = get_option('sss-slider_height');

							try
							{
								$thumb = PhpThumbFactory::create($tmp_name);
							}
							catch (Exception $e)
							{
								// handle error here however you'd like
							}

							$thumb->adaptiveResize($slider_width, $slider_height);
							$thumb->save("../wp-content/uploads/$name");

							$wpdb->insert(
								$this->table,
								array(
									'filename' => $name,
									'date_uploaded' => date('Y-m-d H:i:s')
								)
							);
						}
					}
				}
			}

			if(isset($_GET['deactivate'])){
				$wpdb->update(
					$this->table,
					array( 'active' => 0),
					array( 'id' => strip_tags($_GET['deactivate']) ),
					array( '%d'	), 
					array( '%d' ) 
				);
			}elseif(isset($_GET['activate'])){
				$wpdb->update(
					$this->table,
					array( 'active' => 1),
					array( 'id' => strip_tags($_GET['activate']) ),
					array( '%d'	), 
					array( '%d' ) 
				);
			}

			if(isset($_POST['animation'])){
				foreach($_POST['animation'] as $key => $effect){
					$wpdb->update(
						$this->table,
						array( 'animation' => $effect),
						array( 'id' => $key ),
						array( '%s'	), 
						array( '%d' ) 
					);
				}
			}

			if(isset($_GET['delete'])){
				$query = $wpdb->get_row("
					SELECT * FROM {$this->table}
					WHERE id = " . strip_tags($_GET['delete']) . "
				");
				@unlink("../wp-content/uploads/" . $query->filename);
				$wpdb->query("
					DELETE FROM {$this->table}
					WHERE id = " . strip_tags($_GET['delete']) . "
				");
			}

			if(isset($_POST['save_settings'])){
				update_option('sss-slider_width', strip_tags($_POST['slider_width']));
				update_option('sss-slider_height', strip_tags($_POST['slider_height']));
				update_option('sss-gallery_img_width', strip_tags($_POST['gallery_img_width']));
				update_option('sss-gallery_img_height', strip_tags($_POST['gallery_img_height']));

				update_option('sss-show_direction_nav', (isset($_POST['show_direction_nav']) ? 1 : 0));
				update_option('sss-show_control_nav', (isset($_POST['show_control_nav']) ? 1 : 0));
			}
			?>
			<div class="wrap sss-manager">
				<p><h2><?php echo __( 'Smoothness Slider', 'sss' ) ?></h2></p>


				<div class="postbox  hide-if-js" style="display: block; ">
					<h3 class="hndle"><span>Upload New Photo</span></h3>
					<div class="inside">
						<form action="" method="POST" enctype="multipart/form-data">
							<input type="file" name="images[]" />
							<span class="add-button" id="add_browse_button"></span>
							<p><input type="submit" name="upload" class="button-primary" value="Upload" /></p>
						</form>
					</div>
				</div>

				<div class="postbox  hide-if-js" style="display: block; ">
					<h3 class="hndle"><span>Gallery</span></h3>
					<div class="inside">
						<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
							<thead>
								<tr>
									<th class="text-left">ID</th>
									<th class="text-left">Image</th>
									<th class="text-left">Filename</th>
									<th class="text-left">Active</th>
									<th class="text-left">Animation</th>
									<th>Date</th>
									<th>Delete</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$query = $wpdb->get_results("
								SELECT * FROM {$this->table}
							");

							foreach($query as $row){
							?>
								<tr class="gradeA">
									<td><?php echo $row->id ?></td>
									<td><img src="<?php echo plugins_url('show_image.php', __FILE__) ?>?filename=uploads/<?php echo $row->filename ?>&w=80&h=80" /></td>
									<td><?php echo $row->filename ?></td>
									<td>
										<?php if($row->active == 1){ ?>
										<a href="?page=slider-settings&deactivate=<?php echo $row->id ?>" class="active-icon" title="Click to deactivate"></a>
										<?php }else{ ?>
										<a href="?page=slider-settings&activate=<?php echo $row->id ?>">Activate</a>
										<?php } ?>
									</td>
									<td>
										<form action="" method="post" id="animation_form<?php echo $row->id ?>">
										<select name="animation[<?php echo $row->id ?>]" class="change-animation" anim_id="<?php echo $row->id ?>">
											<option value="random" <?php echo $row->animation == 'random' ? 'selected="selected"' : '' ?>>Random</option>
											<option value="fold" <?php echo $row->animation == 'fold' ? 'selected="selected"' : '' ?>>Fold</option>
											<option value="fade" <?php echo $row->animation == 'fade' ? 'selected="selected"' : '' ?>>Fade</option>
											<option value="sliceDown" <?php echo $row->animation == 'sliceDown' ? 'selected="selected"' : '' ?>>Slice Down</option>
											<option value="sliceDownLeft" <?php echo $row->animation == 'sliceDownLeft' ? 'selected="selected"' : '' ?>>Slice Down Left</option>
											<option value="sliceUp" <?php echo $row->animation == 'sliceUp' ? 'selected="selected"' : '' ?>>Slice Up</option>
											<option value="sliceUpLeft" <?php echo $row->animation == 'sliceUpLeft' ? 'selected="selected"' : '' ?>>Slice Up Left</option>
											<option value="sliceUpDown" <?php echo $row->animation == 'sliceUpDown' ? 'selected="selected"' : '' ?>>Slice Up Down</option>
											<option value="sliceUpDownLeft" <?php echo $row->animation == 'sliceUpDownLeft' ? 'selected="selected"' : '' ?>>Slice Up Down Left</option>
											<option value="slideInRight" <?php echo $row->animation == 'slideInRight' ? 'selected="selected"' : '' ?>>Slide In Right</option>
											<option value="slideInLeft" <?php echo $row->animation == 'slideInLeft' ? 'selected="selected"' : '' ?>>Slide In Left</option>
											<option value="boxRandom" <?php echo $row->animation == 'boxRandom' ? 'selected="selected"' : '' ?>>Box Random</option>
											<option value="boxRain" <?php echo $row->animation == 'boxRain' ? 'selected="selected"' : '' ?>>Box Rain</option>
											<option value="boxRainReverse" <?php echo $row->animation == 'boxRainReverse' ? 'selected="selected"' : '' ?>>Box Rain Reverse</option>
											<option value="boxRainGrow" <?php echo $row->animation == 'boxRainGrow' ? 'selected="selected"' : '' ?>>Box Rain Grow</option>
											<option value="boxRainGrowReverse" <?php echo $row->animation == 'boxRainGrowReverse' ? 'selected="selected"' : '' ?>>Box Rain Grow Reverse</option>
										</select>
										</form>
									</td>
									<td class="center"><?php echo $row->date_uploaded ?></td>
									<td class="center"><a href="?page=slider-settings&delete=<?php echo $row->id ?>" class="delete-icon"></a></td>
								</tr>
							<?php } ?>
							</tbody>
							<tfoot>
								<tr>
									<th class="text-left">ID</th>
									<th class="text-left">Image</th>
									<th class="text-left">Filename</th>
									<th class="text-left">Active</th>
									<th class="text-left">Animation</th>
									<th>Date</th>
									<th>Delete</th>
								</tr>
							</tfoot>
						</table>
						<div style="clear: both"></div>
					</div>
				</div>

				<div style="clear: both"></div>
				<div class="postbox  hide-if-js" style="display: block; ">
					<h3 class="hndle"><span>Settings</span></h3>
					<div class="inside">
						<form action="" method="post">
							<?php
							$slider_width = get_option('sss-slider_width');
							$slider_height = get_option('sss-slider_height');
							$gallery_img_width = get_option('sss-gallery_img_width');
							$gallery_img_height = get_option('sss-gallery_img_height');
							$show_direction_nav = get_option('sss-show_direction_nav');
							$show_control_nav = get_option('sss-show_control_nav');
							?>
							<p>
								<label for="slider_width">Slider Width: </label>
								<input type="text" name="slider_width" id="slider_width" value="<?php echo $slider_width ? $slider_width : 980 ?>" />
							</p>
							<p>
								<label for="slider_height">Slider Height: </label>
								<input type="text" name="slider_height" id="slider_height" value="<?php echo $slider_height ? $slider_height : 330 ?>" />
							</p>
							<p>
								<label for="gallery_img_width">Gallery Image Width: </label>
								<input type="text" name="gallery_img_width" id="gallery_img_width" value="<?php echo $gallery_img_width ? $gallery_img_width : 280 ?>" />
							</p>
							<p>
								<label for="gallery_img_height">Gallery Image Height: </label>
								<input type="text" name="gallery_img_height" id="gallery_img_height" value="<?php echo $gallery_img_height ? $gallery_img_height : 180 ?>" />
							</p>
							<p>
								<label for="show_direction_nav">
									<input type="checkbox" name="show_direction_nav" id="show_direction_nav" <?php echo $show_direction_nav === false || $show_direction_nav === '1' ? 'checked="checked"' : '' ?> />
									Show previous/next navigation
								</label>
							</p>
							<p>
								<label for="show_control_nav">
									<input type="checkbox" name="show_control_nav" id="show_control_nav" <?php echo $show_control_nav === false || $show_control_nav === '1' ? 'checked="checked"' : '' ?> />
									Show control navigation
								</label>
							</p>
							<p><input type="submit" name="save_settings" class="button-primary" value="Save" /></p>
						</form>
					</div>
				</div>
			</div>
			<!-- .wrap .sss-manager -->
			<?php
        }

		function slider_shortcode(){
			global $wpdb;

			$query = $wpdb->get_results("
				SELECT * FROM {$this->table}
				WHERE active = 1
			");

			$width = get_option('sss-slider_width');
			$height = get_option('sss-slider_height');
			$width = $width ? $width : 980;
			$height = $height ? $height : 330;

			$imgs = '';
			foreach($query as $row){
				$imgs .= '<img src="'.get_bloginfo('wpurl').'/wp-content/uploads/'.$row->filename.'" '.($row->animation != 'random' ? 'data-transition="' . $row->animation . '"' : '')." />\n";
			}

			$html = '<div class="slider-wrapper theme-default">
				<div id="sss-slider" class="nivoSlider" style="width: '.$width.'px; height: '.$height.'px">
					'.$imgs.'
				</div>
			</div>
			<div style="clear: both"></div>';

			return $html;
		}

		function gallery_shortcode(){
			global $wpdb;

			$gallery_img_width = get_option('sss-gallery_img_width');
			$gallery_img_height = get_option('sss-gallery_img_height');
			$gallery_img_width = $gallery_img_width ? $gallery_img_width : 280;
			$gallery_img_height = $gallery_img_height ? $gallery_img_height : 180;

			$query = $wpdb->get_results("
				SELECT * FROM {$this->table}
				WHERE active = 1
			");

			$imgs = '';
			foreach($query as $row){
				$imgs .= '<div class="img-wrap RightWarpShadow RWLarge RWNormal" style="width: '.$gallery_img_width.'; height: '.$gallery_img_height.'"><a href="'.get_bloginfo('wpurl').'/wp-content/uploads/'.$row->filename.'" class="fancybox"><img src="'.plugins_url('show_image.php', __FILE__).'?filename=uploads/'.$row->filename.'&w='.$gallery_img_width.'&h='.$gallery_img_height.'" alt="" /></a></div>'."\n";
			}

			$html = '<div class="sss-container">
				'.$imgs.'
			</div>';

			return $html;
		}
	}
}

if (class_exists("SmoothnessSliderShortcode")) {
	$sss = new SmoothnessSliderShortcode();
}

//Initialize the admin and users panel
if (!function_exists("sss_menu")) {
	function sss_menu() {
		global $sss;
		if (!isset($sss)) {
			return;
		}

		if (function_exists('add_menu_page')) {
            add_menu_page(__('Slider','sss'), __('Slider','sss'), 'edit_posts', 'slider-settings', array(&$sss, 'slider_settings'), plugin_dir_url( __FILE__ ).'/images/application_view_gallery.png' );
		}
	}
}

//Actions and Filters	
if (isset($sss)) {
	//Actions
	add_action( 'admin_menu', 'sss_menu');
	add_action( 'admin_head', array(&$sss, 'admin_css_js'));
	add_action( 'wp_head', array(&$sss, 'add_front_css'));
	add_action( 'wp_footer', array(&$sss, 'add_front_js'));
	add_action( 'activate_smoothness-slider-shortcode/sss.php',  array(&$sss, 'init'));

	add_shortcode( 'smoothness-slider', array(&$sss, 'slider_shortcode') );

	add_shortcode( 'smoothness-gallery', array(&$sss, 'gallery_shortcode') );
}
?>

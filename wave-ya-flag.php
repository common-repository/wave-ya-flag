<?php
/*
	Plugin Name: Wave Ya Flag
	Plugin URI: http://flashapplications.de/?p=668
	Description: Show your opinion with your flag. This Widget shows a 3D-Flag of an Image that you want to show. 
	Version: 1.0
	Author: Jörg Sontag
	Author URI: http://www.flashapplications.de
	
	Copyright 2010, Jörg Sontag Flashapplications

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// check for WP context
if ( !defined('ABSPATH') ){ die(); }

//initially set the options
function wp_flag_install () {
	
    $newoptions = get_option('wpflag_options');
	$newoptions['width'] = '200';
	$newoptions['height'] = '150';
	$newoptions['bgcolor'] = 'ffffff';
	$newoptions['trans'] = 'false';
	$newoptions['link'] = '';
	$newoptions['media'] = '';
	add_option('wpflag_options', $newoptions);
	
	// widget options
	$widgetoptions = get_option('wpflag_widget');
	$newoptions['width'] = '200';
	$newoptions['height'] = '150';
	$newoptions['bgcolor'] = 'ffffff';
	$newoptions['trans'] = 'false';
	$newoptions['link'] = '';
    $newoptions['media'] = '';
	add_option('wpflag_widget', $newoptions);
}

// add the admin page
function wp_flag_add_pages() {
	add_options_page('Wave Ya Flag', 'Wave Ya Flag', 8, __FILE__, 'wp_flag_options');
}

// replace tag in content with flag (non-shortcode version for WP 2.3.x)
function wp_flag_init($content){
	if( strpos($content, '[WP-FLAG]') === false ){
		return $content;
	} else {
		$code = wp_flag_createflashcode(false);
		$content = str_replace( '[WP-FLAG]', $code, $content );
		return $content;
	}
}

// template function
function wp_flag_insert( $atts=NULL ){
	echo wp_flag_createflashcode( false, $atts );
}

// shortcode function
function wp_flag_shortcode( $atts=NULL ){
	return wp_flag_createflashcode( false, $atts );
}

// piece together the flash code
function wp_flag_createflashcode( $widget=false, $atts=NULL ){
	// get the options
	if( $widget == true ){
		$options = get_option('wpflag_widget');
		$soname = "widget_so";
		$divname = "wpflagwidgetcontent";
		// get compatibility mode variable from the main options
		$mainoptions = get_option('wpflag_options');
	} else if( $atts != NULL ){
		$options = shortcode_atts( get_option('wpflag_options'), $atts );
		$soname = "shortcode_so";
		$divname = "wpflagwidgetcontent";
	} else {
		$options = get_option('wpflag_options');
		$soname = "so";
		$divname = "wpflagwidgetcontent";
	}

	// get some paths
	if( function_exists('plugins_url') ){ 
		// 2.6 or better
		$movie = plugins_url('wave-ya-flag/WaveYaFlag.swf');
		$path = plugins_url('wave-ya-flag/');
	} else {
		// pre 2.6
		$movie = get_bloginfo('wpurl') . "/wp-content/plugins/wave-ya-flag/WaveYaFlag.swf";
		$path = get_bloginfo('wpurl')."/wp-content/plugins/wave-ya-flag/";
	}
	// add random seeds to so name and movie url to avoid collisions and force reloading (needed for IE)
	$soname .= rand(0,9999999);
	$movie .= '?r=' . rand(0,9999999);
	$divname .= rand(0,9999999);
	// write flash tag
	if( $options['compmode']!='true' ){
		$flashtag = '<!-- SWFObject embed by Geoff Stearns geoff@deconcept.com http://blog.deconcept.com/swfobject/ -->';	
		$flashtag .= '<script type="text/javascript" src="'.$path.'swfobject.js"></script>';
		$flashtag .= '<div id="'.$divname.'">';
	
		$flashtag .= '</p><p>Wave Ya Flag by <a href="http://flashapplications.de/">Joerg Sontag Flashapplications</a> requires <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> 10 or better.</p></div>';
		$flashtag .= '<script type="text/javascript">';
		$flashtag .= 'var '.$soname.' = new SWFObject("'.$movie.'", "flag", "'.$options['width'].'", "'.$options['height'].'", "10", "#'.$options['bgcolor'].'");';
		if( $options['trans'] == 'true' ){
			$flashtag .= $soname.'.addParam("wmode", "transparent");';
		}
             
		$flashtag .= $soname.'.addVariable("link", "'.$options['link'].'");';
		$flashtag .= $soname.'.addVariable("media", "'.$options['media'].'");';
		$flashtag .= $soname.'.write("'.$divname.'");';
		$flashtag .= '</script>';
	} else {
		$flashtag = '<object type="application/x-shockwave-flash" data="'.$movie.'" width="'.$options['width'].'" height="'.$options['height'].'">';
		$flashtag .= '<param name="movie" value="'.$movie.'" />';
		$flashtag .= '<param name="bgcolor" value="#'.$options['bgcolor'].'" />';
		$flashtag .= '<param name="AllowScriptAccess" value="always" />';
		if( $options['trans'] == 'true' ){
			$flashtag .= '<param name="wmode" value="transparent" />';
		}
		$flashtag .= '<param name="flashvars" value="';
        $flashtag .= 'link='.$options['link'];
      	$flashtag .= '&amp;media='.$options['media'];
		$flashtag .= '" />';
		// alternate content
		$flashtag .= '</p><p>Wave Ya Flag by <a href="http://flashapplications.de/">Joerg Sontag Flashapplications</a> requires <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> 10 or better.</p></div>';
		$flashtag .= '</object>';
	}
	return $flashtag;
}

// options page
function wp_flag_options() {	
	$options = $newoptions = get_option('wpflag_options');
	// if submitted, process results
	if ( $_POST["wpflag_submit"] ) {
		$newoptions['width'] = strip_tags(stripslashes($_POST["width"]));
		$newoptions['height'] = strip_tags(stripslashes($_POST["height"]));
	 	 $newoptions['bgcolor'] = strip_tags(stripslashes($_POST["bgcolor"]));
        $newoptions['trans'] = strip_tags(stripslashes($_POST["trans"]));
        $newoptions['media'] = strip_tags(stripslashes($_POST["media"]));
		$newoptions['link'] = strip_tags(stripslashes($_POST["link"]));
	}
	// any changes? save!
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('wpflag_options', $options);
	}
	// options form
	echo '<form method="post">';
	echo "<div class=\"wrap\"><h2>Wave Ya Flag options</h2>";
	echo '<table class="form-table">';
	// width
	echo '<tr valign="top"><th scope="row">SWF Width</th>';
	echo '<td><input type="text" name="width" value="'.$options['width'].'" size="8"></input><br /></td></tr>';
	// height
	echo '<tr valign="top"><th scope="row">SWF Height</th>';
	echo '<td><input type="text" name="height" value="'.$options['height'].'" size="8"></input><br /></td></tr>';

	// Media URL
	echo '<tr valign="top"><th scope="row">Flag Image URL</th>';
	echo '<td><input type="text" name="media" value="'.$options['media'].'" size="200"></input></td></tr>';
	// background color
	echo '<tr valign="top"><th scope="row">Background color</th>';
	echo '<td><input type="text" name="bgcolor" value="'.$options['bgcolor'].'" size="8"></input><br />6 character hex color value</td></tr>';
	// transparent
	echo '<tr valign="top"><th scope="row">Use transparent Mode</th>';
	echo '<td><input type="checkbox" name="trans" value="true"';
	if( $options['trans'] == "true" ){ echo ' checked="checked"'; }
	echo '></input><br />Switches on Flash\'s wmode-transparent setting</td></tr>';
	// LINK URL
	echo '<tr valign="top"><th scope="row">Link URL</th>';
	echo '<td><input type="text" name="link" value="'.$options['link'].'" size="200"></input><br /></td></tr>';
    echo '<input type="hidden" name="wpflag_submit" value="true"></input>';
	echo '</table>';
	echo '<p class="submit"><input type="submit" value="Update Options &raquo;"></input></p>';
	echo '</div>';
	echo '</form>';
	
}

//uninstall all options
function wp_flag_uninstall () {
	delete_option('flag_options');
	delete_option('flag_widget');
}


// widget
function widget_init_wp_flag_widget() {
	// Check for required functions
	if (!function_exists('register_sidebar_widget'))
		return;

	function wp_flag_widget($args){
	    extract($args);
		$options = get_option('wpflag_widget');
		?>
	        <?php echo $before_widget; ?>
			<?php if( !empty($options['title']) ): ?>
				<?php echo $before_title . $options['title'] . $after_title; ?>
			<?php endif; ?>
			<?php
				if( !stristr( $_SERVER['PHP_SELF'], 'widgets.php' ) ){
					echo wp_flag_createflashcode(true);
				}
			?>
	        <?php echo $after_widget; ?>
		<?php
	}
	
	function wp_flag_widget_control() {
		$options = $newoptions = get_option('wpflag_widget');
		if ( $_POST["wpflag_widget_submit"] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST["wpflag_widget_title"]));
			$newoptions['width'] = strip_tags(stripslashes($_POST["wpflag_widget_width"]));
			$newoptions['height'] = strip_tags(stripslashes($_POST["wpflag_widget_height"]));
			$newoptions['bgcolor'] = strip_tags(stripslashes($_POST["wpflag_widget_bgcolor"]));
            $newoptions['trans'] = strip_tags(stripslashes($_POST["wpflag_widget_trans"]));
            $newoptions['media'] = strip_tags(stripslashes($_POST["wpflag_widget_media"]));
			$newoptions['link'] = strip_tags(stripslashes($_POST["wpflag_widget_link"]));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('wpflag_widget', $options);
		}
		$title = attribute_escape($options['title']);
		$width = attribute_escape($options['width']);
		$height = attribute_escape($options['height']);
		$bgcolor = attribute_escape($options['bgcolor']);
	    $trans = attribute_escape($options['trans']);
	    $media = attribute_escape($options['media']);
		$link = attribute_escape($options['link']);
		
		?>
			<p><label for="wpflag_widget_title"><?php _e('Title:'); ?> <input class="widefat" id="wpflag_widget_title" name="wpflag_widget_title" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="wpflag_widget_width"><?php _e('SWF width:'); ?> <input class="widefat" id="wpflag_widget_width" name="wpflag_widget_width" type="text" value="<?php echo $width; ?>" /></label></p>
			<p><label for="wpflag_widget_height"><?php _e('SWF height:'); ?> <input class="widefat" id="wpflag_widget_height" name="wpflag_widget_height" type="text" value="<?php echo $height; ?>" /></label></p>
			<p><label for="wpflag_widget_media"><?php _e('Flag Image:'); ?> <input class="widefat" id="wpflag_widget_media" name="wpflag_widget_media" type="text" value="<?php echo $media; ?>" /></label></p>
			<p><label for="wpflag_widget_link"><?php _e('Flag Link URL:'); ?> <input class="widefat" id="wpflag_widget_link" name="wpflag_widget_link" type="text" value="<?php echo $link; ?>" /></label></p>
			<p><label for="wpflag_widget_bgcolor"><?php _e('Background Color:'); ?> <input class="widefat" id="wpflag_widget_bgcolor" name="wpflag_widget_bgcolor" type="text" value="<?php echo $bgcolor; ?>" /></label></p>
			<p><label for="wpflag_widget_trans"><input class="checkbox" id="wpflag_widget_trans" name="wpflag_widget_trans" type="checkbox" value="true" <?php if( $trans == "true" ){ echo ' checked="checked"'; } ?> > Background Transparency</label></p>
			<input type="hidden" id="wpflag_widget_submit" name="wpflag_widget_submit" value="1" />
		<?php
	}
	
	register_sidebar_widget( "WP-Flag", wp_flag_widget );
	register_widget_control( "WP-Flag", "wp_flag_widget_control" );
}

// Delay plugin execution until sidebar is loaded
add_action('widgets_init', 'widget_init_wp_flag_widget');

// add the actions
add_action('admin_menu', 'wp_flag_add_pages');
register_activation_hook( __FILE__, 'wp_flag_install' );
register_deactivation_hook( __FILE__, 'wp_flag_uninstall' );
if( function_exists('add_shortcode') ){
	add_shortcode('wp-flag', 'wp_flag_shortcode');
	add_shortcode('WP-FLAG', 'wp_flag_shortcode');
} else {
	add_filter('the_content','wp_flag_init');
}

?>
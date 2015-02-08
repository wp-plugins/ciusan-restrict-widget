<?php 
/*
Plugin Name: Ciusan Restrict Widget
Plugin URI: 
Description: Ciusan Restrict Widget can show widget for register user only or for guest only...
Author: Dannie Herdyawan
Version: 1
Author URI: http://www.ciusan.com/
*/

/*
   _____                                                 ___  ___
  /\  __'\                           __                 /\  \/\  \
  \ \ \/\ \     __      ___     ___ /\_\     __         \ \  \_\  \
   \ \ \ \ \  /'__`\  /' _ `\ /` _ `\/\ \  /'__'\        \ \   __  \
    \ \ \_\ \/\ \L\.\_/\ \/\ \/\ \/\ \ \ \/\  __/    ___  \ \  \ \  \
     \ \____/\ \__/.\_\ \_\ \_\ \_\ \_\ \_\ \____\  /\__\  \ \__\/\__\
      \/___/  \/__/\/_/\/_/\/_/\/_/\/_/\/_/\/____/  \/__/   \/__/\/__/

*/


function ciusan_restrict_widget_userinfo($input, $var = null){
	global $wpdb;
	$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '{$input}' OR user_email = '{$input}' OR ID = '{$input}'");
	if(!empty($user)){

		foreach ($user as $key => $value) {
			$users_data[$key] = $value; 
		}

		$usermeta = $wpdb->get_results("SELECT * FROM $wpdb->usermeta WHERE user_id = '{$user->ID}'");

		foreach ($usermeta as $key => $value) {
			$users_data[$value->meta_key] = $value->meta_value;
		}

		if(!is_null($var)){
			return @$users_data[$var];	
		}else{
			return @$users_data;
		}			
	}else{
		return null;
	}
}

function ciusan_restrict_widget_usermeta($user_id_or_email, $string = '%%display_name%%'){

	$user_info = ciusan_restrict_widget_userinfo($user_id_or_email);

	foreach ($user_info as $key => $user) {
		$search = "%%{$key}%%";
		$string = str_replace($search, $user_info[$key], $string);
	}

	return $string;
}

class ciusan_restrict_widget extends WP_Widget {

	// constructor
	function ciusan_restrict_widget() {
		$widget_ops = array('description' => 'Restrict Widget Ciusan.' );
		parent::WP_Widget(false, __('Restrict Widget Ciusan', 'Ciusan'));   
	}

	// widget form creation
	function form($instance) {
		$title_login		= $instance['title_login'];
		$message_login		= $instance['message_login'];
		$title_visitor		= $instance['title_visitor'];
		$message_visitor	= $instance['message_visitor'];
?>
		<h4 style="margin-bottom:-5px;">Visitors (Users not logged in)</h4>
		<p>
        	<label for="<?php echo $this->get_field_id('title_visitor'); ?>"><?php _e('Title:'); ?>
            	<input class="widefat" id="<?php echo $this->get_field_id('title_visitor'); ?>" name="<?php echo $this->get_field_name('title_visitor'); ?>" type="text" value="<?php echo $title_visitor; ?>" />
            </label>
            <span style="display:block;margin-top:1px;margin-bottom:15px;font-size:smaller !important;">e.g. Welcome Guest!</span>
		</p><p>
        	<label for="<?php echo $this->get_field_id('message_visitor'); ?>"><?php _e('Message:'); ?>
            	<textarea class="widefat" id="<?php echo $this->get_field_id('message_visitor'); ?>" name="<?php echo $this->get_field_name('message_visitor'); ?>"><?php echo $message_visitor; ?></textarea>
			</label>
			<span style="display:block;margin-top:1px;margin-bottom:15px;font-size:smaller !important;">Specify message and shortcodes for users who are <b>not</b> logged in.</span>
		</p>

		<h4 style="margin-top:25px;margin-bottom:-5px;">Users (Logged in users)</h4>
		<p>
			<label for="<?php echo $this->get_field_id('title_login'); ?>"><?php _e('Title:'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title_login'); ?>" name="<?php echo $this->get_field_name('title_login'); ?>" type="text" value="<?php echo $title_login; ?>" />
			</label>
			<span style="display:block;margin-top:1px; margin-bottom:15px;font-size:smaller !important;">e.g. Hello %%display_name%%!</span>
		</p><p>
			<label for="<?php echo $this->get_field_id('message_login'); ?>"><?php _e('Message:'); ?>
				<textarea class="widefat" id="<?php echo $this->get_field_id('message_login'); ?>" name="<?php echo $this->get_field_name('message_login'); ?>"><?php echo $message_login; ?></textarea>
			</label>
			<span style="display:block;margin-top:1px;margin-bottom:15px;font-size:smaller !important;">Specify message and shortcodes for users who are logged in.</span>
		</p>
<?php
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title_login']	= $new_instance['title_login'];
		$instance['message_login']	= $new_instance['message_login'];
		$instance['title_visitor']	= $new_instance['title_visitor'];
		$instance['message_visitor']= $new_instance['message_visitor'];
		return $instance;
	}

	// widget display
	function widget($args, $instance) {
		extract($args);
		global $wpdb, $options, $current_user;
		$display_name = ciusan_restrict_widget_userinfo($current_user->ID, 'display_name');

		$options		= get_option('ciusan_restrict_widget_option');
		$title_login	= str_replace('%%display_name%%', $display_name, strip_tags($instance['title_login']));
		$message_login	= do_shortcode($instance['message_login']);
		$title_visitor	= $instance['title_visitor'];
		$message_visitor= do_shortcode($instance['message_visitor']);

		echo $before_widget;

		if (is_user_logged_in()){
			if ($title_login) {
				echo $before_title . $title_login . $after_title;
				$display_name = ciusan_restrict_widget_userinfo($current_user->ID, 'display_name');
				$title = str_replace('%%display_name%%', $display_name, strip_tags($user_title));
				echo $message_login;
			} else {
				echo $before_title . 'Hi Welcomeback!' . $after_title;
				echo $message_login;
			}
		} else {
			if ($title_visitor) {
				echo $before_title . $title_visitor . $after_title;
				echo $message_visitor;
			} else {
				echo $before_title . 'Hi Guest!' . $after_title;
				echo $message_visitor;
			}

		}

		echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("ciusan_restrict_widget");'));
?>
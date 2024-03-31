<?php
/**
 * WP Admin Announcement
 *
 * @package       WPADMINANN
 * @author        Thiarara
 * @license       gplv2-or-later
 * @version       1.1.01
 *
 * @wordpress-plugin
 * Plugin Name:   WP Admin Announcement
 * Plugin URI:    https://github.com/Thiararapeter/WP-Admin-Announcement
 * Description:   A plugin to display a custom announcement on the WordPress dashboard for all users.
 * Version:       1.1.03
 * Author:        Thiarara
 * Author URI:    https://github.com/thiararapeter
 * Text Domain:   wp-admin-announcement
 * Domain Path:   /languages
 * License:       GPLv2 or later
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Admin Announcement. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HELPER COMMENT START
 * 
 * This file contains the logic required to run the plugin.
 * To add some functionality, you can simply define the WordPres hooks as followed: 
 * 
 * add_action( 'init', 'some_callback_function', 10 );
 * 
 * and call the callback function like this 
 * 
 * function some_callback_function(){}
 * 
 * HELPER COMMENT END
 */

	// Hook into the WordPress admin_menu action to add our admin page
	add_action('admin_menu', 'cdn_add_admin_page');

	if (!function_exists('cdn_add_admin_page')) {
		function cdn_add_admin_page() {
			// Add the main admin page with an icon
			add_menu_page('WP Announcement Guide', 'WP Admin Announcement', 'manage_options', 'wp-announcement', 'cdn_admin_page_content', 'dashicons-megaphone');
			add_submenu_page('wp-announcement', 'WP Comments', 'Comments', 'manage_options', 'wp-announcement-comments', 'cdn_comments_page_content');
			add_submenu_page('wp-announcement', 'WP Settings', 'Settings', 'manage_options', 'wp-announcement-settings', 'cdn_settings_page_content');
		}
	}

	if (!function_exists('cdn_comments_page_content')) {
    function cdn_comments_page_content() {
        // Retrieve and display feedback
        $feedback_data = get_option('cdn_feedback_data', array());
        $current_announcement_title = get_option('cdn_announcement_title', '');

        echo '<div style="width: 50%; margin: 0 auto; border: 1px solid #ccc; padding: 20px; background-color: #f9f9f9; font-family: Arial, sans-serif;">';
        echo '<h1 style="font-size: 24px; color: #333;">Announcement Feedback</h1>';
        echo '<style>';
        echo '.feedback-table { width: 100%; border-collapse: collapse; }';
        echo '.feedback-table th, .feedback-table td { border: 1px solid #ddd; padding: 8px; }';
        echo '.feedback-table th { background-color: #f2f2f2; }';
        echo '.feedback-table tr:nth-child(even) { background-color: #f9f9f9; }';
        echo '.feedback-table tr:hover { background-color: #e6e6e6; }';
        echo '.feedback-table th, .feedback-table td { text-align: left; }';
        echo '.feedback-table th { font-weight: bold; }';
        echo '.feedback-table input[type="checkbox"] { margin-right: 5px; }';
        echo '.feedback-table input[type="submit"] { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 20px; }';
        echo '.feedback-table input[type="submit"]:hover { background-color: #45a049; }';
        echo '</style>';
        echo '<form method="post" action="">';
        echo '<table class="feedback-table">';
        echo '<tr><th>Select</th><th>Announcement Title</th><th>Username</th><th>Type</th><th>Comment</th><th>Submitted</th></tr>';
        if (empty($feedback_data)) {
            echo '<tr><td colspan="6">No comments yet.</td></tr>';
        } else {
            foreach ($feedback_data as $index => $feedback) {
                $announcement_title = isset($feedback['announcement_title']) ? esc_html($feedback['announcement_title']) : $current_announcement_title;
                $username = isset($feedback['username']) ? esc_html($feedback['username']) : 'Anonymous';
                $type = esc_html($feedback['type']);
                $comment = esc_html($feedback['comment']);
                $submitted = isset($feedback['submitted']) ? esc_html($feedback['submitted']) : 'Unknown';
                echo "<tr><td><input type='checkbox' name='cdn_feedback_to_delete[]' value='" . esc_attr($index) . "'></td><td>" . esc_html($announcement_title) . "</td><td>" . esc_html($username) . "</td><td>" . esc_html($type) . "</td><td>" . esc_html($comment) . "</td><td>" . esc_html($submitted) . "</td></tr>";
            }
        }
        echo '</table>';
        echo '<input type="hidden" name="cdn_feedback_action" value="delete_selected_comments">';
        echo '<input type="submit" name="cdn_delete_selected_comments_submit" value="Delete Selected Comments" style="padding: 10px 20px; margin-top: 20px;">';
        echo '</form>';
        echo '<p style="text-align: center; margin-top: 20px;">&copy; ' . esc_html(date('Y')) . ' WP Admin Announcement Plugin created by Creative Designers Ke.</p>';
        echo '</div>';
    }
}

	// hook for Feedback form
	if (!function_exists('cdn_process_feedback')) {
    function cdn_process_feedback() {
        // Check if the feedback form is submitted
        if (isset($_POST['cdn_feedback_action']) && $_POST['cdn_feedback_action'] === 'submit_feedback') {
            // Sanitize feedback type and comment
            $feedback_type = isset($_POST['cdn_feedback_type']) ? sanitize_text_field($_POST['cdn_feedback_type']) : '';
            $feedback_comment = isset($_POST['cdn_feedback_comment']) ? wp_kses_post($_POST['cdn_feedback_comment']) : '';
            $current_user = wp_get_current_user();
            $username = $current_user->display_name;

            // Validate feedback comment
            if (empty($feedback_comment) || strlen($feedback_comment) < 3) {
                // Display error notice if comment is empty or less than 3 characters
                add_action('admin_notices', 'cdn_feedback_error_notice');
                return;
            }

            // Save feedback to options
            $feedback_data = get_option('cdn_feedback_data', array());
            $feedback_data[] = array(
				'announcement_title' => $current_announcement_title,
                'username' => $username,
                'type' => $feedback_type,
                'comment' => $feedback_comment,
                'submitted' => date('Y-m-d H:i:s')
            );
            update_option('cdn_feedback_data', $feedback_data);

            // Display success notice
            add_action('admin_notices', 'cdn_feedback_success_notice');

            // Redirect back to the dashboard widget
            wp_redirect(admin_url('index.php'));
            exit;
        }

        // Check if the action is to delete selected comments
        if (isset($_POST['cdn_feedback_action']) && $_POST['cdn_feedback_action'] === 'delete_selected_comments') {
            $feedback_data = get_option('cdn_feedback_data', array());
            $selected_for_deletion = isset($_POST['cdn_feedback_to_delete']) ? $_POST['cdn_feedback_to_delete'] : array();
            foreach ($selected_for_deletion as $index) {
                unset($feedback_data[$index]);
            }
            update_option('cdn_feedback_data', array_values($feedback_data));

            // Redirect back to the comments page
            wp_redirect(admin_url('admin.php?page=wp-announcement-comments'));
            exit;
        }
    }
}

	// Hook into the WordPress admin_init action to process feedback
	add_action('admin_init', 'cdn_process_feedback');

	// customization of wp admin announcement input page
	if (!function_exists('cdn_admin_page_content')) {
    function cdn_admin_page_content() {
        // Check if the form is submitted
        if (isset($_POST['cdn_announcement_submit'])) {
            // Sanitize and save the announcement title and content
            $announcement_title = isset($_POST['cdn_announcement_title']) ? sanitize_text_field($_POST['cdn_announcement_title']) : '';
            $announcement_content = isset($_POST['cdn_announcement_content']) ? wp_kses_post($_POST['cdn_announcement_content']) : ''; // Use wp_kses_post to allow HTML tags
            update_option('cdn_announcement_title', $announcement_title);
            update_option('cdn_announcement_content', $announcement_content);
            // Update the announcement date
            update_option('cdn_announcement_date', date('Y-m-d H:i:s'));
        }

        // Get the current announcement title and content
        $current_announcement_title = get_option('cdn_announcement_title', '');
        $current_announcement_content = get_option('cdn_announcement_content', '');

        // Display the admin page content
        echo '<div style="width: 50%; margin: 0 auto; border: 1px solid #ccc; padding: 20px; background-color: #f9f9f9; font-family: Arial, sans-serif;">';
        echo '<h1 style="font-size: 24px; color: #333;">Comprehensive Guide to Using the WP Admin Announcement Plugin</h1>';
        echo '<p>This plugin allows you to display a custom announcement on your WordPress dashboard. Here\'s how it works:</p>';
        echo '<ol>';
        echo '<li>Enter your custom announcement title and content in the fields below.</li>';
        echo '<li>Click the "Publish Announcement" button to save your announcement.</li>';
        echo '<li>The announcement will be displayed on your WordPress dashboard.</li>';
        echo '</ol>';
        echo '<form method="post" action="">';
        echo '<p>Enter your announcement title:</p>';
        echo '<input type="text" name="cdn_announcement_title" value="' . esc_attr($current_announcement_title) . '" style="width: 100%;">';
        echo '<p>Enter your announcement content:</p>';
        wp_editor($current_announcement_content, 'cdn_announcement_content', array(
            'textarea_name' => 'cdn_announcement_content',
            'media_buttons' => true, // Disable media buttons
            'teeny' => false, // Use minimal editor
            'textarea_rows' => 10, // Set number of rows
			'fullscreen' => false,
        ));
        echo '<br><input type="submit" name="cdn_announcement_submit" value="Publish Announcement" style="margin-top: 10px;">';
        echo '</form>';
        echo '<p style="text-align: center; margin-top: 20px;">&copy; ' . esc_html(date('Y')) . ' WP Admin Announcement Plugin created by Creative Designers Ke.</p>';
        echo '</div>';
   	 }
	}

	// Hook into the WordPress wp_dashboard_setup action to add our dashboard widget
	add_action('wp_dashboard_setup', 'cdn_add_dashboard_widgets');

	if (!function_exists('cdn_dashboard_widget_content')) {
    function cdn_dashboard_widget_content() {
        // Get the current announcement title and content
        $current_announcement_title = get_option('cdn_announcement_title', '');
        $current_announcement_content = get_option('cdn_announcement_content', '');
        // Get the color setting
        $color_setting = get_option('cdn_color_setting', '#000000');
        // Get the footer color setting
        $footer_color_setting = get_option('cdn_footer_color_setting', '#000000');
        // Get the badge display duration setting
        $badge_duration = get_option('cdn_badge_duration', 7);
        // Get the announcement text type
        $text_type = get_option('cdn_announcement_text_type', 'normal');
        // Get the announcement title font type
        $title_font_type = get_option('cdn_announcement_title_font', 'normal');
        // Get the announcement title color
        $title_color = get_option('cdn_announcement_title_color', '#000000');
        // Get the Google Font type
        $google_font_type = get_option('cdn_google_font_type', 'Roboto');
        
        // Check if the announcement is new
        $announcement_date = get_option('cdn_announcement_date', '');
        $is_new = false;
        if (!empty($announcement_date)) {
            $announcement_date_obj = new DateTime($announcement_date);
            $current_date_obj = new DateTime();
            $interval = $announcement_date_obj->diff($current_date_obj);
            $is_new = $interval->days <= $badge_duration;
        }

        // Get the author's username
        $author_username = get_option('cdn_announcement_author', '');

        // Display the announcement with customization
        echo '<div class="cdn-dashboard-widget" style="border: 1px solid #ccc; padding: 20px; background-color: #f9f9f9; color: ' . esc_attr($color_setting) . '; position: relative; font-family: \'' . esc_attr($google_font_type) . '\', sans-serif;">';
        if (!empty($current_announcement_title) || !empty($current_announcement_content)) {
            $text_style = '';
            switch ($text_type) {
                case 'light':
                    $text_style = 'font-weight: lighter;';
                    break;
                case 'bold':
                    $text_style = 'font-weight: bold;';
                    break;
                case 'italic':
                    $text_style = 'font-style: italic;';
                    break;
            }
            $title_style = '';
            switch ($title_font_type) {
                case 'bold':
                    $title_style = 'font-weight: bold;';
                    break;
                case 'italic':
                    $title_style = 'font-style: italic;';
                    break;
            }
            if (!empty($current_announcement_title)) {
                echo '<h2 class="cdn-announcement-title" style="margin-top: 20px; ' . esc_attr($title_style) . ' color: ' . esc_attr($title_color) . ';">' . esc_html($current_announcement_title) . '</h2>';
            }
            if (!empty($current_announcement_content)) {
                echo '<p class="cdn-announcement-text" style="margin-top: 20px; ' . $text_style . '">' . $current_announcement_content . '</p>';
            }

            if ($is_new) {
                // Customized "New" badge aligned to the top right
                echo '<span class="cdn-new-badge" style="background-color: #008000; color: #fff; padding: 5px 10px; margin:5px 10px; border-radius: 5px; font-size: 14px; font-weight: bold; position: absolute; top: 10px; right: 10px;">New Announcement Available</span>';
            }
            // Display the posted time and author's username with "@" prefix
            if (!empty($announcement_date) && !empty($author_username)) {
                echo '<p class="cdn-footer-text" style="font-size: 12px; color: ' . esc_attr($footer_color_setting) . ';">Posted on: ' . esc_html($announcement_date) . ' by @' . esc_html($author_username) . '</p>';
            } elseif (!empty($announcement_date)) {
                echo '<p class="cdn-footer-text" style="font-size: 12px; color: ' . esc_attr($footer_color_setting) . ';">Posted on: ' . esc_html($announcement_date) . '</p>';
            }
        } else {
            echo '<p>No announcement to display.</p>';
        }
        echo '</div>';
        
        // Add feedback form
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="cdn_feedback_action" value="submit_feedback">';
        echo '<p>Feedback:</p>';
        echo '<select name="cdn_feedback_type">';
        echo '<option value="like">Like</option>';
        echo '<option value="dislike">Dislike</option>';
        echo '</select>';
        echo '<textarea name="cdn_feedback_comment" rows="3" cols="50" placeholder="Your comment..."></textarea>';
        echo '<input type="submit" name="cdn_feedback_submit" value="Submit Feedback">';
        echo '</form>';
        
        // Display the copyright after the feedback form
        echo '<p class="cdn-copyright-text" style="text-align: center; margin-top: 20px; color: ' . esc_attr($footer_color_setting) . ';">&copy; ' . date('Y') . ' WP Admin Announcement Plugin created by Creative Designers Ke.</p>';
       }
    }


		if (!function_exists('cdn_process_feedback')) {
     function cdn_process_feedback() {
        if (isset($_POST['cdn_feedback_action']) && $_POST['cdn_feedback_action'] === 'submit_feedback') {
            $feedback_type = isset($_POST['cdn_feedback_type']) ? sanitize_text_field($_POST['cdn_feedback_type']) : '';
            $feedback_comment = isset($_POST['cdn_feedback_comment']) ? sanitize_text_field($_POST['cdn_feedback_comment']) : '';
            $current_user = wp_get_current_user();
            $username = $current_user->display_name;

            // Check if feedback is empty or less than 3 characters
            if (empty($feedback_comment) || strlen($feedback_comment) < 3) {
                add_action('admin_notices', 'cdn_feedback_error_notice');
                return;
            }

            // Save feedback to options
            $feedback_data = get_option('cdn_feedback_data', array());
            $feedback_data[] = array(
                'username' => $username,
                'type' => $feedback_type,
                'comment' => $feedback_comment,
                'submitted' => date('Y-m-d H:i:s')
            );
            update_option('cdn_feedback_data', $feedback_data);

            // Add success notice
            add_action('admin_notices', 'cdn_feedback_success_notice');

            // Redirect back to the dashboard widget
            wp_redirect(admin_url('index.php'));
            exit;
        }
    }
}

    if (!function_exists('cdn_feedback_error_notice')) {
        function cdn_feedback_error_notice() {
            echo '<div id="message" class="error notice is-dismissible"><p>Feedback must be more than 3 characters.</p></div>';
        }
    }

    if (!function_exists('cdn_feedback_success_notice')) {
        function cdn_feedback_success_notice() {
            echo '<div id="message" class="updated notice is-dismissible"><p>Feedback submitted successfully.</p></div>';
        }
    }

    // Hook into the WordPress admin_init action to add our settings
    add_action('admin_init', 'cdn_settings_init');

    if (!function_exists('cdn_settings_init')) {
        function cdn_settings_init() {
            register_setting('cdn_settings_group', 'cdn_access_level', array('type' => 'array', 'sanitize_callback' => 'cdn_sanitize_access_level'));
            register_setting('cdn_settings_group', 'cdn_color_setting');
            register_setting('cdn_settings_group', 'cdn_badge_duration');
            register_setting('cdn_settings_group', 'cdn_footer_color_setting');
            register_setting('cdn_settings_group', 'cdn_announcement_text_type');
            register_setting('cdn_settings_group', 'cdn_announcement_title_font');
            register_setting('cdn_settings_group', 'cdn_announcement_title_color');
            register_setting('cdn_settings_group', 'cdn_google_font_type'); // New setting for Google Font type

            add_settings_section('cdn_settings_section', 'Plugin Settings', null, 'wp-announcement-settings');

            add_settings_field('cdn_access_level', 'Who can see the Announcement?', 'cdn_access_level_callback', 'wp-announcement-settings', 'cdn_settings_section');
            add_settings_field('cdn_color_setting', 'Annaoucement text color', 'cdn_color_setting_callback', 'wp-announcement-settings', 'cdn_settings_section');
            add_settings_field('cdn_badge_duration', 'Badge Display Duration (Days)', 'cdn_badge_duration_callback', 'wp-announcement-settings', 'cdn_settings_section');
            add_settings_field('cdn_footer_color_setting', 'Footer Color Setting', 'cdn_footer_color_setting_callback', 'wp-announcement-settings', 'cdn_settings_section');
            add_settings_field('cdn_announcement_text_type', 'Announcement Text Type', 'cdn_announcement_text_type_callback', 'wp-announcement-settings', 'cdn_settings_section');
            add_settings_field('cdn_announcement_title_font', 'Announcement Title Font Type', 'cdn_announcement_title_font_callback', 'wp-announcement-settings', 'cdn_settings_section');
            add_settings_field('cdn_announcement_title_color', 'Announcement Title Color', 'cdn_announcement_title_color_callback', 'wp-announcement-settings', 'cdn_settings_section');
            add_settings_field('cdn_google_font_type', 'Google Font Type', 'cdn_google_font_type_callback', 'wp-announcement-settings', 'cdn_settings_section'); 
        }
    }

    if (!function_exists('cdn_google_font_type_callback')) {
        function cdn_google_font_type_callback() {
            $google_font_options = array(
                'Roboto' => 'Roboto',
                'Open Sans' => 'Open Sans',
                'Lato' => 'Lato',
                'Oswald' => 'Oswald',
                'Montserrat' => 'Montserrat',
                'Raleway' => 'Raleway',
                'PT Sans' => 'PT Sans',
                'PT Serif' => 'PT Serif',
                'Source Sans Pro' => 'Source Sans Pro',
                'Source Serif Pro' => 'Source Serif Pro'
            );
            $selected_font = get_option('cdn_google_font_type', 'Roboto');
            echo '<select name="cdn_google_font_type">';
            foreach ($google_font_options as $value => $label) {
                $selected = ($value == $selected_font) ? ' selected' : '';
                echo "<option value='{$value}'{$selected}>{$label}</option>";
            }
            echo '</select>';
        }
    }

    if (!function_exists('cdn_enqueue_google_font')) {
        function cdn_enqueue_google_font() {
            // Get the Google Font type from the settings
            $google_font_type = get_option('cdn_google_font_type', 'Roboto');

            // Enqueue the Google Font
            wp_enqueue_style('cdn-google-font', 'https://fonts.googleapis.com/css?family=' . urlencode($google_font_type) . '&display=swap');
        }
    }

    // Hook the function into the admin_enqueue_scripts action
    add_action('admin_enqueue_scripts', 'cdn_enqueue_google_font');


    if (!function_exists('cdn_announcement_title_font_callback')) {
        function cdn_announcement_title_font_callback() {
            $font_options = array(
                'normal' => 'Normal',
                'bold' => 'Bold',
                'italic' => 'Italic'
            );
            $selected_font = get_option('cdn_announcement_title_font', 'normal');
            echo '<select name="cdn_announcement_title_font">';
            foreach ($font_options as $value => $label) {
                $selected = ($value == $selected_font) ? ' selected' : '';
                echo "<option value='{$value}'{$selected}>{$label}</option>";
            }
            echo '</select>';
        }
    }

    if (!function_exists('cdn_announcement_title_color_callback')) {
    function cdn_announcement_title_color_callback() {
        $color_setting = get_option('cdn_announcement_title_color', '#000000');
        echo "<input type='color' name='cdn_announcement_title_color' value='{$color_setting}'>";
        }
    }

    if (!function_exists('cdn_access_level_callback')) {
        function cdn_access_level_callback() {
        $access_levels = get_option('cdn_access_level', array('admin'));
        $roles = get_editable_roles();
        echo '<div>';
        foreach ($roles as $role => $details) {
            $checked = in_array($role, $access_levels) ? ' checked' : '';
            echo "<input type='checkbox' name='cdn_access_level[]' value='{$role}'{$checked}> {$details['name']}<br>";
        }
        echo '</div>';
        }
    }

    if (!function_exists('cdn_sanitize_access_level')) {
        function cdn_sanitize_access_level($input) {
        $roles = get_editable_roles();
        $allowed_roles = array_keys($roles);
        $sanitized_roles = array();
        foreach ($input as $role) {
            if (in_array($role, $allowed_roles)) {
                $sanitized_roles[] = $role;
            }
        }
        return $sanitized_roles;
        }
    }

    if (!function_exists('cdn_announcement_text_type_callback')) {
        function cdn_announcement_text_type_callback() {
        $text_type_options = array(
            'normal' => 'Normal',
            'light' => 'Light',
            'bold' => 'Bold',
            'italic' => 'Italic'
        );
        $selected_text_type = get_option('cdn_announcement_text_type', 'normal');
        echo '<select name="cdn_announcement_text_type">';
        foreach ($text_type_options as $value => $label) {
            $selected = ($value == $selected_text_type) ? ' selected' : '';
            echo "<option value='{$value}'{$selected}>{$label}</option>";
            }
            echo '</select>';
        }
    }

    if (!function_exists('cdn_badge_duration_callback')) {
        function cdn_badge_duration_callback() {
            $badge_duration = get_option('cdn_badge_duration', 7);
        echo "<input type='number' name='cdn_badge_duration' value='{$badge_duration}' min='1'>";
  
        function cdn_color_setting_callback() {
        $color_setting = get_option('cdn_color_setting', '#000000');
        echo "<input type='color' name='cdn_color_setting' value='{$color_setting}'>";
    }
}
    }

if (!function_exists('cdn_footer_color_setting_callback')) {
    function cdn_footer_color_setting_callback() {
        $footer_color_setting = get_option('cdn_footer_color_setting', '#000000');
        echo "<input type='color' name='cdn_footer_color_setting' value='{$footer_color_setting}'>";
    }
}

	if (!function_exists('cdn_add_dashboard_widgets')) {
    function cdn_add_dashboard_widgets() {
        $access_levels = get_option('cdn_access_level', array('admin'));
        $current_user = wp_get_current_user();
        $user_role = (array) $current_user->roles;
        $has_access = array_intersect($user_role, $access_levels);

        if (!empty($has_access)) {
            wp_add_dashboard_widget('cdn_dashboard_widget', 'WP Admin Announcement', 'cdn_dashboard_widget_content');
        }
    	}
	}

	if (!function_exists('cdn_process_feedback')) {
        function cdn_process_feedback() {
            // Check if the feedback form is submitted
            if (isset($_POST['cdn_feedback_action']) && $_POST['cdn_feedback_action'] === 'submit_feedback') {
                // Sanitize feedback type and comment
                $feedback_type = isset($_POST['cdn_feedback_type']) ? sanitize_text_field($_POST['cdn_feedback_type']) : '';
                $feedback_comment = isset($_POST['cdn_feedback_comment']) ? wp_kses_post($_POST['cdn_feedback_comment']) : '';
                $current_user = wp_get_current_user();
                $username = $current_user->display_name;
    
                // Validate feedback comment
                if (empty($feedback_comment) || strlen($feedback_comment) < 3) {
                    // Display error notice if comment is empty or less than 3 characters
                    add_action('admin_notices', 'cdn_feedback_error_notice');
                    return;
                }
    
                // Save feedback to options
                $feedback_data = get_option('cdn_feedback_data', array());
                $feedback_data[] = array(
                    'announcement_title' => get_option('cdn_announcement_title', ''),
                    'username' => $username,
                    'type' => $feedback_type,
                    'comment' => $feedback_comment,
                    'submitted' => date('Y-m-d H:i:s')
                );
                update_option('cdn_feedback_data', $feedback_data);
    
                // Display success notice
                add_action('admin_notices', 'cdn_feedback_success_notice');
    
                // Redirect back to the dashboard widget
                wp_redirect(admin_url('index.php'));
                exit;
            }
    
            // Check if the action is to delete selected comments
            if (isset($_POST['cdn_feedback_action']) && $_POST['cdn_feedback_action'] === 'delete_selected_comments') {
                $feedback_data = get_option('cdn_feedback_data', array());
                $selected_for_deletion = isset($_POST['cdn_feedback_to_delete']) ? $_POST['cdn_feedback_to_delete'] : array();
                foreach ($selected_for_deletion as $index) {
                    unset($feedback_data[$index]);
                }
                update_option('cdn_feedback_data', array_values($feedback_data));
    
                // Redirect back to the comments page
                wp_redirect(admin_url('admin.php?page=wp-announcement-comments'));
                exit;
            }
        }
    }
  
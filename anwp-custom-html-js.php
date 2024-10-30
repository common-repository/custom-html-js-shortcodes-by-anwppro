<?php
/**
 * Plugin Name: Custom HTML & JS Shortcodes by AnWP.pro
 * Description: Easily create custom HTML and Javascript shortcodes. Syntax highlighting and revisions support.
 * Version:     0.2.1
 * Author:      Andrei Strekozov <anwp.pro>
 * Author URI:  https://anwp.pro
 * License:     GPLv2+
 * Requires PHP: 5.4
 * Text Domain: anwp-custom-html-js
 * Domain Path: /languages
 *
 * @link    https://anwp.pro
 *
 * @package AnWP_Custom_HTML_JS
 * @version 0.2.1
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

/**
 * Copyright (c) 2017-2018 Andrei Strekozov <anwp.pro> (email : anwp.pro@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Check for required PHP and WP versions
if ( version_compare( PHP_VERSION, '5.4', '<' ) || ! version_compare( get_bloginfo( 'version' ), '4.9', '>=' ) ) {

	add_action( 'admin_notices', 'anwpcs_requirements_not_met_notice' );

} else {

	// Require the main plugin class
	require_once plugin_dir_path( __FILE__ ) . 'class-anwp-custom-html-js.php';

	// Kick it off.
	add_action( 'plugins_loaded', array( anwp_custom_html_js(), 'hooks' ) );

	// Activation and deactivation.
	register_activation_hook( __FILE__, array( anwp_custom_html_js(), 'activate' ) );
	register_deactivation_hook( __FILE__, array( anwp_custom_html_js(), 'deactivate' ) );
}

/**
 * Adds a notice to the dashboard if the plugin requirements are not met.
 *
 * @since  0.1.0
 * @return void
 */
function anwpcs_requirements_not_met_notice() {

	// Compile default message.
	$default_message = esc_html__( 'Custom HTML and JS Shortcodes is missing requirements and currently NOT ACTIVE. Please make sure all requirements are available.', 'anwp-custom-html-js' );

	// Default details.
	$details = '';

	if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
		/* translators: %s minimum PHP version */
		$details .= '<small>' . sprintf( esc_html__( 'Custom HTML and JS Shortcodes cannot run on PHP versions older than %s. Please contact your hosting provider to update your site.', 'anwp-custom-html-js' ), '5.4.0' ) . '</small><br />';
	}

	if ( ! version_compare( get_bloginfo( 'version' ), '4.9', '>=' ) ) {
		/* translators: %s minimum WP version */
		$details .= '<small>' . sprintf( esc_html__( 'Custom HTML and JS Shortcodes requires WordPress version %s+.', 'anwp-custom-html-js' ), '4.9' ) . '</small><br />';
	}

	// Output errors.
	?>
	<div id="message" class="error">
		<p><?php echo wp_kses_post( $default_message ); ?></p>
		<?php echo wp_kses_post( $details ); ?>
	</div>
	<?php
}

/**
 * Grab the AnWP_Custom_HTML_JS object and return it.
 * Wrapper for AnWP_Custom_HTML_JS::get_instance().
 *
 * @since  0.1.0
 * @return AnWP_Custom_HTML_JS Singleton instance of plugin class.
 */
function anwp_custom_html_js() {
	return AnWP_Custom_HTML_JS::get_instance();
}

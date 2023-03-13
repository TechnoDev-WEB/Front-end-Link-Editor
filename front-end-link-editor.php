<?php
/*
 * Plugin Name:       Front-end Link Editor
 * Plugin URI:        https://github.com/TechnoDev-WEB/Front-end-Link-Editor
 * Description:       Transform your WordPress post links in real-time with our user-friendly plugin.
 * 					  Edit links and link text directly from the frontend while logged in, without leaving the page.
 * Version:           1.0.0
 * Requires at least: 4.9
 * Requires PHP:      7.0
 * Author:            TechnoDev
 * Author URI:        https://technodev.ro
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Update URI:        https://github.com/TechnoDev-WEB/Front-end-Link-Editor
 * 
 * Icons made by Font Awesome (https://fontawesome.com)
 * 
 * This plugin uses the Poppins font, which is licensed under the SIL Open Font License. The font was downloaded from https://fonts.google.com and is being hosted on our server.

 * The original author of the font is Indian Type Foundry. For more information about the Poppins font and the SIL Open Font License, see https://scripts.sil.org/OFL.

 *
 * Front-end Link Editor
 *
 * Copyright (C) 2023, by TechnoDev
 *
 * Front-end Link Editor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or any later version.
 *
 * Front-end Link Editor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// Check the PHP version


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('FLE_URL', plugin_dir_url(__FILE__));
define('FLE_PATH', plugin_dir_path(__FILE__));
define( 'FLE_PHP_VERSION', '7.0' );

if ( version_compare( PHP_VERSION, FLE_PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'front_end_link_editor_fail_php_version' );
	deactivate_plugins( plugin_basename( __FILE__ ) );
} elseif ( ! version_compare( get_bloginfo( 'version' ), '4.9', '>=' ) ) {
	add_action( 'admin_notices', 'front_end_link_editor_fail_wp_version' );
	deactivate_plugins( plugin_basename( __FILE__ ) );
} else {
	require FLE_PATH . 'includes/fle-main.php';
}

if ( is_plugin_active( 'elementor/elementor.php' ) ) {
	add_action( 'admin_notices', 'front_end_link_editor_elementor_plugin' );
}


/**
 * Front-end Link Editor admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 
 * @return void
 */

function front_end_link_editor_fail_php_version() {
	$message = sprintf(
		__( 'Front-end Link Editor requires at least PHP version %1$s. You are running version %2$s. Please upgrade and try again.', 'front-end-link-editor' ),
		FLE_PHP_VERSION,
		PHP_VERSION
	);

	$full_message = sprintf( '<div class="error"><p>%s</p></div>', wpautop( $message ) );
	echo wp_kses_post( $full_message );
}
 
/**
 * Front-end Link Editor admin notice for minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @return void
 */
function front_end_link_editor_fail_wp_version() {

	$wp_version = get_bloginfo( 'version' );
	$fle_required_wp_version = '4.9';

	$message = sprintf(
		__( 'Front-end Link Editor plugin requires a minimum version of WordPress %1$s. Your current version of WordPress is %2$s. Please upgrade your WordPress installation to use this plugin.', 'front-end-link-editor' ),
		$fle_required_wp_version,
		$wp_version
	 );
 
	 $full_message = sprintf( '<div class="error"><p>%s</p></div>', wpautop( $message ) );
	 echo wp_kses_post( $full_message );
}


/**
 * Front-end Link Editor admin elementor plugin active.
 *
 * Warning when the site contain elementor plugin active.
 
 * @return void
 */

 function front_end_link_editor_elementor_plugin() {
	$message = sprintf(
	   __( 'Front-end Link Editor plugin is not compatible with Elementor.', 'front-end-link-editor' )
	);

	$full_message = sprintf( '<div class="error"><p>%s</p></div>', wpautop( $message ) );
	echo wp_kses_post( $full_message );
 }
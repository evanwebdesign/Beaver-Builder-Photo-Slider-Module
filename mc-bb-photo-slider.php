<?php
/**
 * Plugin Name: Minimal Chaos - Beaver Builder Photo Slider (v2.4.0)
 * Description: Beaver Builder photo slider module with captions, counter, swipe, dashicons controls, crop options, and performance pausing.
 * Version:     2.4.0
 * Author:      Minimal Chaos
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MC_BB_PHOTO_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'MC_BB_PHOTO_SLIDER_URL',  plugin_dir_url( __FILE__ ) );

add_action( 'init', function () {

	if ( ! class_exists( 'FLBuilder' ) ) {
		return;
	}

	require_once MC_BB_PHOTO_SLIDER_PATH . 'modules/mc-photo-slider/mc-photo-slider.php';
} );

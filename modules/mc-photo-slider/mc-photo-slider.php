<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MC_Photo_Slider_Module extends FLBuilderModule {

	public function __construct() {

		parent::__construct( array(
			'name'            => __( 'Photo Slider', 'mc' ),
			'description'     => __( 'Photo slider with autoplay + prev/next/play-pause controls.', 'mc' ),
			'category'        => __( 'Minimal Chaos', 'mc' ),
			'dir'             => MC_BB_PHOTO_SLIDER_PATH . 'modules/mc-photo-slider/',
			'url'             => MC_BB_PHOTO_SLIDER_URL  . 'modules/mc-photo-slider/',
			'editor_export'   => true,
			'enabled'         => true,
			'partial_refresh' => true,
			'icon'            => 'format-gallery.svg', // v2.4.0: custom module icon in BB panel
		) );

		$this->add_css( 'dashicons' );
		$this->add_css( 'mc-photo-slider', $this->url . 'css/frontend.css', array(), '2.4.0' );
		$this->add_js(  'mc-photo-slider', $this->url . 'js/frontend.js',  array(), '2.4.0', true );
	}
}

FLBuilder::register_module( 'MC_Photo_Slider_Module', array(
	'general' => array(
		'title'    => __( 'General', 'mc' ),
		'sections' => array(
			'content' => array(
				'title'  => __( 'Photos', 'mc' ),
				'fields' => array(
					'photos' => array(
						'type'        => 'multiple-photos',
						'label'       => __( 'Gallery Photos', 'mc' ),
						'help'        => __( 'Add one or more photos.', 'mc' ),
						'connections' => array( 'photo' ),
					),
					'crop' => array(
						'type'    => 'select',
						'label'   => __( 'Photo Crop', 'mc' ),
						'default' => 'landscape',
						'options' => array(
							'landscape' => __( 'Landscape (4x3)', 'mc' ),
							'vertical'  => __( 'Vertical (3x4)', 'mc' ),
							'square'    => __( 'Square (1x1)', 'mc' ),
						),
						'help' => __( 'Controls the aspect ratio of the slider viewport. Images will be cropped to fill.', 'mc' ),
					),
					'show_captions' => array(
						'type'    => 'select',
						'label'   => __( 'Show Captions', 'mc' ),
						'default' => 'yes',
						'options' => array(
							'yes' => __( 'Yes', 'mc' ),
							'no'  => __( 'No', 'mc' ),
						),
						'help' => __( 'Uses the Media Library caption field for each image (if present).', 'mc' ),
					),
					'show_thumbnails' => array(
						'type'    => 'select',
						'label'   => __( 'Show Thumbnails', 'mc' ),
						'default' => 'no',
						'options' => array(
							'no'  => __( 'No', 'mc' ),
							'yes' => __( 'Yes', 'mc' ),
						),
						'help' => __( 'Displays a horizontal strip of clickable thumbnails below the slider.', 'mc' ),
					),
					'thumbnail_size' => array(
						'type'    => 'select',
						'label'   => __( 'Thumbnail Size', 'mc' ),
						'default' => 'md',
						'options' => array(
							'sm' => __( 'Small', 'mc' ),
							'md' => __( 'Medium', 'mc' ),
							'lg' => __( 'Large', 'mc' ),
						),
						'help' => __( 'Controls the square size of thumbnails.', 'mc' ),
					),
				),
			),
			'behavior' => array(
				'title'  => __( 'Behavior', 'mc' ),
				'fields' => array(
					'autoplay' => array(
						'type'    => 'select',
						'label'   => __( 'Autoplay', 'mc' ),
						'default' => 'yes',
						'options' => array(
							'yes' => __( 'Yes', 'mc' ),
							'no'  => __( 'No', 'mc' ),
						),
					),
					'interval' => array(
						'type'        => 'unit',
						'label'       => __( 'Autoplay Interval', 'mc' ),
						'default'     => '5',
						'units'       => array( 's' ),
						'description' => __( 'seconds', 'mc' ),
						'help'        => __( 'Time between slides when autoplay is enabled.', 'mc' ),
					),
					'pause_on_hover' => array(
						'type'    => 'select',
						'label'   => __( 'Pause on Hover', 'mc' ),
						'default' => 'yes',
						'options' => array(
							'yes' => __( 'Yes', 'mc' ),
							'no'  => __( 'No', 'mc' ),
						),
					),
				),
			),
		),
	),
) );

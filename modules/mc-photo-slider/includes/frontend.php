<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$node_id   = ! empty( $module->node ) ? $module->node : uniqid( 'mcps_', true );
$slider_id = 'mc-photo-slider-' . esc_attr( $node_id );

$photos_setting = isset( $settings->photos ) ? $settings->photos : array();

$autoplay = ( isset( $settings->autoplay ) && $settings->autoplay === 'no' ) ? 'false' : 'true';

$interval_seconds = 5;
if ( isset( $settings->interval ) && $settings->interval !== '' ) {
	$interval_seconds = floatval( $settings->interval );
	if ( $interval_seconds <= 0 ) $interval_seconds = 5;
}
$interval_ms = (int) round( $interval_seconds * 1000 );

$pause_on_hover = ( isset( $settings->pause_on_hover ) && $settings->pause_on_hover === 'no' ) ? 'false' : 'true';

$crop = isset( $settings->crop ) ? $settings->crop : 'landscape';
$crop_class = 'is-landscape';
if ( $crop === 'vertical' ) $crop_class = 'is-vertical';
if ( $crop === 'square' ) $crop_class = 'is-square';

$show_captions = ( isset( $settings->show_captions ) && $settings->show_captions === 'no' ) ? false : true;

// v2.2.0: Optional thumbnail rail
$show_thumbnails = ( isset( $settings->show_thumbnails ) && $settings->show_thumbnails === 'yes' );
$thumb_size = isset( $settings->thumbnail_size ) ? $settings->thumbnail_size : 'md';
$thumb_class = 'is-thumb-md';
if ( $thumb_size === 'sm' ) $thumb_class = 'is-thumb-sm';
if ( $thumb_size === 'lg' ) $thumb_class = 'is-thumb-lg';

$photo_ids = array();
if ( is_array( $photos_setting ) ) {
	foreach ( $photos_setting as $p ) {
		if ( is_numeric( $p ) ) {
			$photo_ids[] = (int) $p;
		} elseif ( is_object( $p ) && isset( $p->id ) ) {
			$photo_ids[] = (int) $p->id;
		} elseif ( is_array( $p ) && isset( $p['id'] ) ) {
			$photo_ids[] = (int) $p['id'];
		}
	}
}
$photo_ids = array_values( array_filter( array_unique( $photo_ids ) ) );

if ( empty( $photo_ids ) ) {
	if ( defined( 'FL_BUILDER_EDITING' ) && FL_BUILDER_EDITING ) : ?>
		<div class="mc-photo-slider__notice">
			<strong>Photo Slider</strong><br>
			Add photos in the module settings (General → Photos → Gallery Photos).
		</div>
	<?php endif;
	return;
}

$total = count( $photo_ids );
?>
<div
	id="<?php echo $slider_id; ?>"
	class="mc-photo-slider <?php echo esc_attr( $crop_class ); ?>"
	data-mc-photo-slider
	data-interval="<?php echo esc_attr( $interval_ms ); ?>"
	data-autoplay="<?php echo esc_attr( $autoplay ); ?>"
	data-pause-on-hover="<?php echo esc_attr( $pause_on_hover ); ?>"
	data-has-thumbs="<?php echo $show_thumbnails ? 'true' : 'false'; ?>"
	tabindex="0"
	aria-roledescription="carousel"
	aria-label="Photo slider"
>
	<div class="mc-photo-slider__viewport">
		<?php foreach ( $photo_ids as $index => $attachment_id ) :
			$url = wp_get_attachment_image_url( $attachment_id, 'full' );
			if ( empty( $url ) ) continue;

			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$caption = '';
			if ( $show_captions ) {
				$caption = wp_get_attachment_caption( $attachment_id );
			}

			$is_active = ( $index === 0 );
			?>
			<div class="mc-photo-slider__slide<?php echo $is_active ? ' is-active' : ''; ?>" data-slide data-index="<?php echo (int) $index; ?>" id="<?php echo esc_attr( $slider_id . '-slide-' . (int) $index ); ?>">
				<?php
				// v2.4.0: Only the first full-size slide image loads on initial page load.
				// Other full-size slide images are deferred and only loaded on demand
				// when that slide becomes active. Thumbnails still load normally.
				$placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
				?>
				<img
					class="mc-photo-slider__img"
					src="<?php echo $is_active ? esc_url( $url ) : esc_attr( $placeholder ); ?>"
					data-full-src="<?php echo esc_url( $url ); ?>"
					data-loaded="<?php echo $is_active ? 'true' : 'false'; ?>"
					alt="<?php echo esc_attr( $alt ); ?>"
					loading="<?php echo $is_active ? 'eager' : 'lazy'; ?>"
					decoding="async"
				/>
				<?php if ( ! empty( $caption ) ) : ?>
					<div class="mc-photo-slider__caption" data-caption><?php echo esc_html( $caption ); ?></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="mc-photo-slider__bar" aria-label="Slider controls">
		<div class="mc-photo-slider__count" aria-live="polite">
			<span data-counter>1/<?php echo esc_html( $total ); ?></span>
		</div>

		<div class="mc-photo-slider__controls">
			<button type="button" class="mc-photo-slider__btn" data-prev aria-label="Previous slide">
				<span class="dashicons dashicons-arrow-left-alt" aria-hidden="true"></span>
			</button>

			<button
				type="button"
				class="mc-photo-slider__btn"
				data-toggle
				aria-label="Pause"
				aria-pressed="false"
			>
				<span class="dashicons dashicons-controls-pause mc-photo-slider__icon" data-icon-pause aria-hidden="true"></span>
				<span class="dashicons dashicons-controls-play mc-photo-slider__icon" data-icon-play aria-hidden="true"></span>
			</button>

			<button type="button" class="mc-photo-slider__btn" data-next aria-label="Next slide">
				<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
			</button>
		</div>
	</div>

<?php if ( $show_thumbnails ) : ?>
	<div class="mc-photo-slider__thumbs <?php echo esc_attr( $thumb_class ); ?>" aria-label="Slide thumbnails">
		<div class="mc-photo-slider__thumbs-track" data-thumbs>
			<?php foreach ( $photo_ids as $t_index => $t_id ) :
				$thumb_url = wp_get_attachment_image_url( $t_id, 'thumbnail' );
				if ( empty( $thumb_url ) ) continue;
				$thumb_alt = get_post_meta( $t_id, '_wp_attachment_image_alt', true );
				$btn_label = 'Go to slide ' . ( (int) $t_index + 1 ) . ' of ' . (int) $total;
				?>
				<button
					type="button"
					class="mc-photo-slider__thumb"
					data-thumb
					data-thumb-index="<?php echo (int) $t_index; ?>"
					aria-label="<?php echo esc_attr( $btn_label ); ?>"
					aria-controls="<?php echo esc_attr( $slider_id . '-slide-' . (int) $t_index ); ?>"
					<?php echo ( $t_index === 0 ) ? 'aria-current="true"' : ''; ?>
				>
					<img class="mc-photo-slider__thumb-img" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" loading="lazy" decoding="async" />
				</button>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>

</div>

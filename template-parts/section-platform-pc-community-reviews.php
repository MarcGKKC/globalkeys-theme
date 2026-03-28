<?php
/**
 * PC-Kollektion: Community Reviews Showcase (Mockup mit Fake-Bewertung).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_review_products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$gk_review_products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 5,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
		)
	);
}

if ( empty( $gk_review_products ) || ! is_array( $gk_review_products ) ) {
	$gk_review_products = array();
}

$gk_fake_gamertags = array(
	'ShadowByte77',
	'NeonWarden',
	'PixelRogue',
	'IronNovaX',
	'LumaRift',
);
$gk_fake_reviews = array(
	__( '"One of those games that is hard to stop playing. It is a lot of fun and every run feels different. Cannot wait for full release!"', 'globalkeys' ),
	__( '"Great pacing and atmosphere. After a few hours I was completely hooked and already planning another run."', 'globalkeys' ),
	__( '"Clean gameplay loop, super responsive controls and really fair difficulty curve. Big recommendation."', 'globalkeys' ),
	__( '"I expected a quick test, but ended up playing all evening. The progression feels rewarding."', 'globalkeys' ),
	__( '"Exactly my kind of game: polished, fun, and worth every minute. Looking forward to future updates."', 'globalkeys' ),
);
$gk_member_since_dates = array(
	'2021-09-14',
	'2020-02-06',
	'2019-11-23',
	'2022-05-18',
	'2018-08-01',
);
/** Placeholder bis echte Community-Bewertungen angebunden sind */
$gk_fake_rating_scores = array( 10, 9, 10, 8, 10 );
$gk_fake_rating_counts = array( 4, 12, 7, 24, 9 );
/** Kompakte Steam-Zeile pro Slide (Urteil + Anzahl, gleiche Spalte wie Community-Bewertung) */
$gk_steam_rows_by_slide = array(
	array(
		array(
			'verdict' => __( 'Äußerst positiv', 'globalkeys' ),
			'count'   => 5305,
		),
	),
	array(
		array(
			'verdict' => __( 'Sehr positiv', 'globalkeys' ),
			'count'   => 842,
		),
	),
	array(
		array(
			'verdict' => __( 'Äußerst positiv', 'globalkeys' ),
			'count'   => 1204,
		),
	),
	array(
		array(
			'verdict' => __( 'Positiv', 'globalkeys' ),
			'count'   => 312,
		),
	),
	array(
		array(
			'verdict' => __( 'Äußerst positiv', 'globalkeys' ),
			'count'   => 2108,
		),
	),
);
$gk_fallback_review_image = get_template_directory_uri() . '/Pictures/2892.jpg';
$gk_slides = array();
for ( $i = 0; $i < 5; $i++ ) {
	$p = isset( $gk_review_products[ $i ] ) && $gk_review_products[ $i ] instanceof WC_Product ? $gk_review_products[ $i ] : null;
	$title     = $p ? $p->get_name() : __( 'Slay the Spire II', 'globalkeys' );
	$permalink = $p ? $p->get_permalink() : '#';
	$image     = $gk_fallback_review_image;
	$tags      = array();

	if ( $p ) {
		$img_id = (int) $p->get_image_id();
		if ( $img_id ) {
			$image = wp_get_attachment_image_url( $img_id, 'full' );
		}
		if ( ! $image && function_exists( 'globalkeys_get_product_hero_image_url' ) ) {
			$image = globalkeys_get_product_hero_image_url( $p, 'full' );
		}

		$terms = get_the_terms( (int) $p->get_id(), 'product_tag' );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			$terms = get_the_terms( (int) $p->get_id(), 'product_cat' );
		}
		$terms = is_array( $terms ) ? array_values( array_filter( $terms ) ) : array();
		$tags  = array_slice( $terms, 0, 5 );
	}

	if ( $i === 0 ) {
		$image = get_theme_file_uri( 'Pictures/Image 4 (1).png' );
	}
	/* Forza: festes Key-Art aus dem Theme */
	if ( $p && stripos( $p->get_name(), 'forza' ) !== false ) {
		$image = get_theme_file_uri( 'Pictures/Image 6.png' );
	}
	/* Life is Strange: festes Key-Art */
	if ( $p && stripos( $p->get_name(), 'life is strange' ) !== false ) {
		$image = get_theme_file_uri( 'Pictures/Image 7.png' );
	}
	/* Death Stranding: festes Key-Art */
	if ( $p && stripos( $p->get_name(), 'death stranding' ) !== false ) {
		$image = get_theme_file_uri( 'Pictures/Image 5.png' );
	}
	/* Crimson (z. B. Crimson Desert): festes Key-Art */
	if ( $p && stripos( $p->get_name(), 'crimson' ) !== false ) {
		$image = get_theme_file_uri( 'Pictures/Image 8.png' );
	}
	if ( ! $image ) {
		$image = $gk_fallback_review_image;
	}

	$gk_slides[] = array(
		'title'          => $title,
		'permalink'      => $permalink,
		'image'          => $image,
		'tags'           => $tags,
		'gamertag'       => $gk_fake_gamertags[ $i ],
		'review'         => $gk_fake_reviews[ $i ],
		'member_since'   => date_i18n( 'M d, Y', strtotime( $gk_member_since_dates[ $i ] ) ),
		'rating_score'   => isset( $gk_fake_rating_scores[ $i ] ) ? (int) $gk_fake_rating_scores[ $i ] : 10,
		'rating_count'   => isset( $gk_fake_rating_counts[ $i ] ) ? (int) $gk_fake_rating_counts[ $i ] : 4,
		'steam_rows'     => isset( $gk_steam_rows_by_slide[ $i ] ) && is_array( $gk_steam_rows_by_slide[ $i ] ) ? $gk_steam_rows_by_slide[ $i ] : array(),
	);
}

$gk_fake_user_avatar = get_theme_file_uri( 'Pictures/andon.png' );
$gk_pc_comm_section    = get_query_var( 'gk_section', array() );
$gk_pc_comm_section_id = is_array( $gk_pc_comm_section ) && ! empty( $gk_pc_comm_section['id'] ) ? (string) $gk_pc_comm_section['id'] : '';
$gk_pc_comm_title_id   = $gk_pc_comm_section_id !== '' ? $gk_pc_comm_section_id . '-title' : 'gk-pc-community-title';
?>

<section<?php echo $gk_pc_comm_section_id !== '' ? ' id="' . esc_attr( $gk_pc_comm_section_id ) . '"' : ''; ?> class="gk-section gk-section-platform-pc-community" role="region" aria-labelledby="<?php echo esc_attr( $gk_pc_comm_title_id ); ?>">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $gk_pc_comm_title_id ); ?>" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Die Community empfiehlt', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
			</h2>
		</div>
		<p class="gk-pc-community__subtitle"><?php esc_html_e( 'Spielempfehlungen des Tages von der Community', 'globalkeys' ); ?></p>

		<div class="gk-pc-community__carousel-wrap" tabindex="0">
			<button type="button" class="gk-test-arrow gk-test-arrow--prev gk-pc-community__arrow gk-pc-community__arrow--prev" aria-label="<?php esc_attr_e( 'Vorherige', 'globalkeys' ); ?>">
				<span class="gk-test-arrow-circle"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></span>
			</button>
			<div class="gk-pc-community__frame">
				<div class="gk-pc-community__slides">
				<?php foreach ( $gk_slides as $i => $gk_slide ) : ?>
					<div class="gk-pc-community__content<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide-index="<?php echo esc_attr( (string) $i ); ?>" aria-hidden="<?php echo 0 === $i ? 'false' : 'true'; ?>">
						<a class="gk-pc-community__media" href="<?php echo esc_url( $gk_slide['permalink'] ); ?>" style="--gk-review-image: url('<?php echo esc_url( $gk_slide['image'] ); ?>');">
							<span class="screen-reader-text"><?php echo esc_html( $gk_slide['title'] ); ?></span>
						</a>

						<div class="gk-pc-community__review">
							<?php
							$gk_rs = isset( $gk_slide['rating_score'] ) ? max( 0, min( 10, (int) $gk_slide['rating_score'] ) ) : 10;
							$gk_rc = isset( $gk_slide['rating_count'] ) ? max( 0, (int) $gk_slide['rating_count'] ) : 0;
							$gk_rating_aria = sprintf(
								/* translators: 1: score 0–10, 2: review count */
								__( 'Community rating %1$d out of 10, based on %2$d reviews', 'globalkeys' ),
								$gk_rs,
								$gk_rc
							);
							$gk_ring_r  = 17;
							$gk_circ    = 2 * M_PI * $gk_ring_r;
							$gk_arc_len = ( max( 0, min( 10, $gk_rs ) ) / 10 ) * $gk_circ;
							if ( $gk_rs >= 10 ) {
								$gk_arc_len = $gk_circ;
							}
							$gk_dash_arc = number_format( $gk_arc_len, 4, '.', '' );
							$gk_dash_gap = number_format( $gk_circ, 4, '.', '' );
							?>
							<blockquote class="gk-product-hover-panel__excerpt gk-pc-community__quote">
								<?php echo esc_html( $gk_slide['review'] ); ?>
							</blockquote>
							<div class="gk-pc-community__rating-steam-row">
								<div class="gk-pc-community__rating" role="group" aria-label="<?php echo esc_attr( $gk_rating_aria ); ?>">
									<div class="gk-pc-community__rating-ring" aria-hidden="true">
										<svg class="gk-pc-community__rating-ring-svg" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<circle class="gk-pc-community__rating-track" cx="22" cy="22" r="<?php echo esc_attr( (string) $gk_ring_r ); ?>" />
											<circle class="gk-pc-community__rating-arc" cx="22" cy="22" r="<?php echo esc_attr( (string) $gk_ring_r ); ?>" transform="rotate(-90 22 22)" stroke-dasharray="<?php echo esc_attr( $gk_dash_arc . ' ' . $gk_dash_gap ); ?>" stroke-linecap="round" />
										</svg>
										<span class="gk-pc-community__rating-score"><?php echo esc_html( (string) $gk_rs ); ?></span>
									</div>
									<div class="gk-pc-community__rating-text" aria-hidden="true">
										<span class="gk-pc-community__rating-based"><?php esc_html_e( 'Based on:', 'globalkeys' ); ?></span>
										<span class="gk-pc-community__rating-count">
											<?php
											echo esc_html(
												sprintf(
													/* translators: %d: number of reviews */
													_n( '%d review', '%d reviews', $gk_rc, 'globalkeys' ),
													$gk_rc
												)
											);
											?>
										</span>
									</div>
								</div>
								<?php if ( ! empty( $gk_slide['steam_rows'] ) ) : ?>
									<span class="gk-pc-community__rating-steam-sep" aria-hidden="true"></span>
									<div class="gk-pc-community__steam-inline">
										<p class="gk-pc-community__steam-inline-heading"><?php esc_html_e( 'Neueste Steambewertungen:', 'globalkeys' ); ?></p>
										<div class="gk-pc-community__steam-inline-rows">
											<?php foreach ( $gk_slide['steam_rows'] as $gk_steam_row ) : ?>
												<?php
												$gk_s_count = isset( $gk_steam_row['count'] ) ? max( 0, (int) $gk_steam_row['count'] ) : 0;
												$gk_s_count_fmt = number_format_i18n( $gk_s_count );
												$gk_s_verdict = isset( $gk_steam_row['verdict'] ) ? (string) $gk_steam_row['verdict'] : '';
												?>
												<div class="gk-pc-community__steam-inline-row">
													<span class="gk-pc-community__steam-inline-value">
														<?php echo esc_html( $gk_s_verdict ); ?>
														<span class="gk-pc-community__steam-inline-count">(<?php echo esc_html( $gk_s_count_fmt ); ?>)</span>
													</span>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $gk_slide['tags'] ) ) : ?>
								<div class="gk-pc-community__tags gk-product-hover-panel__tags">
									<p class="gk-product-hover-panel__tags-heading"><?php esc_html_e( 'Tags:', 'globalkeys' ); ?></p>
									<ul class="gk-product-hover-panel__tag-list">
										<?php foreach ( $gk_slide['tags'] as $gk_review_term ) : ?>
											<li><span class="gk-product-hover-panel__tag"><?php echo esc_html( $gk_review_term->name ); ?></span></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
							<div class="gk-pc-community__author">
								<img src="<?php echo esc_url( $gk_fake_user_avatar ); ?>" alt="" />
								<div>
									<strong><?php echo esc_html( $gk_slide['gamertag'] ); ?></strong>
									<span><?php echo esc_html( sprintf( __( 'Mitglied seit: %s', 'globalkeys' ), $gk_slide['member_since'] ) ); ?></span>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
			<button type="button" class="gk-test-arrow gk-test-arrow--next gk-pc-community__arrow gk-pc-community__arrow--next" aria-label="<?php esc_attr_e( 'Nächste', 'globalkeys' ); ?>">
				<span class="gk-test-arrow-circle"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
			</button>
		</div>

		<div class="gk-pc-community__dots" aria-label="<?php esc_attr_e( 'Review-Navigation', 'globalkeys' ); ?>">
			<?php foreach ( $gk_slides as $i => $gk_slide ) : ?>
				<button type="button" class="<?php echo 0 === $i ? 'is-active' : ''; ?>" data-dot-index="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Rezension %d', 'globalkeys' ), $i + 1 ) ); ?>"></button>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<script>
(function() {
	var root = document.currentScript && document.currentScript.previousElementSibling;
	if (!root || !root.classList.contains('gk-section-platform-pc-community')) {
		root = document.querySelector('.gk-section-platform-pc-community');
	}
	if (!root) { return; }
	var slides = Array.prototype.slice.call(root.querySelectorAll('.gk-pc-community__content'));
	var dots = Array.prototype.slice.call(root.querySelectorAll('.gk-pc-community__dots button'));
	var prev = root.querySelector('.gk-pc-community__arrow--prev');
	var next = root.querySelector('.gk-pc-community__arrow--next');
	if (!slides.length) { return; }
	var current = 0;
	var setSlide = function(index) {
		current = (index + slides.length) % slides.length;
		slides.forEach(function(slide, i) {
			var active = i === current;
			slide.classList.toggle('is-active', active);
			slide.setAttribute('aria-hidden', active ? 'false' : 'true');
		});
		dots.forEach(function(dot, i) {
			dot.classList.toggle('is-active', i === current);
			dot.setAttribute('aria-current', i === current ? 'true' : 'false');
		});
	};
	if (prev) {
		prev.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			setSlide(current - 1);
		});
	}
	if (next) {
		next.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			setSlide(current + 1);
		});
	}
	dots.forEach(function(dot, i) {
		dot.addEventListener('click', function() { setSlide(i); });
	});
	var wrap = root.querySelector('.gk-pc-community__carousel-wrap');
	if (wrap) {
		wrap.addEventListener('wheel', function(e) {
			var dx = e.deltaX;
			var dy = e.deltaY;
			if (e.shiftKey && Math.abs(dy) > 8) {
				e.preventDefault();
				if (dy > 0) {
					setSlide(current + 1);
				} else {
					setSlide(current - 1);
				}
				return;
			}
			if (Math.abs(dx) <= Math.abs(dy)) {
				return;
			}
			if (Math.abs(dx) < 6) {
				return;
			}
			e.preventDefault();
			if (dx > 0) {
				setSlide(current + 1);
			} else {
				setSlide(current - 1);
			}
		}, { passive: false });
		var touchStartX = 0;
		var onTouchStart = function(ev) {
			if (ev.touches && ev.touches.length === 1) {
				touchStartX = ev.touches[0].clientX;
			}
		};
		var onTouchEnd = function(ev) {
			if (!ev.changedTouches || !ev.changedTouches.length) {
				return;
			}
			var dx = ev.changedTouches[0].clientX - touchStartX;
			if (Math.abs(dx) < 48) {
				return;
			}
			if (dx < 0) {
				setSlide(current + 1);
			} else {
				setSlide(current - 1);
			}
		};
		wrap.addEventListener('touchstart', onTouchStart, { passive: true });
		wrap.addEventListener('touchend', onTouchEnd, { passive: true });
	}
	root.addEventListener('keydown', function(e) {
		if (e.key === 'ArrowLeft') {
			e.preventDefault();
			setSlide(current - 1);
		} else if (e.key === 'ArrowRight') {
			e.preventDefault();
			setSlide(current + 1);
		}
	});
	setSlide(0);
})();
</script>

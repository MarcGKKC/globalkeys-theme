<?php
/**
 * Template part: Our Categories – 3 Karten pro Slide, Carousel, gleiche Überschrift wie zuvor.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-categories', 'aria_label' => __( 'Kategorien', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-categories';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'All Categories', 'globalkeys' );

$gk_category_collection_names = array(
	__( 'Survival', 'globalkeys' ),
	__( 'Adventure', 'globalkeys' ),
	__( 'Action', 'globalkeys' ),
	__( 'RPG', 'globalkeys' ),
	__( 'Shooter', 'globalkeys' ),
	__( 'Simulation', 'globalkeys' ),
	__( 'Sport', 'globalkeys' ),
	__( 'Strategy', 'globalkeys' ),
	__( 'Horror', 'globalkeys' ),
	__( 'Indie', 'globalkeys' ),
	__( 'Multiplayer', 'globalkeys' ),
	__( 'Story', 'globalkeys' ),
	__( 'Casual', 'globalkeys' ),
	__( 'Open World', 'globalkeys' ),
	__( 'New Releases', 'globalkeys' ),
);

$gk_categories_bg_url = get_template_directory_uri() . '/Pictures/category-card-bg.svg';
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-categories" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-categories-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'All Categories', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
	</div>
	<div class="gk-test-boxes-wrapper">
		<div class="gk-test-carousel" data-current="0">
			<div class="gk-test-carousel-inner">
				<button type="button" class="gk-test-arrow gk-test-arrow--prev" aria-label="<?php esc_attr_e( 'Vorherige', 'globalkeys' ); ?>">
					<span class="gk-test-arrow-circle"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></span>
				</button>
				<div class="gk-test-slides">
					<?php for ( $page = 0; $page < 5; $page++ ) : ?>
					<div class="gk-test-slide<?php echo 0 === $page ? ' is-active' : ''; ?>" data-page="<?php echo (int) $page; ?>">
						<div class="gk-test-boxes-row">
							<?php for ( $i = 1; $i <= 3; $i++ ) : $idx = $page * 3 + $i - 1; $label = isset( $gk_category_collection_names[ $idx ] ) ? $gk_category_collection_names[ $idx ] : ''; ?>
							<div class="gk-test-box gk-test-box--bg" style="--gk-card-bg: url('<?php echo esc_url( $gk_categories_bg_url ); ?>');">
								<span class="gk-test-box-label">
									<span class="gk-test-box-label-line" aria-hidden="true"></span>
									<span class="gk-test-box-label-text"><?php echo esc_html( $label ); ?></span>
								</span>
							</div>
							<?php endfor; ?>
						</div>
					</div>
					<?php endfor; ?>
				</div>
				<button type="button" class="gk-test-arrow gk-test-arrow--next" aria-label="<?php esc_attr_e( 'Nächste', 'globalkeys' ); ?>">
					<span class="gk-test-arrow-circle"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
				</button>
			</div>
		</div>
		<nav class="gk-test-pagination gk-categories-carousel-dots" aria-label="<?php esc_attr_e( 'Kategorien-Seiten', 'globalkeys' ); ?>">
			<?php for ( $d = 0; $d < 5; $d++ ) : ?>
			<button type="button" class="gk-test-dot<?php echo 0 === $d ? ' is-active' : ''; ?>" data-page="<?php echo (int) $d; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Seite %d', 'globalkeys' ), $d + 1 ) ); ?>" aria-current="<?php echo 0 === $d ? 'true' : 'false'; ?>"></button>
			<?php endfor; ?>
		</nav>
	</div>
	<script>
	(function () {
		var root = document.getElementById( <?php echo wp_json_encode( $id ); ?> );
		if ( ! root ) return;
		var car = root.querySelector( '.gk-test-carousel' );
		if ( ! car || car.getAttribute( 'data-gk-bound' ) === '1' ) return;
		car.setAttribute( 'data-gk-bound', '1' );
		var slides = root.querySelectorAll( '.gk-test-slide' );
		if ( ! slides.length ) return;
		var prev = root.querySelector( '.gk-test-arrow--prev' );
		var next = root.querySelector( '.gk-test-arrow--next' );
		var dots = root.querySelectorAll( '.gk-test-dot' );
		var total = slides.length;
		function norm( p ) { return ( ( p % total ) + total ) % total; }
		function getCur() {
			var c = parseInt( car.getAttribute( 'data-current' ), 10 );
			return isNaN( c ) ? 0 : norm( c );
		}
		function goTo( p ) {
			p = norm( p );
			car.setAttribute( 'data-current', String( p ) );
			for ( var i = 0; i < slides.length; i++ ) {
				slides[ i ].classList.toggle( 'is-active', i === p );
			}
			for ( var j = 0; j < dots.length; j++ ) {
				dots[ j ].classList.toggle( 'is-active', j === p );
				dots[ j ].setAttribute( 'aria-current', j === p ? 'true' : 'false' );
			}
		}
		if ( prev ) prev.addEventListener( 'click', function () { goTo( getCur() - 1 ); } );
		if ( next ) next.addEventListener( 'click', function () { goTo( getCur() + 1 ); } );
		for ( var k = 0; k < dots.length; k++ ) {
			( function ( idx ) {
				dots[ idx ].addEventListener( 'click', function () { goTo( idx ); } );
			} )( k );
		}
		var autoInterval = null;
		function startAuto() {
			if ( autoInterval ) clearInterval( autoInterval );
			autoInterval = setInterval( function () { goTo( getCur() + 1 ); }, 5000 );
		}
		function stopAuto() {
			if ( autoInterval ) { clearInterval( autoInterval ); autoInterval = null; }
		}
		startAuto();
		root.addEventListener( 'mouseenter', stopAuto );
		root.addEventListener( 'mouseleave', startAuto );
	})();
	</script>
</section>

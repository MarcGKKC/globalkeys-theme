<?php
/**
 * Template part for Categories Section
 *
 * Überschrift wie Featured, Inhalt (Kategorien/Produkte) kommt später.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-categories', 'aria_label' => __( 'Kategorien', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-categories';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Our Categories', 'globalkeys' );

$gk_category_labels = array(
	__( 'Survival Games', 'globalkeys' ),
	__( 'Casual Games', 'globalkeys' ),
	__( 'Adventure', 'globalkeys' ),
	__( 'Open World', 'globalkeys' ),
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
	__( 'New Releases', 'globalkeys' ),
	__( 'Bestseller', 'globalkeys' ),
	__( 'Sale', 'globalkeys' ),
	__( 'Preorder', 'globalkeys' ),
	__( 'Family', 'globalkeys' ),
	__( 'Puzzle', 'globalkeys' ),
);

$gk_categories_bg_url = get_template_directory_uri() . '/Pictures/category-card-bg.svg';
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-categories" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-categories-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Our Categories', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<!-- 4 Karten/Reihe (zurückkehren: 5 Karten, i<=5, idx=page*5+i-1) · Wrapper begrenzt Breite, Pfeile bleiben nah an Karten -->
		<div class="gk-categories-carousel" data-current="0">
			<div class="gk-categories-carousel-inner">
			<button type="button" class="gk-categories-arrow gk-categories-arrow--prev" aria-label="<?php esc_attr_e( 'Vorherige', 'globalkeys' ); ?>">
				<span class="gk-categories-arrow-circle"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></span>
			</button>
			<div class="gk-categories-slides">
				<?php for ( $page = 0; $page < 4; $page++ ) : ?>
				<div class="gk-categories-slide<?php echo 0 === $page ? ' is-active' : ''; ?>" data-page="<?php echo (int) $page; ?>">
					<div class="gk-categories-cards">
						<?php for ( $i = 1; $i <= 4; $i++ ) : $idx = $page * 4 + $i - 1; $label = isset( $gk_category_labels[ $idx ] ) ? $gk_category_labels[ $idx ] : ''; ?>
						<div class="gk-categories-card">
							<span class="gk-categories-card-inner gk-categories-card-inner--bg" style="--gk-card-bg: url('<?php echo esc_url( $gk_categories_bg_url ); ?>');">
								<span class="gk-categories-card-label"><?php echo esc_html( $label ); ?></span>
							</span>
						</div>
						<?php endfor; ?>
					</div>
				</div>
				<?php endfor; ?>
			</div>
			<button type="button" class="gk-categories-arrow gk-categories-arrow--next" aria-label="<?php esc_attr_e( 'Nächste', 'globalkeys' ); ?>">
				<span class="gk-categories-arrow-circle"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
			</button>
			</div>
		</div>
		<nav class="gk-categories-pagination" aria-label="<?php esc_attr_e( 'Kategorien-Seiten', 'globalkeys' ); ?>">
			<?php for ( $d = 0; $d < 4; $d++ ) : ?>
			<button type="button" class="gk-categories-dot<?php echo 0 === $d ? ' is-active' : ''; ?>" data-page="<?php echo (int) $d; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Seite %d', 'globalkeys' ), $d + 1 ) ); ?>" aria-current="<?php echo 0 === $d ? 'true' : 'false'; ?>"></button>
			<?php endfor; ?>
		</nav>
	</div>
</section>

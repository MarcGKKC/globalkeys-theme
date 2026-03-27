<?php
/**
 * Template für Plattform-Seiten (PC, PlayStation, Xbox, Nintendo).
 * PC: … Gift cards, Pre-orders, Category grid (ganz unten).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_platform_slug = get_query_var( 'gk_platform' );
if ( ! is_string( $gk_platform_slug ) || $gk_platform_slug === '' ) {
	$gk_platform_slug = '';
}

get_header();
?>

	<main id="primary" class="site-main site-main--platform gk-platform-page" data-platform="<?php echo esc_attr( $gk_platform_slug ); ?>">

		<?php if ( $gk_platform_slug === 'pc' ) : ?>
			<div class="gk-platform-header">
				<h1 class="gk-platform-title"><?php esc_html_e( 'PC', 'globalkeys' ); ?></h1>
				<p class="gk-platform-desc"><?php esc_html_e( 'Discover the best PC games, DLC, pre-orders and PC bestsellers Globalkeys.co', 'globalkeys' ); ?></p>
				<nav class="gk-platform-stores-bar" aria-label="<?php esc_attr_e( 'PC stores', 'globalkeys' ); ?>">
					<a href="#"><?php esc_html_e( 'Steam', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'EA App', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Rockstar', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Epic Games', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Battle.net', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Microsoft Store', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Ubisoft Connect', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Roblox', 'globalkeys' ); ?></a>
				</nav>
				<nav class="gk-platform-nav-below" aria-label="<?php esc_attr_e( 'Browse', 'globalkeys' ); ?>">
					<a href="<?php echo esc_url( home_url( '/trending-games/' ) ); ?>"><?php esc_html_e( 'Trending', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/preorders/' ) ); ?>"><?php esc_html_e( 'Bestsellers', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/preorders/' ) ); ?>"><?php esc_html_e( 'Pre-orders', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Gift cards', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Subscriptions', 'globalkeys' ); ?></a>
				</nav>
			</div>
			<div class="gk-section-inner gk-section-featured-inner">
				<?php get_template_part( 'template-parts/platform-featured-carousel' ); ?>
			</div>
			<?php get_template_part( 'template-parts/section-platform-trending' ); ?>
			<?php get_template_part( 'template-parts/section-platform-best-with-friends' ); ?>
			<?php get_template_part( 'template-parts/section-platform-spotlight-banner' ); ?>
			<?php get_template_part( 'template-parts/section-platform-pc-gift-cards' ); ?>
			<?php get_template_part( 'template-parts/section', 'preorders' ); ?>
			<?php get_template_part( 'template-parts/section-platform-pc-category-grid' ); ?>
		<?php elseif ( $gk_platform_slug === 'playstation' ) : ?>
			<div class="gk-platform-header">
				<h1 class="gk-platform-title"><?php esc_html_e( 'PlayStation', 'globalkeys' ); ?></h1>
				<p class="gk-platform-desc"><?php esc_html_e( 'Discover the best PlayStation games, DLC, pre-orders and PlayStation bestsellers Globalkeys.co', 'globalkeys' ); ?></p>
				<nav class="gk-platform-stores-bar" aria-label="<?php esc_attr_e( 'PlayStation consoles', 'globalkeys' ); ?>">
					<a href="#"><?php esc_html_e( 'PlayStation 4', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'PlayStation 5', 'globalkeys' ); ?></a>
				</nav>
				<nav class="gk-platform-nav-below" aria-label="<?php esc_attr_e( 'Browse', 'globalkeys' ); ?>">
					<a href="<?php echo esc_url( home_url( '/trending-games/' ) ); ?>"><?php esc_html_e( 'Trending', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/preorders/' ) ); ?>"><?php esc_html_e( 'Bestsellers', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Gift cards', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Subscriptions', 'globalkeys' ); ?></a>
				</nav>
			</div>
			<div class="gk-section-inner gk-section-featured-inner">
				<?php get_template_part( 'template-parts/platform-featured-carousel' ); ?>
			</div>
			<?php get_template_part( 'template-parts/section-platform-trending' ); ?>
			<?php get_template_part( 'template-parts/section-platform-best-with-friends' ); ?>
			<?php get_template_part( 'template-parts/section-platform-spotlight-banner' ); ?>
			<?php get_template_part( 'template-parts/section', 'preorders' ); ?>
		<?php elseif ( $gk_platform_slug === 'xbox' ) : ?>
			<div class="gk-platform-header">
				<h1 class="gk-platform-title"><?php esc_html_e( 'Xbox', 'globalkeys' ); ?></h1>
				<p class="gk-platform-desc"><?php esc_html_e( 'Discover the best Xbox games, DLC, pre-orders and Xbox bestsellers Globalkeys.co', 'globalkeys' ); ?></p>
				<nav class="gk-platform-stores-bar" aria-label="<?php esc_attr_e( 'Xbox consoles', 'globalkeys' ); ?>">
					<a href="#"><?php esc_html_e( 'Xbox One', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Xbox Series X|S', 'globalkeys' ); ?></a>
				</nav>
				<nav class="gk-platform-nav-below" aria-label="<?php esc_attr_e( 'Browse', 'globalkeys' ); ?>">
					<a href="<?php echo esc_url( home_url( '/trending-games/' ) ); ?>"><?php esc_html_e( 'Trending', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/preorders/' ) ); ?>"><?php esc_html_e( 'Bestsellers', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/preorders/' ) ); ?>"><?php esc_html_e( 'Pre-orders', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Gift cards', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Subscriptions', 'globalkeys' ); ?></a>
				</nav>
			</div>
			<div class="gk-section-inner gk-section-featured-inner">
				<?php get_template_part( 'template-parts/platform-featured-carousel' ); ?>
			</div>
			<?php get_template_part( 'template-parts/section-platform-trending' ); ?>
			<?php get_template_part( 'template-parts/section-platform-best-with-friends' ); ?>
			<?php get_template_part( 'template-parts/section-platform-spotlight-banner' ); ?>
			<?php get_template_part( 'template-parts/section', 'preorders' ); ?>
		<?php elseif ( $gk_platform_slug === 'nintendo' ) : ?>
			<div class="gk-platform-header">
				<h1 class="gk-platform-title"><?php esc_html_e( 'Nintendo', 'globalkeys' ); ?></h1>
				<p class="gk-platform-desc"><?php esc_html_e( 'Discover the best Nintendo games, DLC, pre-orders and Nintendo bestsellers Globalkeys.co', 'globalkeys' ); ?></p>
				<nav class="gk-platform-stores-bar" aria-label="<?php esc_attr_e( 'Nintendo systems', 'globalkeys' ); ?>">
					<a href="#"><?php esc_html_e( 'Nintendo Switch', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Nintendo Switch 2', 'globalkeys' ); ?></a>
				</nav>
				<nav class="gk-platform-nav-below" aria-label="<?php esc_attr_e( 'Browse', 'globalkeys' ); ?>">
					<a href="<?php echo esc_url( home_url( '/trending-games/' ) ); ?>"><?php esc_html_e( 'Trending', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/preorders/' ) ); ?>"><?php esc_html_e( 'Bestsellers', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/preorders/' ) ); ?>"><?php esc_html_e( 'Pre-orders', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Gift cards', 'globalkeys' ); ?></a>
					<a href="#"><?php esc_html_e( 'Subscriptions', 'globalkeys' ); ?></a>
				</nav>
			</div>
			<div class="gk-section-inner gk-section-featured-inner">
				<?php get_template_part( 'template-parts/platform-featured-carousel' ); ?>
			</div>
			<?php get_template_part( 'template-parts/section-platform-trending' ); ?>
			<?php get_template_part( 'template-parts/section-platform-best-with-friends' ); ?>
			<?php get_template_part( 'template-parts/section-platform-spotlight-banner' ); ?>
			<?php get_template_part( 'template-parts/section', 'preorders' ); ?>
		<?php endif; ?>

	</main><!-- #primary -->

<?php
get_footer();

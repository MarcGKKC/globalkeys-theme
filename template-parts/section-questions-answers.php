<?php
/**
 * Template part: Questions & Answers (unter Become a Premium Member / Premium-Member-CTA).
 * Links: 3 Kategorie-Würfel (Icon + Überschrift); rechts: FAQ-Akkordeon (filterbar).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = get_query_var( 'gk_section', array( 'id' => 'section-questions-answers', 'aria_label' => __( 'Questions & Answers', 'globalkeys' ) ) );
$id      = ! empty( $section['id'] ) ? $section['id'] : 'section-questions-answers';

$gk_qa_default_categories = array(
	array(
		'slug' => 'shop',
		'label' => __( 'General/info', 'globalkeys' ),
		'icon'  => 'shop',
	),
	array(
		'slug' => 'payment',
		'label' => __( 'Payments/Pricing', 'globalkeys' ),
		'icon'  => 'payment',
	),
	array(
		'slug' => 'trust',
		'label' => __( 'Security/Support', 'globalkeys' ),
		'icon'  => 'trust',
	),
);

$gk_qa_categories = apply_filters( 'globalkeys_questions_answers_categories', $gk_qa_default_categories );
if ( ! is_array( $gk_qa_categories ) ) {
	$gk_qa_categories = $gk_qa_default_categories;
}
$gk_qa_categories = array_values(
	array_filter(
		$gk_qa_categories,
		static function ( $row ) {
			return is_array( $row )
				&& ! empty( $row['slug'] )
				&& ! empty( $row['label'] )
				&& is_string( $row['slug'] )
				&& is_string( $row['label'] );
		}
	)
);

$gk_qa_default_items = array(
	array(
		'question' => __( 'Who is GlobalKeys and what do they do?', 'globalkeys' ),
		'answer'   => __(
			"GlobalKeys is a digital games shop: you browse the catalogue, pay online, and receive digital keys or redemption details in your account after purchase. You then activate them on the right platform (for example Steam or PlayStation), depending on the product.\n\n"
			. "There is no physical shipping for keys—delivery is digital. Always read each product page before checkout so you know exactly what is included.",
			'globalkeys'
		),
		'category' => 'shop',
	),
	array(
		'question' => __( 'Can you also buy DLCs, gift cards, and subscriptions from GlobalKeys?', 'globalkeys' ),
		'answer'   => __(
			"Yes, when they are listed in the store. Besides full games you can find DLCs, selected gift cards, and subscription-style offers wherever suppliers and licenses allow.\n\n"
			. "The catalogue changes over time. Each product page states platform, content, and restrictions—especially region and country—so check that before you buy.",
			'globalkeys'
		),
		'category' => 'shop',
	),
	array(
		'question' => __( 'How are GlobalKeys able to offer such competitive prices?', 'globalkeys' ),
		'answer'   => __(
			"Prices reflect publisher deals, promotions, currency, and how digital supply is sourced. GlobalKeys works with official partners so strong offers can sit next to a broad catalogue.\n\n"
			. "Digital delivery avoids many costs of physical retail. Premium members can save more with extra discounts; everyone sees clear product information at checkout.",
			'globalkeys'
		),
		'category' => 'payment',
	),
	array(
		'question' => __( 'Which payment methods can you use at GlobalKeys?', 'globalkeys' ),
		'answer'   => __(
			"Checkout only shows methods that work for your order and country—cards, PayPal or similar wallets, and sometimes local options, depending on rules and partners.\n\n"
			. "If something you expected is missing, it is usually regional. Try another listed method or contact GlobalKeys Support with your country and a checkout screenshot.",
			'globalkeys'
		),
		'category' => 'payment',
	),
	array(
		'question' => __( 'How safe and reliable is your shopping experience with GlobalKeys?', 'globalkeys' ),
		'answer'   => __(
			"Payments run through established providers using common industry security practices. You should get what you pay for, and your data is handled with care.\n\n"
			. "Protect your account with a strong password and secure email. If a charge, key, or product looks wrong, contact GlobalKeys Support with your order details.",
			'globalkeys'
		),
		'category' => 'trust',
	),
	array(
		'question' => __( 'How can you contact the GlobalKeys Support if you need help?', 'globalkeys' ),
		'answer'   => __(
			"Use the Help or Contact section on the site, or the contact details in the legal notice (Imprint). Include your order number, purchase email, and a short note about what you see on screen.\n\n"
			. "Replies can take longer during busy periods. Clear details (platform, product, error message, screenshots if useful) speed things up.",
			'globalkeys'
		),
		'category' => 'trust',
	),
);

$gk_qa_items = apply_filters( 'globalkeys_questions_answers_faq_items', $gk_qa_default_items );
if ( ! is_array( $gk_qa_items ) ) {
	$gk_qa_items = $gk_qa_default_items;
}

$gk_slugs = array_values( array_filter( array_map( 'sanitize_key', wp_list_pluck( $gk_qa_categories, 'slug' ) ) ) );

/** @var array<int, array{question: string, answer: string, category?: string}> $gk_qa_items */
$gk_qa_items = array_values(
	array_filter(
		$gk_qa_items,
		static function ( $row ) {
			return is_array( $row )
				&& isset( $row['question'], $row['answer'] )
				&& is_string( $row['question'] )
				&& $row['question'] !== ''
				&& is_string( $row['answer'] )
				&& trim( $row['answer'] ) !== '';
		}
	)
);

$gk_qa_extra = apply_filters( 'globalkeys_questions_answers_section_content', '' );
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-bestsellers gk-section-featured gk-section-questions-answers" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Questions & Answers', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
			</h2>
		</div>

		<?php if ( ( is_string( $gk_qa_extra ) && $gk_qa_extra !== '' ) || ! empty( $gk_qa_items ) ) : ?>
		<div class="gk-section-questions-answers__row<?php echo ! empty( $gk_qa_categories ) ? ' gk-section-questions-answers__row--with-categories' : ''; ?>">
			<?php if ( ! empty( $gk_qa_categories ) ) : ?>
			<div class="gk-faq-categories" role="toolbar" aria-label="<?php esc_attr_e( 'FAQ categories', 'globalkeys' ); ?>">
				<?php foreach ( $gk_qa_categories as $gk_cat ) : ?>
					<?php
					$gk_cat_slug = sanitize_key( $gk_cat['slug'] );
					$gk_icon     = isset( $gk_cat['icon'] ) ? (string) $gk_cat['icon'] : $gk_cat_slug;
					$gk_icon_html = function_exists( 'globalkeys_faq_category_icon_svg' ) ? globalkeys_faq_category_icon_svg( $gk_icon ) : '';
					if ( $gk_icon_html === '' ) {
						continue;
					}
					?>
				<button type="button" class="gk-faq-category-tile" data-category-filter="<?php echo esc_attr( $gk_cat_slug ); ?>" aria-pressed="false">
					<span class="gk-faq-category-tile__icon" aria-hidden="true"><?php echo $gk_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="gk-faq-category-tile__label"><?php echo esc_html( $gk_cat['label'] ); ?></span>
				</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="gk-section-questions-answers__column">
				<?php if ( is_string( $gk_qa_extra ) && $gk_qa_extra !== '' ) : ?>
				<div class="gk-section-questions-answers__intro">
					<?php echo wp_kses_post( $gk_qa_extra ); ?>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $gk_qa_items ) ) : ?>
				<div class="gk-faq-list">
					<?php foreach ( $gk_qa_items as $gk_i => $gk_row ) : ?>
						<?php
						$gk_q_id = $id . '-faq-' . (int) $gk_i;
						$gk_cat  = isset( $gk_row['category'] ) ? sanitize_key( (string) $gk_row['category'] ) : '';
						if ( $gk_cat === '' || ! in_array( $gk_cat, $gk_slugs, true ) ) {
							$gk_cat = ! empty( $gk_slugs[0] ) ? $gk_slugs[0] : 'shop';
						}
						?>
					<details class="gk-faq-item" data-category="<?php echo esc_attr( $gk_cat ); ?>">
						<summary class="gk-faq-item__summary" id="<?php echo esc_attr( $gk_q_id ); ?>-label">
							<span class="gk-faq-item__question"><?php echo esc_html( $gk_row['question'] ); ?></span>
							<span class="gk-faq-item__chevron" aria-hidden="true">
								<svg class="gk-faq-item__chevron-svg" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
							</span>
						</summary>
						<div class="gk-faq-item__answer" role="region" aria-labelledby="<?php echo esc_attr( $gk_q_id ); ?>-label">
							<div class="gk-faq-item__answer-inner">
								<?php echo wp_kses_post( wpautop( $gk_row['answer'] ) ); ?>
							</div>
						</div>
					</details>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>

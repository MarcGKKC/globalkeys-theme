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
		'question' => __( 'What is Globalkeys and how does it work?', 'globalkeys' ),
		'answer'   => __( 'Globalkeys is a digital games store: you buy a product key, receive it in your account after purchase, and redeem it on the relevant platform (e.g. Steam, PlayStation).', 'globalkeys' ),
		'category' => 'shop',
	),
	array(
		'question' => __( 'Do you also sell DLCs, gift cards, and subscriptions?', 'globalkeys' ),
		'answer'   => __( 'Yes, when they are listed in the store. Details and region or country notes are shown on each product page.', 'globalkeys' ),
		'category' => 'shop',
	),
	array(
		'question' => __( 'How do you offer such competitive prices?', 'globalkeys' ),
		'answer'   => __( 'We work with official partners and use regional offers. Premium members also benefit from exclusive discounts.', 'globalkeys' ),
		'category' => 'payment',
	),
	array(
		'question' => __( 'Which payment methods can I use?', 'globalkeys' ),
		'answer'   => __( 'Available payment options are shown at checkout. Depending on your country, options may include card, PayPal, and other providers.', 'globalkeys' ),
		'category' => 'payment',
	),
	array(
		'question' => __( 'Is the store trustworthy and secure?', 'globalkeys' ),
		'answer'   => __( 'We use established payment providers and protect your account with common security standards. If you have questions, our support team can help.', 'globalkeys' ),
		'category' => 'trust',
	),
	array(
		'question' => __( 'How can I contact you?', 'globalkeys' ),
		'answer'   => __( 'Use the contact or help sections on the website, or email us at the address given in the legal notice. Response times may vary depending on volume.', 'globalkeys' ),
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
							<span class="gk-faq-item__chevron" aria-hidden="true"></span>
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

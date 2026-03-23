<?php
/**
 * Produkt-Meta: URL zu einem kurzen Preview-Video (WebM/MP4) pro WooCommerce-Produkt.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Eingabefeld unter „Produktdaten“ → Tab „Allgemein“.
 */
function globalkeys_product_trailer_url_field() {
	global $post;

	$value = $post ? get_post_meta( $post->ID, '_gk_product_trailer_url', true ) : '';
	$value_carousel = $post ? get_post_meta( $post->ID, '_gk_product_trailer_carousel_url', true ) : '';
	?>
	<div class="options_group">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => '_gk_product_trailer_url',
				'name'        => '_gk_product_trailer_url',
				'label'       => __( 'Preview-Video (URL)', 'globalkeys' ),
				'placeholder' => 'https://… oder /wp-content/themes/.../datei.webm',
				'description' => __( 'Volle URL zur WebM- oder MP4-Datei (z. B. aus der Mediathek oder Theme-Ordner Previews/). Wird z. B. für Hover-Trailer auf Karten genutzt.', 'globalkeys' ),
				'desc_tip'    => true,
				'value'       => $value,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_gk_product_trailer_carousel_url',
				'name'        => '_gk_product_trailer_carousel_url',
				'label'       => __( 'Carousel-Trailer (HD, optional)', 'globalkeys' ),
				'placeholder' => '',
				'description' => __( 'Optional: HD-Version (z. B. 1920×1080) für die Produktvorschau auf der Plattform-Seite. Leer = Standard-Trailer.', 'globalkeys' ),
				'desc_tip'    => true,
				'value'       => $value_carousel,
			)
		);
		?>
	</div>
	<?php
}
add_action( 'woocommerce_product_options_general_product_data', 'globalkeys_product_trailer_url_field' );

/**
 * URL speichern.
 *
 * @param WC_Product $product Produktobjekt.
 */
function globalkeys_save_product_trailer_url( $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	if ( isset( $_POST['_gk_product_trailer_url'] ) ) {
		$url = trim( wp_unslash( $_POST['_gk_product_trailer_url'] ) );
		if ( $url === '' ) {
			$product->delete_meta_data( '_gk_product_trailer_url' );
		} else {
			$safe = esc_url_raw( $url );
			if ( $safe === '' ) {
				$safe = sanitize_text_field( $url );
			}
			$product->update_meta_data( '_gk_product_trailer_url', $safe );
		}
	}

	if ( isset( $_POST['_gk_product_trailer_carousel_url'] ) ) {
		$url = trim( wp_unslash( $_POST['_gk_product_trailer_carousel_url'] ) );
		if ( $url === '' ) {
			$product->delete_meta_data( '_gk_product_trailer_carousel_url' );
		} else {
			$safe = esc_url_raw( $url );
			if ( $safe === '' ) {
				$safe = sanitize_text_field( $url );
			}
			$product->update_meta_data( '_gk_product_trailer_carousel_url', $safe );
		}
	}
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_save_product_trailer_url', 10, 1 );

/**
 * Theme-Standard-Preview, wenn am Produkt kein eigenes Video hinterlegt ist.
 *
 * @param WC_Product $product Produkt.
 * @return string Volle URL, sonst leer.
 */
function globalkeys_get_default_product_trailer_url( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$slug = $product->get_slug();
	/** @var array<string, string> Slug (Woo) → Theme-Datei unter Previews/ */
	$trailers_by_slug = array(
		'ready-or-not-boiling-point' => 'ron-gk.webm',
		'ready-or-not'               => 'ron-game-gk.webm',
		'ready-or-not-pc'            => 'ron-game-gk.webm',
		'ready-or-not-steam'         => 'ron-game-gk.webm',
		'ready-or-not-pc-steam'      => 'ron-game-gk.webm',
		'arc-raiders'                  => 'arc-raiders-pc-steam-preview.webm',
		'arc-raiders-pc'               => 'arc-raiders-pc-steam-preview.webm',
		'arc-raiders-pc-steam'         => 'arc-raiders-pc-steam-preview.webm',
		'arc-raiders-steam'            => 'arc-raiders-pc-steam-preview.webm',
		'resident-evil-requiem'        => 'rer-gk.webm',
		'resident-evil-requiem-pc'    => 'rer-gk.webm',
		'resident-evil-requiem-steam' => 'rer-gk.webm',
		'marathon'                    => 'marathon-gk.webm',
		'marathon-pc'                 => 'marathon-gk.webm',
		'marathon-steam'              => 'marathon-gk.webm',
		'marathon-pc-steam'           => 'marathon-gk.webm',
		'pokemon-pokopia'             => 'pokemon-pokopia-gk.webm',
		'pokemon-pokopia-pc'          => 'pokemon-pokopia-gk.webm',
		'pokemon-pokopia-steam'       => 'pokemon-pokopia-gk.webm',
		'pokemon-pokopia-pc-steam'    => 'pokemon-pokopia-gk.webm',
		'ea-sports-fc-26'             => 'eafc26-gk.webm',
		'ea-fc-26'                    => 'eafc26-gk.webm',
		'fc-26'                       => 'eafc26-gk.webm',
		'eafc-26'                     => 'eafc26-gk.webm',
		'eafc26'                      => 'eafc26-gk.webm',
		'elden-ring-shadow-of-the-erdtree' => 'ERSotE-gk.webm',
		'elden-ring-shadow-of-erdtree'      => 'ERSotE-gk.webm',
		'elden-ring-erdtree'                => 'ERSotE-gk.webm',
		'elden-ring-nightreign'             => 'elden-ring-n-preview-gk.webm',
		'elden-ring-nightreign-steam'       => 'elden-ring-n-preview-gk.webm',
		'elden-ring-nightreign-pc'          => 'elden-ring-n-preview-gk.webm',
		'elden-ring-nightreign-pc-steam'    => 'elden-ring-n-preview-gk.webm',
		'life-is-strange-reunion'              => 'life-is-strange-r-steam-preview.webm',
		'life-is-strange-reunion-steam'        => 'life-is-strange-r-steam-preview.webm',
		'life-is-strange-reunion-pc'           => 'life-is-strange-r-steam-preview.webm',
		'life-is-strange-reunion-pc-steam'     => 'life-is-strange-r-steam-preview.webm',
		'life-is-strange-r-steam'          => 'life-is-strange-r-steam-preview.webm',
		'lego-batman-legacy-of-the-dark-knight'             => 'lego-batman-lotDK-preview-gk.webm',
		'lego-batman-legacy-of-the-dark-knight-steam'      => 'lego-batman-lotDK-preview-gk.webm',
		'lego-batman-legacy-of-the-dark-knight-pc'         => 'lego-batman-lotDK-preview-gk.webm',
		'lego-batman-legacy-of-the-dark-knight-pc-steam'   => 'lego-batman-lotDK-preview-gk.webm',
		'lego-batman-lotdk'                                => 'lego-batman-lotDK-preview-gk.webm',
		'lego-batman-lotdk-steam'                          => 'lego-batman-lotDK-preview-gk.webm',
	);

	if ( isset( $trailers_by_slug[ $slug ] ) ) {
		return get_template_directory_uri() . '/Previews/' . $trailers_by_slug[ $slug ];
	}

	/* Ready or Not: Boiling Point (DLC) – vor dem Basisspiel prüfen */
	$title = $product->get_name();
	if ( $title !== '' && stripos( $title, 'ready or not' ) !== false && stripos( $title, 'boiling point' ) !== false ) {
		return get_template_directory_uri() . '/Previews/ron-gk.webm';
	}

	/* Ready or Not (Hauptspiel) – ohne Boiling Point im Titel */
	if ( $title !== '' && stripos( $title, 'ready or not' ) !== false ) {
		return get_template_directory_uri() . '/Previews/ron-game-gk.webm';
	}

	/* ARC Raiders – Titel-Match falls der Slug nicht in der Map steht */
	if ( $title !== '' && stripos( $title, 'arc raiders' ) !== false ) {
		return get_template_directory_uri() . '/Previews/arc-raiders-pc-steam-preview.webm';
	}

	/* Resident Evil Requiem */
	if ( $title !== '' && stripos( $title, 'resident evil' ) !== false && stripos( $title, 'requiem' ) !== false ) {
		return get_template_directory_uri() . '/Previews/rer-gk.webm';
	}

	/* Marathon (Bungie) – Titel enthält „Marathon“ */
	if ( $title !== '' && stripos( $title, 'marathon' ) !== false ) {
		return get_template_directory_uri() . '/Previews/marathon-gk.webm';
	}

	/* Pokémon / Pokemon Pokopia */
	if ( $title !== '' && stripos( $title, 'pokopia' ) !== false ) {
		return get_template_directory_uri() . '/Previews/pokemon-pokopia-gk.webm';
	}

	/* Life is Strange: Reunion */
	if ( $title !== '' && stripos( $title, 'life is strange' ) !== false && stripos( $title, 'reunion' ) !== false ) {
		return get_template_directory_uri() . '/Previews/life-is-strange-r-steam-preview.webm';
	}

	/* LEGO Batman: Legacy of the Dark Knight */
	if ( $title !== '' && stripos( $title, 'lego' ) !== false && stripos( $title, 'batman' ) !== false
		&& ( stripos( $title, 'legacy of the dark knight' ) !== false || stripos( $title, 'lotdk' ) !== false ) ) {
		return get_template_directory_uri() . '/Previews/lego-batman-lotDK-preview-gk.webm';
	}

	/* Elden Ring Nightreign – vor Shadow of the Erdtree (beides „Elden Ring“) */
	if ( $title !== '' && stripos( $title, 'elden ring' ) !== false && stripos( $title, 'nightreign' ) !== false ) {
		return get_template_directory_uri() . '/Previews/elden-ring-n-preview-gk.webm';
	}

	/* Elden Ring: Shadow of the Erdtree */
	if ( $title !== '' && (
		( stripos( $title, 'elden ring' ) !== false && stripos( $title, 'erdtree' ) !== false )
		|| stripos( $title, 'shadow of the erdtree' ) !== false
	) ) {
		return get_template_directory_uri() . '/Previews/ERSotE-gk.webm';
	}

	/* EA SPORTS FC 26 / EA FC 26 (nicht FC 262 o. Ä.) */
	if ( $title !== '' ) {
		if (
			preg_match( '/\bfc\W*26(?!\d)/iu', $title )
			|| preg_match( '/\beafc\W*26(?!\d)/iu', $title )
			|| preg_match( '/\beafc26\b/iu', $title )
		) {
			return get_template_directory_uri() . '/Previews/eafc26-gk.webm';
		}
	}

	return '';
}

/**
 * Hilfsfunktion: Trailer-URL eines Produkts (für Templates).
 *
 * @param WC_Product|int $product Produkt oder Post-ID.
 * @return string Leere Zeichenkette oder URL.
 */
function globalkeys_get_product_trailer_url( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	$url = $product->get_meta( '_gk_product_trailer_url' );
	$url = is_string( $url ) ? trim( $url ) : '';
	if ( $url !== '' ) {
		return $url;
	}

	return globalkeys_get_default_product_trailer_url( $product );
}

/**
 * Trailer-URL für HTML src auflösen (/pfad → vollständige URL).
 *
 * @param string $url Rohwert aus Meta.
 * @return string Leer oder gültige URL.
 */
function globalkeys_resolve_product_trailer_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}

	/*
	 * Häufiger Eingabefehler: http://wp-content/themes/... (ohne Domain – „wp-content“ wird als Host geparst).
	 * Korrigieren zu /wp-content/themes/... auf dieser Site.
	 */
	$parts = wp_parse_url( $url );
	if ( is_array( $parts ) && ! empty( $parts['host'] ) && strtolower( $parts['host'] ) === 'wp-content' && ! empty( $parts['path'] ) ) {
		$path = '/wp-content' . $parts['path'];
		return esc_url( home_url( $path ) );
	}

	if ( preg_match( '#^https?://#i', $url ) ) {
		return esc_url( $url );
	}
	if ( strpos( $url, '/' ) === 0 ) {
		return esc_url( home_url( $url ) );
	}
	return esc_url( $url );
}

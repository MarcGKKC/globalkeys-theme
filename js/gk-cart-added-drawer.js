( function( $, window, document ) {
	'use strict';

	if ( typeof gkCartDrawer === 'undefined' ) {
		return;
	}

	var overlay = document.getElementById( 'gk-added-cart-drawer-overlay' );
	var drawer = document.getElementById( 'gk-added-cart-drawer' );
	var body = document.getElementById( 'gk-added-cart-drawer-body' );
	if ( ! overlay || ! drawer || ! body ) {
		return;
	}

	var closeBtn = drawer.querySelector( '.gk-added-cart-drawer__close' );
	var activeRequest = null;
	var fallbackOpenTimer = null;
	var closeHideTimer = null;
	var DRAWER_ANIM_MS = 760;
	var isDrawerOpen = false;
	var lastFetchTs = 0;
	var lastFetchProductId = 0;
	var blockFallbackUntil = 0;
	var lastActionArmTs = 0;
	var pdpImmediateArmUntil = 0;
	var RELOAD_SUPPRESS_KEY = 'gk_drawer_suppress_next_open';
	var lastRealAddedTs = 0;
	var drawerFetchGen = 0;
	var externalSyncTimer = null;

	function escapeHtml( value ) {
		return String( value || '' )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#039;' );
	}

	/** Legacy-Markup ohne .header-cart-icon-wrap → Wrapper nachziehen (Badge sitzt am Icon). */
	function ensureHeaderCartIconWrap( link ) {
		var wrap = link.querySelector( '.header-cart-icon-wrap' );
		if ( wrap ) {
			return wrap;
		}
		var icon = link.querySelector( '.header-icon-cart' );
		if ( ! icon ) {
			return null;
		}
		wrap = document.createElement( 'span' );
		wrap.className = 'header-cart-icon-wrap';
		var badge = link.querySelector( '.header-cart-count' );
		icon.parentNode.insertBefore( wrap, icon );
		wrap.appendChild( icon );
		if ( badge && badge.parentNode === link ) {
			wrap.appendChild( badge );
		}
		return wrap;
	}

	/** Summe aller Stueckzahlen im Warenkorb — gleiche Logik wie WC()->cart->get_cart_contents_count(). */
	function updateHeaderCartBadge( count ) {
		var n = parseInt( count, 10 );
		if ( isNaN( n ) || n < 0 ) {
			n = 0;
		}
		var link = document.querySelector( 'a.header-cart-link' );
		if ( ! link ) {
			return;
		}
		var wrap = ensureHeaderCartIconWrap( link );
		if ( ! wrap ) {
			return;
		}
		var badge = wrap.querySelector( '.header-cart-count' );
		if ( n === 0 ) {
			if ( badge ) {
				badge.remove();
			}
			return;
		}
		if ( badge ) {
			badge.textContent = String( n );
		} else {
			var span = document.createElement( 'span' );
			span.className = 'header-cart-count';
			span.textContent = String( n );
			wrap.appendChild( span );
		}
	}

	/* Gleiche Pfade wie Pictures/cart.g.svg (Header-Warenkorb), fill = currentColor fuer Grau-Styling. */
	function getHeaderCartIconSvg() {
		return (
			'<svg class="gk-added-cart-drawer__empty-cart-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
				'<path fill="currentColor" d="M9.37493 8.26053C9.78333 8.19134 10.1705 8.46633 10.2397 8.87472L10.7679 11.9926C10.837 12.401 10.5621 12.7881 10.1537 12.8573C9.74527 12.9265 9.35811 12.6515 9.28893 12.2431L8.76074 9.12526C8.69155 8.71687 8.96654 8.32971 9.37493 8.26053Z"/>' +
				'<path fill="currentColor" d="M14.7891 8.87472C14.8582 8.46633 15.2454 8.19134 15.6538 8.26053C16.0622 8.32971 16.3372 8.71687 16.268 9.12526L15.7398 12.2431C15.6706 12.6515 15.2835 12.9265 14.8751 12.8573C14.4667 12.7881 14.1917 12.401 14.2609 11.9926L14.7891 8.87472Z"/>' +
				'<path fill="currentColor" d="M2.24896 2.29245C1.8582 2.15506 1.43005 2.36047 1.29266 2.75123C1.15527 3.142 1.36068 3.57015 1.75145 3.70754L2.01266 3.79937C2.68026 4.03409 3.11902 4.18964 3.44186 4.34805C3.74509 4.49683 3.87876 4.61726 3.96682 4.74612C4.05708 4.87821 4.12678 5.05963 4.16611 5.42298C4.20726 5.80319 4.20828 6.2984 4.20828 7.03835V9.75999C4.20828 11.2125 4.22191 12.2599 4.35897 13.0601C4.50529 13.9144 4.79742 14.526 5.34366 15.1022C5.93752 15.7285 6.69032 16.0012 7.58656 16.1283C8.44479 16.25 9.53464 16.25 10.8804 16.25L16.2861 16.25C17.0278 16.25 17.6518 16.25 18.1568 16.1882C18.6925 16.1227 19.1811 15.9793 19.6076 15.6318C20.0341 15.2842 20.2731 14.8346 20.4455 14.3232C20.6079 13.841 20.7339 13.2299 20.8836 12.5035L21.3925 10.0341L21.3935 10.0295L21.4039 9.97726C21.5686 9.15237 21.7071 8.45848 21.7416 7.90037C21.7777 7.31417 21.711 6.73616 21.3292 6.23977C21.0942 5.93435 20.7639 5.76144 20.4634 5.65586C20.1569 5.54817 19.8103 5.48587 19.4606 5.44677C18.7735 5.36997 17.9389 5.36998 17.1203 5.36999L5.66809 5.36999C5.6648 5.33324 5.66124 5.29709 5.6574 5.26156C5.60367 4.76518 5.48725 4.31246 5.20527 3.89982C4.92109 3.48396 4.54324 3.21762 4.10261 3.00142C3.69052 2.79922 3.16689 2.61514 2.55036 2.39841L2.24896 2.29245ZM5.70828 6.86999H17.089C17.9454 6.86999 18.6991 6.87099 19.2939 6.93748C19.5895 6.97052 19.8107 7.01642 19.9661 7.07104C20.0931 7.11568 20.1361 7.15213 20.1423 7.1574C20.2037 7.23881 20.2704 7.38651 20.2444 7.80796C20.217 8.25153 20.1005 8.84379 19.9229 9.73372L19.9225 9.73594L19.4237 12.1561C19.2623 12.9389 19.1537 13.4593 19.024 13.8441C18.9009 14.2095 18.7853 14.3669 18.66 14.469C18.5348 14.571 18.3573 14.6525 17.9746 14.6993C17.5714 14.7487 17.0399 14.75 16.2406 14.75H10.9377C9.5209 14.75 8.53783 14.7482 7.79716 14.6432C7.08235 14.5418 6.70473 14.3576 6.43219 14.0701C6.11202 13.7325 5.93933 13.4018 5.83744 12.8069C5.72628 12.1578 5.70828 11.249 5.70828 9.75999L5.70828 6.86999Z" clip-rule="evenodd" fill-rule="evenodd"/>' +
				'<path fill="currentColor" d="M7.5002 21.75C6.25756 21.75 5.2502 20.7426 5.2502 19.5C5.2502 18.2573 6.25756 17.25 7.5002 17.25C8.74285 17.25 9.7502 18.2573 9.7502 19.5C9.7502 20.7426 8.74285 21.75 7.5002 21.75ZM6.7502 19.5C6.7502 19.9142 7.08599 20.25 7.5002 20.25C7.91442 20.25 8.2502 19.9142 8.2502 19.5C8.2502 19.0858 7.91442 18.75 7.5002 18.75C7.08599 18.75 6.7502 19.0858 6.7502 19.5Z" clip-rule="evenodd" fill-rule="evenodd"/>' +
				'<path fill="currentColor" d="M16.5002 21.7501C15.2576 21.7501 14.2502 20.7427 14.2502 19.5001C14.2502 18.2574 15.2576 17.2501 16.5002 17.2501C17.7428 17.2501 18.7502 18.2574 18.7502 19.5001C18.7502 20.7427 17.7428 21.7501 16.5002 21.7501ZM15.7502 19.5001C15.7502 19.9143 16.086 20.2501 16.5002 20.2501C16.9144 20.2501 17.2502 19.9143 17.2502 19.5001C17.2502 19.0859 16.9144 18.7501 16.5002 18.7501C16.086 18.7501 15.7502 19.0859 15.7502 19.5001Z" clip-rule="evenodd" fill-rule="evenodd"/>' +
			'</svg>'
		);
	}

	function buildEmptyStateSectionHtml() {
		var ec = gkCartDrawer.emptyCart || {};
		var title = ec.title || 'Dein Warenkorb ist leer';
		var copy = ec.copy || '';
		var links = Array.isArray( ec.links ) ? ec.links : [];
		var linksHtml = '';
		for ( var li = 0; li < links.length; li++ ) {
			if ( ! links[ li ] || ! links[ li ].url ) {
				continue;
			}
			if ( linksHtml !== '' ) {
				linksHtml += '<span class="gk-added-cart-drawer__empty-sep" aria-hidden="true">•</span>';
			}
			linksHtml += '<a href="' + escapeHtml( links[ li ].url ) + '">' + escapeHtml( links[ li ].label || '' ) + '</a>';
		}
		return (
			'<section class="gk-added-cart-drawer__empty" aria-label="Leerer Warenkorb">' +
				'<div class="gk-added-cart-drawer__empty-icon" aria-hidden="true">' + getHeaderCartIconSvg() + '</div>' +
				'<h3 class="gk-added-cart-drawer__empty-title">' + escapeHtml( title ) + '</h3>' +
				( copy ? '<p class="gk-added-cart-drawer__empty-copy">' + escapeHtml( copy ) + '</p>' : '' ) +
				( linksHtml ? '<div class="gk-added-cart-drawer__empty-links">' + linksHtml + '</div>' : '' ) +
			'</section>'
		);
	}

	function buildRecommendationsHtml( recos ) {
		if ( ! recos || ! recos.length ) {
			return '';
		}
		var label = gkCartDrawer.recoLabel || 'We recommend';
		var cards = '';
		for ( var ri = 0; ri < recos.length; ri++ ) {
			var r = recos[ ri ];
			if ( ! r || ! r.url ) {
				continue;
			}
			var img = r.image
				? '<img src="' + escapeHtml( r.image ) + '" alt="" class="gk-added-cart-drawer__reco-img" loading="lazy" decoding="async" />'
				: '';
			var disc = r.discount ? '<span class="gk-added-cart-drawer__reco-discount">' + escapeHtml( r.discount ) + '</span>' : '';
			cards += '' +
				'<a class="gk-added-cart-drawer__reco-card" href="' + escapeHtml( r.url ) + '">' +
					'<span class="gk-added-cart-drawer__reco-img-wrap">' + img + '</span>' +
					'<span class="gk-added-cart-drawer__reco-body">' +
						'<span class="gk-added-cart-drawer__reco-name">' + escapeHtml( r.name || '' ) + '</span>' +
						( r.store ? '<span class="gk-added-cart-drawer__reco-store">' + escapeHtml( r.store ) + '</span>' : '' ) +
						'<span class="gk-added-cart-drawer__reco-price-row">' +
							'<span class="gk-added-cart-drawer__reco-price">' + escapeHtml( r.price || '' ) + '</span>' + disc +
						'</span>' +
					'</span>' +
				'</a>';
		}
		if ( cards === '' ) {
			return '';
		}
		return (
			'<div class="gk-added-cart-drawer__reco">' +
				'<h4 class="gk-added-cart-drawer__reco-title">' + escapeHtml( label ) + '</h4>' +
				'<div class="gk-added-cart-drawer__reco-row">' + cards + '</div>' +
			'</div>'
		);
	}

	function buildItemCard( item ) {
		var image = item.image ? '<img src="' + escapeHtml( item.image ) + '" alt="" class="gk-added-cart-drawer__item-image" loading="lazy" decoding="async" />' : '';
		var discount = item.discount ? '<span class="gk-added-cart-drawer__item-discount">' + escapeHtml( item.discount ) + '</span>' : '';
		var store = item.store ? '<p class="gk-added-cart-drawer__item-subline">' + escapeHtml( item.store ) + '</p>' : '';
		var cartItemKey = item.cartItemKey ? String( item.cartItemKey ) : '';
		var productId = parseInt( item.id || 0, 10 ) || 0;
		var removeUrl = item.removeUrl ? String( item.removeUrl ) : '';
		var removeDisabledAttr = ( cartItemKey || productId ) ? '' : ' disabled';
		var qtyOptions = '';
		for ( var q = 1; q <= 10; q++ ) {
			var selected = ( parseInt( item.quantity || 1, 10 ) === q ) ? ' selected' : '';
			qtyOptions += '<option value="' + q + '"' + selected + '>' + q + '</option>';
		}
		return '' +
			'<div class="gk-added-cart-drawer__item">' +
				'<a class="gk-added-cart-drawer__item-thumb" href="' + escapeHtml( item.url ) + '">' + image + '</a>' +
				'<div class="gk-added-cart-drawer__item-meta">' +
					'<a class="gk-added-cart-drawer__item-title" href="' + escapeHtml( item.url ) + '">' + escapeHtml( item.name ) + '</a>' +
					store +
					'<p class="gk-added-cart-drawer__item-price">' + escapeHtml( item.price ) + discount + '</p>' +
				'</div>' +
				'<div class="gk-added-cart-drawer__item-controls">' +
					'<select class="gk-added-cart-drawer__qty" data-cart-item-key="' + escapeHtml( cartItemKey ) + '" aria-label="' + escapeHtml( gkCartDrawer.qtyLabel || 'Qty' ) + '">' + qtyOptions + '</select>' +
					'<button type="button" class="gk-added-cart-drawer__remove" data-cart-item-key="' + escapeHtml( cartItemKey ) + '" data-product-id="' + String( productId ) + '" data-remove-url="' + escapeHtml( removeUrl ) + '" aria-label="Remove"' + removeDisabledAttr + '>' +
						'<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>' +
					'</button>' +
				'</div>' +
			'</div>';
	}

	function render( payload ) {
		var item = payload && payload.item ? payload.item : null;
		var cart = payload && payload.cart ? payload.cart : null;
		var cartItems = ( cart && Array.isArray( cart.items ) ) ? cart.items : ( payload && payload.cartItems ? payload.cartItems : [] );
		if ( ! cart ) {
			body.innerHTML = '';
			return;
		}

		var cartCount = parseInt( cart.count, 10 );
		if ( isNaN( cartCount ) ) {
			cartCount = 0;
		}
		/* Leerer Cart: nie „Einzel-Item“ aus item rendern (Server konnte trotzdem product aus product_id senden). */
		if ( cartCount === 0 ) {
			item = null;
		}

		var itemHtml = '';
		if ( cartItems && cartItems.length ) {
			for ( var ci = 0; ci < cartItems.length; ci++ ) {
				itemHtml += buildItemCard( cartItems[ ci ] );
			}
		} else if ( item ) {
			itemHtml = buildItemCard( item );
		}

		var recos = ( payload && Array.isArray( payload.recommendations ) ) ? payload.recommendations : [];
		var recoHtml = buildRecommendationsHtml( recos );

		var titleEl = drawer.querySelector( '.gk-added-cart-drawer__title' );
		if ( titleEl ) {
			var count = cart.count || 0;
			titleEl.innerHTML = 'Warenkorb <span class="gk-added-cart-drawer__title-count">(' + String( count ) + ' Produkte)</span>';
		}

		if ( ! item && ( ! cartItems || ! cartItems.length ) ) {
			body.innerHTML = '' +
				'<div class="gk-added-cart-drawer__main-scroll gk-added-cart-drawer__main-scroll--empty">' +
				buildEmptyStateSectionHtml() +
				'</div>';
			return;
		}

		body.innerHTML = '' +
			'<div class="gk-added-cart-drawer__main-scroll">' +
				'<div class="gk-added-cart-drawer__cart-scroll-block">' +
					'<div class="gk-added-cart-drawer__items-list">' +
						itemHtml +
					'</div>' +
					recoHtml +
				'</div>' +
			'</div>' +
			'<div class="gk-added-cart-drawer__footer">' +
				'<p class="gk-added-cart-drawer__total"><span>' + escapeHtml( gkCartDrawer.totalLabel || 'Total cart' ) + ':</span> <strong>' + escapeHtml( cart.total ) + '</strong></p>' +
				'<div class="gk-added-cart-drawer__actions">' +
					'<a class="gk-added-cart-drawer__btn gk-added-cart-drawer__btn--ghost" href="' + escapeHtml( cart.cartUrl || gkCartDrawer.cartUrl || '#' ) + '">' + escapeHtml( gkCartDrawer.goToCartLabel || 'Go to cart' ) + ' (' + String( cart.count || 0 ) + ')</a>' +
					'<a class="gk-added-cart-drawer__btn gk-added-cart-drawer__btn--cta" href="' + escapeHtml( cart.checkoutUrl || gkCartDrawer.checkoutUrl || '#' ) + '">' + escapeHtml( gkCartDrawer.checkoutLabel || 'Pay now' ) + '</a>' +
				'</div>' +
			'</div>';
	}

	function renderLoadingState() {
		body.innerHTML = '' +
			'<div class="gk-added-cart-drawer__loading">' +
				'<span class="gk-added-cart-drawer__spinner" aria-hidden="true"></span>' +
				'<p class="gk-added-cart-drawer__loading-text">Loading cart...</p>' +
			'</div>';
	}

	function openDrawer() {
		clearTimeout( closeHideTimer );
		var isAlreadyVisible = isDrawerOpen || ! drawer.hidden;
		overlay.hidden = false;
		drawer.hidden = false;
		if ( ! isAlreadyVisible ) {
			overlay.classList.remove( 'is-open' );
			drawer.classList.remove( 'is-open' );
			void drawer.offsetWidth;
			requestAnimationFrame( function() {
				requestAnimationFrame( function() {
					overlay.classList.add( 'is-open' );
					drawer.classList.add( 'is-open' );
				} );
			} );
		} else {
			overlay.classList.add( 'is-open' );
			drawer.classList.add( 'is-open' );
		}
		overlay.setAttribute( 'aria-hidden', 'false' );
		drawer.setAttribute( 'aria-hidden', 'false' );
		document.body.classList.add( 'gk-added-cart-drawer-open' );
		isDrawerOpen = true;
	}

	function closeDrawer() {
		overlay.classList.remove( 'is-open' );
		drawer.classList.remove( 'is-open' );
		closeHideTimer = setTimeout( function() {
			overlay.hidden = true;
			drawer.hidden = true;
		}, DRAWER_ANIM_MS );
		overlay.setAttribute( 'aria-hidden', 'true' );
		drawer.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'gk-added-cart-drawer-open' );
		isDrawerOpen = false;
	}

	function fetchDrawerData( productId, quantity, force ) {
		var now = Date.now();
		if ( ! force && productId > 0 && lastFetchProductId === productId && ( now - lastFetchTs ) < 700 ) {
			return;
		}
		if ( ! force && productId === 0 && lastFetchProductId === 0 && ( now - lastFetchTs ) < 700 ) {
			return;
		}
		lastFetchTs = now;
		lastFetchProductId = productId || 0;
		if ( activeRequest && typeof activeRequest.abort === 'function' ) {
			activeRequest.abort();
		}
		var myGen = ++drawerFetchGen;
		activeRequest = $.ajax( {
			url: gkCartDrawer.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'gk_cart_drawer_data',
				nonce: gkCartDrawer.nonce,
				product_id: productId || 0,
				quantity: quantity || 1
			}
		} ).done( function( res ) {
			if ( myGen !== drawerFetchGen ) {
				return;
			}
			if ( ! res || ! res.success || ! res.data ) {
				return;
			}
			render( res.data );
			var cart = res.data.cart;
			if ( cart && typeof cart.count !== 'undefined' ) {
				updateHeaderCartBadge( cart.count );
			}
		} );
	}

	/** Nur Drawer-HTML aus Server-Stand; kein wc_fragment_refresh (vermeidet Schleifen mit wc_fragments_refreshed). */
	function syncDrawerOnlyCart() {
		fetchDrawerData( 0, 1, true );
	}

	/** Nach eigenen Aktionen im Drawer: Mini-Cart / Fragmente mitziehen. */
	function refreshDrawerFromCurrentState() {
		syncDrawerOnlyCart();
		$( document.body ).trigger( 'wc_fragment_refresh' );
	}

	function scheduleExternalCartSync() {
		if ( ! isDrawerOpen ) {
			return;
		}
		clearTimeout( externalSyncTimer );
		externalSyncTimer = setTimeout( function() {
			syncDrawerOnlyCart();
		}, 100 );
	}

	function scheduleFallbackOpen( productId, quantity, forceOpen ) {
		clearTimeout( fallbackOpenTimer );
		fallbackOpenTimer = setTimeout( function() {
			if ( ! forceOpen && Date.now() < blockFallbackUntil ) {
				return;
			}
			if ( ! isDrawerOpen ) {
				renderLoadingState();
				openDrawer();
			}
			fetchDrawerData( productId || 0, quantity || 1 );
		}, 280 );
	}

	function armSingleDrawerAction( productId, quantity ) {
		var now = Date.now();
		if ( now - lastRealAddedTs < 1200 ) {
			return;
		}
		if ( now - lastActionArmTs < 500 ) {
			return;
		}
		lastActionArmTs = now;
		scheduleFallbackOpen( productId || 0, quantity || 1 );
	}

	function getProductIdFromCartForm( $form ) {
		return parseInt( $form.find( 'input[name="product_id"]' ).val() || $form.find( 'input[name="add-to-cart"]' ).val() || $form.find( 'button[name="add-to-cart"]' ).val(), 10 ) || 0;
	}

	function getQtyFromCartForm( $form ) {
		return parseInt( $form.find( 'input.qty' ).val(), 10 ) || 1;
	}

	$( document.body ).on(
		'removed_from_cart updated_cart_totals applied_coupon removed_coupon',
		scheduleExternalCartSync
	);

	$( document ).on( 'change', '.gk-added-cart-drawer__qty', function() {
		var key = $( this ).attr( 'data-cart-item-key' ) || '';
		var qty = parseInt( $( this ).val(), 10 ) || 1;
		if ( ! key ) {
			return;
		}
		$.ajax( {
			url: gkCartDrawer.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: { action: 'gk_cart_drawer_update_qty', nonce: gkCartDrawer.nonce, cart_item_key: key, quantity: qty }
		} ).always( function() {
			refreshDrawerFromCurrentState();
		} );
	} );

	$( document ).on( 'click', '.gk-added-cart-drawer__remove', function( e ) {
		e.preventDefault();
		e.stopPropagation();
		var $btn = $( e.currentTarget );
		var key = $btn.attr( 'data-cart-item-key' ) || '';
		var productId = parseInt( $btn.attr( 'data-product-id' ) || '0', 10 ) || 0;
		if ( ! key && ! productId ) {
			syncDrawerOnlyCart();
			return;
		}
		var linesVisible = body.querySelectorAll( '.gk-added-cart-drawer__item' ).length;
		$btn.prop( 'disabled', true );
		function finishRefresh() {
			refreshDrawerFromCurrentState();
			$btn.prop( 'disabled', false );
		}
		function tryClearLastResort() {
			if ( linesVisible > 1 ) {
				finishRefresh();
				return;
			}
			$.ajax( {
				url: gkCartDrawer.ajaxUrl,
				method: 'POST',
				dataType: 'json',
				data: { action: 'gk_cart_drawer_clear', nonce: gkCartDrawer.nonce }
			} ).always( finishRefresh );
		}
		$.ajax( {
			url: gkCartDrawer.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'gk_cart_drawer_remove_item',
				nonce: gkCartDrawer.nonce,
				cart_item_key: key,
				product_id: productId
			}
		} ).done( function( res ) {
			if ( res && res.success ) {
				finishRefresh();
				return;
			}
			tryClearLastResort();
		} ).fail( function() {
			tryClearLastResort();
		} );
	} );

	$( document.body ).on( 'added_to_cart', function( event, fragments, cartHash, $button ) {
		var nowTs = Date.now();
		if ( nowTs - lastRealAddedTs < 650 ) {
			return;
		}
		lastRealAddedTs = nowTs;
		blockFallbackUntil = lastRealAddedTs + 1800;
		clearTimeout( fallbackOpenTimer );
		try { sessionStorage.removeItem( 'gk_pending_add_to_cart' ); } catch ( ePending ) {}
		var productId = 0;
		var quantity = 1;
		if ( $button && $button.length ) {
			productId = parseInt( $button.data( 'product_id' ) || $button.attr( 'data-product_id' ) || $button.val(), 10 ) || 0;
			quantity = parseInt( $button.data( 'quantity' ) || $button.attr( 'data-quantity' ), 10 ) || 1;
		}
		if ( ! isDrawerOpen ) {
			renderLoadingState();
			openDrawer();
		}
		fetchDrawerData( productId, quantity );
	} );

	$( document ).on( 'click', 'a.add_to_cart_button, a[href*="add-to-cart="]', function() {
		var $btn = $( this );
		if ( $btn.closest( 'form.cart' ).length ) {
			return;
		}
		var productId = parseInt( $btn.attr( 'data-product_id' ) || $btn.data( 'product_id' ) || $btn.attr( 'value' ), 10 ) || 0;
		var quantity = parseInt( $btn.attr( 'data-quantity' ) || $btn.data( 'quantity' ), 10 ) || 1;
		if ( productId > 0 ) {
			sessionStorage.setItem( 'gk_pending_add_to_cart', JSON.stringify( { productId: productId, quantity: quantity } ) );
		}
		/* Öffnen erfolgt ausschließlich über Woo-Event/Redirect-Flow */
	} );

	$( document ).on( 'click', 'form.cart button.single_add_to_cart_button, form.cart button[name="add-to-cart"]', function() {
		var $form = $( this ).closest( 'form.cart' );
		if ( ! $form.length ) {
			return;
		}
		var pid = getProductIdFromCartForm( $form );
		var qty = getQtyFromCartForm( $form );
		if ( pid > 0 ) {
			sessionStorage.setItem( 'gk_pending_add_to_cart', JSON.stringify( { productId: pid, quantity: qty } ) );
		}
		pdpImmediateArmUntil = Date.now() + 900;
	} );

	$( document ).on( 'submit', 'form.cart', function() {
		var form = this;
		var $form = $( form );
		var pid = getProductIdFromCartForm( $form );
		var qty = getQtyFromCartForm( $form );
		if ( pid > 0 ) {
			sessionStorage.setItem( 'gk_pending_add_to_cart', JSON.stringify( { productId: pid, quantity: qty } ) );
		}
		/* PDP ohne Reload: Add-to-cart per AJAX, damit Position exakt bleibt */
		if ( window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url && pid > 0 ) {
			var ajaxUrl = window.wc_add_to_cart_params.wc_ajax_url.replace( '%%endpoint%%', 'add_to_cart' );
			var $submitBtn = $form.find( 'button.single_add_to_cart_button, button[name="add-to-cart"]' ).first();
			var variationId = parseInt( $form.find( 'input[name="variation_id"]' ).val(), 10 ) || 0;
			var data = {
				product_id: pid,
				quantity: qty
			};
			if ( variationId > 0 ) {
				data.variation_id = variationId;
				$form.find( 'select[name^="attribute_"], input[name^="attribute_"]' ).each( function() {
					var n = this.name;
					var v = $( this ).val();
					if ( n ) {
						data[ n ] = v || '';
					}
				} );
			}
			if ( $submitBtn.length ) {
				$submitBtn.prop( 'disabled', true );
			}
			$.post( ajaxUrl, data )
				.always( function() {
					if ( $submitBtn.length ) {
						$submitBtn.prop( 'disabled', false );
					}
				} )
				.done( function( res ) {
					blockFallbackUntil = Date.now() + 1800;
					clearTimeout( fallbackOpenTimer );
					if ( res && res.fragments ) {
						$( document.body ).trigger( 'added_to_cart', [ res.fragments, res.cart_hash || '', $submitBtn ] );
					} else {
						fetchDrawerData( pid, qty );
					}
				} );
			return false;
		}
	} );

	overlay.addEventListener( 'click', closeDrawer );
	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', closeDrawer );
	}
	document.addEventListener( 'keydown', function( e ) {
		if ( e.key === 'Escape' && ! drawer.hidden ) {
			closeDrawer();
		}
	} );

	window.addEventListener( 'beforeunload', function() {
		try {
			if ( isDrawerOpen ) {
				sessionStorage.setItem( RELOAD_SUPPRESS_KEY, '1' );
			} else {
				sessionStorage.removeItem( RELOAD_SUPPRESS_KEY );
			}
		} catch ( eStorage ) {}
	} );

	( function initFromState() {
		var suppressNextOpen = false;
		try {
			suppressNextOpen = sessionStorage.getItem( RELOAD_SUPPRESS_KEY ) === '1';
			if ( suppressNextOpen ) {
				sessionStorage.removeItem( RELOAD_SUPPRESS_KEY );
			}
		} catch ( eSuppress ) {}

		var hasCartAddedFlag = false;
		try {
			var initUrl = new URL( window.location.href );
			hasCartAddedFlag = initUrl.searchParams.has( 'gk_cart_added' ) || initUrl.searchParams.has( 'added-to-cart' );
			if ( initUrl.searchParams.has( 'gk_cart_added' ) ) {
				initUrl.searchParams.delete( 'gk_cart_added' );
			}
			if ( initUrl.searchParams.has( 'added-to-cart' ) ) {
				initUrl.searchParams.delete( 'added-to-cart' );
			}
			if ( hasCartAddedFlag ) {
				window.history.replaceState( {}, '', initUrl.pathname + ( initUrl.search ? initUrl.search : '' ) + initUrl.hash );
			}
		} catch ( eUrlInit ) {}

		if ( suppressNextOpen ) {
			sessionStorage.removeItem( 'gk_pending_add_to_cart' );
			return;
		}

		var pending = null;
		try {
			pending = JSON.parse( sessionStorage.getItem( 'gk_pending_add_to_cart' ) || 'null' );
		} catch ( err ) {}
		if ( hasCartAddedFlag && pending && pending.productId ) {
			sessionStorage.removeItem( 'gk_pending_add_to_cart' );
			renderLoadingState();
			openDrawer();
			fetchDrawerData( pending.productId, pending.quantity || 1 );
			return;
		}
		if ( hasCartAddedFlag && gkCartDrawer.lastAdded && gkCartDrawer.lastAdded.productId ) {
			renderLoadingState();
			openDrawer();
			fetchDrawerData( gkCartDrawer.lastAdded.productId, gkCartDrawer.lastAdded.quantity || 1 );
			return;
		}
		sessionStorage.removeItem( 'gk_pending_add_to_cart' );
	} )();

} )( jQuery, window, document );

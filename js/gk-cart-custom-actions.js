( function () {
	'use strict';

	function getConfig() {
		var root = document.getElementById( 'gk-cart-custom-static' ) || document.getElementById( 'gk-cart-custom-list' );
		var ajaxUrl = root ? root.getAttribute( 'data-gk-cart-ajax-url' ) : '';
		var nonce = root ? root.getAttribute( 'data-gk-cart-ajax-nonce' ) : '';
		if ( ! ajaxUrl && window.gkCartDrawer && window.gkCartDrawer.ajaxUrl ) {
			ajaxUrl = window.gkCartDrawer.ajaxUrl;
		}
		if ( ! nonce && window.gkCartDrawer && window.gkCartDrawer.nonce ) {
			nonce = window.gkCartDrawer.nonce;
		}
		return {
			ajaxUrl: ajaxUrl || '',
			nonce: nonce || ''
		};
	}

	function postAjax( payload ) {
		var cfg = getConfig();
		if ( ! cfg.ajaxUrl || ! cfg.nonce ) {
			return Promise.reject( new Error( 'missing ajax config' ) );
		}
		var params = new URLSearchParams();
		Object.keys( payload ).forEach( function ( key ) {
			params.append( key, String( payload[ key ] ) );
		} );
		params.append( 'nonce', cfg.nonce );
		return fetch( cfg.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			credentials: 'same-origin',
			body: params.toString()
		} ).then( function ( response ) {
			return response.json();
		} );
	}

	function reloadCart() {
		window.location.reload();
	}

	document.addEventListener( 'change', function ( e ) {
		var select = e.target;
		if ( ! select || !select.classList || !select.classList.contains( 'gk-cart-custom-card__qty-select' ) ) {
			return;
		}
		var itemKey = select.getAttribute( 'data-cart-item-key' ) || '';
		if ( ! itemKey ) {
			return;
		}
		var qty = parseInt( select.value || '1', 10 );
		if ( isNaN( qty ) || qty < 1 ) {
			qty = 1;
		}
		var card = select.closest( '.gk-cart-custom-card' );
		var hidden = card ? card.querySelector( '.gk-cart-custom-card__qty-input' ) : null;
		if ( hidden ) {
			hidden.value = String( qty );
		}
		select.disabled = true;
		postAjax( {
			action: 'gk_cart_drawer_update_qty',
			cart_item_key: itemKey,
			quantity: qty
		} )
			.then( reloadCart )
			.catch( reloadCart );
	} );

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target && e.target.closest ? e.target.closest( '.gk-cart-custom-card__remove' ) : null;
		if ( ! btn ) {
			return;
		}
		e.preventDefault();
		var itemKey = btn.getAttribute( 'data-cart-item-key' ) || '';
		var productId = parseInt( btn.getAttribute( 'data-product-id' ) || '0', 10 ) || 0;
		var removeUrl = btn.getAttribute( 'data-remove-url' ) || '';
		btn.disabled = true;
		if ( ! itemKey && ! productId ) {
			if ( removeUrl ) {
				window.location.href = removeUrl;
				return;
			}
			reloadCart();
			return;
		}
		postAjax( {
			action: 'gk_cart_drawer_remove_item',
			cart_item_key: itemKey,
			product_id: productId
		} )
			.then( reloadCart )
			.catch( function () {
				if ( removeUrl ) {
					window.location.href = removeUrl;
					return;
				}
				reloadCart();
			} );
	} );
} )();

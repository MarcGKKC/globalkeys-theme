( function () {
	'use strict';

	function detectPlatformKey( raw, fallback ) {
		var s = String( raw || '' ).toLowerCase();
		if ( /playstation|ps5|ps4|psn/.test( s ) ) return 'playstation';
		if ( /xbox/.test( s ) ) return 'xbox';
		if ( /nintendo|switch/.test( s ) ) return 'nintendo';
		if ( /steam|pc/.test( s ) ) return 'steam';
		return fallback || 'steam';
	}

	function splitTitleAndPlatform( title ) {
		var raw = String( title || '' ).trim();
		var m = raw.match( /\((Steam|PlayStation|Xbox|Nintendo)\)\s*$/i );
		if ( ! m ) {
			return { title: raw, platform: '' };
		}
		return {
			title: raw.replace( /\((Steam|PlayStation|Xbox|Nintendo)\)\s*$/i, '' ).trim(),
			platform: m[1]
		};
	}

	function dispatchInputEvents( el ) {
		if ( ! el ) return;
		try {
			el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			el.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} catch ( e ) {}
	}

	function buildQtySelect( qtyInput ) {
		var current = parseInt( qtyInput && qtyInput.value ? qtyInput.value : '1', 10 );
		if ( isNaN( current ) || current < 1 ) current = 1;
		var max = 10;
		if ( qtyInput && qtyInput.max && qtyInput.max !== '' ) {
			var parsedMax = parseInt( qtyInput.max, 10 );
			if ( ! isNaN( parsedMax ) && parsedMax >= current ) max = Math.min( 20, parsedMax );
		}
		var select = document.createElement( 'select' );
		select.className = 'gk-added-cart-drawer__qty gk-cart-custom-card__qty-select';
		select.setAttribute( 'aria-label', 'Quantity' );
		for ( var i = 1; i <= max; i++ ) {
			var opt = document.createElement( 'option' );
			opt.value = String( i );
			opt.textContent = String( i );
			if ( i === current ) opt.selected = true;
			select.appendChild( opt );
		}
		select.addEventListener( 'change', function () {
			if ( qtyInput ) {
				qtyInput.value = select.value;
				dispatchInputEvents( qtyInput );
			}
		} );
		return select;
	}

	function getCartItemKeyFromInput( qtyInput ) {
		var name = qtyInput && qtyInput.name ? String( qtyInput.name ) : '';
		var m = name.match( /^cart\[([^\]]+)\]\[qty\]$/ );
		return m && m[1] ? m[1] : '';
	}

	function getCartItemKeyFromRemoveLink( removeLink ) {
		var href = removeLink && removeLink.getAttribute ? String( removeLink.getAttribute( 'href' ) || '' ) : '';
		var m = href.match( /[?&]remove_item=([^&]+)/ );
		if ( ! m || ! m[1] ) return '';
		try {
			return decodeURIComponent( m[1] );
		} catch ( e ) {
			return m[1];
		}
	}

	function pickFirst( root, selectors ) {
		for ( var i = 0; i < selectors.length; i++ ) {
			var el = root.querySelector( selectors[ i ] );
			if ( el ) return el;
		}
		return null;
	}

	function buildCardFromRow( row, cfg ) {
		var thumbLink = pickFirst( row, [ '.product-thumbnail a', '.wc-block-cart-item__image a' ] );
		var thumbImg = pickFirst( row, [ '.product-thumbnail img', '.wc-block-cart-item__image img' ] );
		var nameLink = pickFirst( row, [ '.product-name a', '.wc-block-components-product-name' ] );
		var subtotal = pickFirst( row, [ '.product-subtotal', '.wc-block-cart-item__total', '.wc-block-components-product-price' ] );
		var qtyInput = pickFirst( row, [ '.product-quantity input.qty', '.product-quantity select.qty', '.product-quantity .qty', '.wc-block-components-quantity-selector__input' ] );
		var removeLink = pickFirst( row, [ '.product-remove a.remove', '.wc-block-cart-item__remove-link', 'button.wc-block-cart-item__remove-link' ] );
		if ( ! nameLink ) return null;

		var split = splitTitleAndPlatform( nameLink.textContent || '' );
		var key = detectPlatformKey( split.platform || split.title, 'steam' );
		var labels = cfg && cfg.labels ? cfg.labels : {};
		var icons = cfg && cfg.icons ? cfg.icons : {};
		var platformLabel = split.platform || labels[ key ] || 'Steam';
		var platformIcon = icons[ key ] || '';

		var card = document.createElement( 'article' );
		card.className = 'gk-cart-custom-card';

		var media = document.createElement( thumbLink ? 'a' : 'div' );
		media.className = 'gk-cart-custom-card__media';
		if ( thumbLink ) media.href = thumbLink.href;
		if ( thumbImg ) {
			var img = document.createElement( 'img' );
			img.className = 'gk-cart-custom-card__img';
			img.src = thumbImg.currentSrc || thumbImg.src || '';
			img.alt = thumbImg.alt || '';
			img.decoding = 'async';
			img.loading = 'lazy';
			media.appendChild( img );
		}
		card.appendChild( media );

		var main = document.createElement( 'div' );
		main.className = 'gk-cart-custom-card__main';
		var title = document.createElement( 'a' );
		title.className = 'gk-cart-custom-card__title';
		title.href = nameLink.href || '#';
		title.textContent = split.title || ( nameLink.textContent || '' ).trim();
		main.appendChild( title );

		var platform = document.createElement( 'div' );
		platform.className = 'gk-cart-custom-card__platform';
		if ( platformIcon ) {
			var pimg = document.createElement( 'img' );
			pimg.className = 'gk-cart-custom-card__platform-icon';
			pimg.src = platformIcon;
			pimg.alt = '';
			pimg.width = 18;
			pimg.height = 18;
			pimg.decoding = 'async';
			pimg.loading = 'lazy';
			platform.appendChild( pimg );
		}
		var ptxt = document.createElement( 'span' );
		ptxt.className = 'gk-cart-custom-card__platform-name';
		ptxt.textContent = platformLabel;
		platform.appendChild( ptxt );
		main.appendChild( platform );
		card.appendChild( main );

		var side = document.createElement( 'div' );
		side.className = 'gk-cart-custom-card__side';
		var price = document.createElement( 'div' );
		price.className = 'gk-cart-custom-card__price';
		price.innerHTML = subtotal ? subtotal.innerHTML : '';
		side.appendChild( price );

		var actions = document.createElement( 'div' );
		actions.className = 'gk-cart-custom-card__actions';
		var cartItemKey = getCartItemKeyFromInput( qtyInput ) || getCartItemKeyFromRemoveLink( removeLink );
		if ( qtyInput ) {
			var qtySelect = buildQtySelect( qtyInput );
			if ( cartItemKey ) qtySelect.setAttribute( 'data-cart-item-key', cartItemKey );
			actions.appendChild( qtySelect );
		}
		if ( removeLink ) {
			var removeBtn = document.createElement( 'button' );
			removeBtn.type = 'button';
			removeBtn.className = 'gk-cart-custom-card__remove';
			if ( cartItemKey ) removeBtn.setAttribute( 'data-cart-item-key', cartItemKey );
			if ( removeLink.getAttribute( 'href' ) ) removeBtn.setAttribute( 'data-remove-url', removeLink.getAttribute( 'href' ) );
			removeBtn.textContent = 'Remove item';
			removeBtn.addEventListener( 'click', function () {
				if ( typeof removeLink.click === 'function' ) removeLink.click();
			} );
			actions.appendChild( removeBtn );
		}
		side.appendChild( actions );
		card.appendChild( side );

		return card;
	}

	function renderCustomCards() {
		var productsRoot = document.querySelector( 'body.woocommerce-cart .gk-cart-page-split__products' );
		if ( ! productsRoot ) return false;
		if ( productsRoot.querySelector( '#gk-cart-custom-static' ) ) return true;
		var table = productsRoot.querySelector( 'table.shop_table.cart, table.wc-block-cart-items' );
		if ( ! table ) return false;
		var rows = table.querySelectorAll( 'tbody tr.cart_item, tbody tr.wc-block-cart-items__row' );
		if ( ! rows.length ) return false;
		var cfg = typeof gkCartCards === 'undefined' ? {} : gkCartCards;

		var wrap = productsRoot.querySelector( '#gk-cart-custom-list' );
		if ( ! wrap ) {
			wrap = document.createElement( 'div' );
			wrap.id = 'gk-cart-custom-list';
			wrap.className = 'gk-cart-custom-list';
			table.parentNode.insertBefore( wrap, table );
		}
		wrap.innerHTML = '';
		for ( var i = 0; i < rows.length; i++ ) {
			var card = buildCardFromRow( rows[ i ], cfg );
			if ( card ) wrap.appendChild( card );
		}
		return wrap.children.length > 0;
	}

	function scheduleRenders() {
		renderCustomCards();
		window.setTimeout( renderCustomCards, 300 );
		window.setTimeout( renderCustomCards, 900 );
		window.setTimeout( renderCustomCards, 1700 );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', scheduleRenders );
	} else {
		scheduleRenders();
	}
} )();

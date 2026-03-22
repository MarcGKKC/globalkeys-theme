/**
 * Header Pill Search: Lupen-Button öffnet Such-Overlay (Animation von rechts).
 * Live-Suche: Produkte werden beim Tippen automatisch angezeigt.
 *
 * @package globalkeys
 */
( function() {
	function init() {
		var container = document.querySelector( '.header-pill-container' );
		if ( ! container ) {
			return;
		}

		var trigger = container.querySelector( '.header-pill-search-trigger' );
		var overlay = document.getElementById( 'gk-pill-search-overlay' );
		var searchInput = overlay ? overlay.querySelector( '.header-pill-search-input' ) : null;
		var outer = container.parentNode;
		if ( ! outer || ! outer.classList.contains( 'header-pill-search-outer' ) ) {
			return;
		}
		var closeArea = outer.querySelector( '.header-pill-search-close-area' );
		var closeBtn = closeArea ? closeArea.querySelector( '.header-pill-search-close' ) : null;
		var dropdown = document.getElementById( 'gk-search-dropdown' );
		var searchOverlay = null;
		var searchPageAbortController = null;
		var drawerUserHasTyped = false;

		var ANIMATION_MS = 260;

		function openSearch() {
			var onSearchResultsPage = document.body.classList.contains( 'gk-search-results-page' );
			drawerUserHasTyped = false;
			if ( ! onSearchResultsPage ) {
				document.body.classList.add( 'gk-search-open' );
				searchOverlay = document.createElement( 'div' );
				searchOverlay.id = 'gk-search-overlay';
				searchOverlay.className = 'gk-search-overlay';
				searchOverlay.setAttribute( 'aria-hidden', 'true' );
				searchOverlay.addEventListener( 'click', function() {
					closeSearch();
				} );
				document.body.appendChild( searchOverlay );
			}
			if ( overlay ) {
				overlay.setAttribute( 'aria-hidden', 'false' );
				void overlay.offsetHeight;
			}
			container.classList.add( 'is-search-open' );
			outer.classList.add( 'is-search-open' );
			if ( closeArea ) {
				closeArea.setAttribute( 'aria-hidden', 'false' );
			}
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'true' );
			}
			if ( ! document.body.classList.contains( 'gk-search-results-page' ) && dropdown ) {
				dropdown.setAttribute( 'hidden', '' );
				dropdown.setAttribute( 'aria-hidden', 'true' );
			}
			if ( searchInput ) {
				setTimeout( function() {
					searchInput.focus( { preventScroll: true } );
					var len = ( searchInput.value || '' ).length;
					searchInput.setSelectionRange( len, len );
					if ( len > 0 ) {
						drawerUserHasTyped = true;
						updateDropdownContent();
					}
				}, ANIMATION_MS );
			}
		}

		function updateDropdownContent() {
			if ( ! dropdown || ! searchInput ) return;
			if ( document.body.classList.contains( 'gk-search-results-page' ) ) {
				dropdown.setAttribute( 'hidden', '' );
				dropdown.setAttribute( 'aria-hidden', 'true' );
				return;
			}
			var val = ( searchInput.value || '' ).trim();
			if ( val.length === 0 && ! drawerUserHasTyped ) {
				dropdown.setAttribute( 'hidden', '' );
				dropdown.setAttribute( 'aria-hidden', 'true' );
				return;
			}
			dropdown.removeAttribute( 'hidden' );
			dropdown.setAttribute( 'aria-hidden', 'false' );
			renderDropdownFromProductsData( val );
		}

		function renderDropdownFromProductsData( term ) {
			term = ( term || '' ).trim();
			var listEl = dropdown ? dropdown.querySelector( '.header-pill-search-dropdown-list' ) : null;
			var allLink = document.getElementById( 'gk-search-dropdown-all-link' );
			var baseUrl = dropdown ? ( dropdown.getAttribute( 'data-base-url' ) || '' ) : '';
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			if ( ! data || ! data.index || ! data.dropdown || ! data.names ) {
				if ( listEl ) listEl.innerHTML = '';
				if ( allLink ) allLink.textContent = ( typeof gkPillSearch !== 'undefined' && gkPillSearch.seeAll ) ? gkPillSearch.seeAll : 'See all results';
				return;
			}
			var matched = {};
			var termLower = term.toLowerCase();
			var names = data.names || {};
			if ( term.length === 0 ) {
				var ids = Object.keys( data.dropdown ).map( Number );
				ids.sort( function( a, b ) { return ( names[ a ] || '' ).localeCompare( names[ b ] || '' ); } );
				for ( var k = 0; k < ids.length; k++ ) {
					matched[ ids[ k ] ] = names[ ids[ k ] ] || '';
				}
			} else {
				for ( var i = 0; i < data.index.length; i++ ) {
					var p = data.index[ i ];
					if ( ( p.n && p.n.indexOf( termLower ) === 0 ) || ( p.s && p.s.indexOf( termLower ) === 0 ) ) {
						matched[ p.id ] = p.n || names[ p.id ] || '';
					}
				}
			}
			var ids = Object.keys( matched ).map( Number );
			ids.sort( function( a, b ) { return ( matched[ a ] || '' ).localeCompare( matched[ b ] || '' ); } );
			var total = ids.length;
			var displayIds = ids.slice( 0, 5 );
			var html = '';
			for ( var j = 0; j < displayIds.length; j++ ) {
				var item = data.dropdown[ displayIds[ j ] ];
				if ( ! item ) continue;
				html += '<li class="header-pill-search-dropdown-item">' +
					'<a href="' + ( item.url || '#' ).replace( /"/g, '&quot;' ) + '" class="header-pill-search-dropdown-link">' +
					'<span class="header-pill-search-dropdown-thumb">' +
					'<img src="' + ( item.image || '' ).replace( /"/g, '&quot;' ) + '" alt="" class="gk-search-dropdown-product-img" loading="lazy" />' +
					'</span>' +
					'<span class="header-pill-search-dropdown-info">' +
					'<span class="header-pill-search-dropdown-title">' + ( item.name || '' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ) + '</span>' +
					'<span class="header-pill-search-dropdown-platform">PC</span>' +
					'</span>' +
					'<span class="header-pill-search-dropdown-price">' + ( item.price || '' ).replace( /</g, '&lt;' ) + '</span>' +
					'</a></li>';
			}
			var noResultsTxt = ( typeof gkPillSearch !== 'undefined' && gkPillSearch.noResults ) ? gkPillSearch.noResults : 'Keine Treffer';
			var seeAllFmt = ( typeof gkPillSearch !== 'undefined' && gkPillSearch.seeAllResults ) ? gkPillSearch.seeAllResults : 'See all %d results';
			var seeAllTxt = ( typeof gkPillSearch !== 'undefined' && gkPillSearch.seeAll ) ? gkPillSearch.seeAll : 'See all results';
			if ( listEl ) listEl.innerHTML = html || '<li class="header-pill-search-dropdown-item"><span class="header-pill-search-dropdown-link" style="pointer-events:none;opacity:0.7;">' +
				( term.length > 0 ? noResultsTxt : '' ) + '</span></li>';
			if ( allLink ) {
				if ( term.length > 0 ) {
					var sep = baseUrl.indexOf( '?' ) >= 0 ? '&' : '?';
					allLink.href = baseUrl + sep + 's=' + encodeURIComponent( term );
				} else {
					allLink.href = baseUrl;
				}
				allLink.textContent = total > 0 ? seeAllFmt.replace( '%d', total ) : seeAllTxt;
			}
		}

		function onSearchInput() {
			if ( document.body.classList.contains( 'gk-search-results-page' ) ) {
				updateSearchResultsPage();
			} else {
				drawerUserHasTyped = true;
				updateDropdownContent();
			}
		}

		function setSearchResultsCount( count ) {
			var el = document.getElementById( 'gk-search-results-count' );
			if ( ! el ) return;
			var one = ( typeof gkPillSearch !== 'undefined' && gkPillSearch.resultsCountOne ) ? gkPillSearch.resultsCountOne : '1 result';
			var many = ( typeof gkPillSearch !== 'undefined' && gkPillSearch.resultsCountMany ) ? gkPillSearch.resultsCountMany : '%d results';
			el.textContent = count === 1 ? one : many.replace( '%d', count );
		}

		function sortProductIds( ids, data, sortType ) {
			var names = data.names || {};
			var prices = data.prices || {};
			var sortVal = ( document.getElementById( 'gk-search-sort' ) && document.getElementById( 'gk-search-sort' ).value ) ? document.getElementById( 'gk-search-sort' ).value : 'name-asc';
			if ( sortType ) sortVal = sortType;
			ids = ids.slice();
			var dates = data.dates || {};
			if ( sortVal === 'name-asc' ) {
				ids.sort( function( a, b ) { return ( names[ a ] || '' ).localeCompare( names[ b ] || '' ); } );
			} else if ( sortVal === 'name-desc' ) {
				ids.sort( function( a, b ) { return ( names[ b ] || '' ).localeCompare( names[ a ] || '' ); } );
			} else if ( sortVal === 'price-asc' ) {
				ids.sort( function( a, b ) { return ( prices[ a ] || 0 ) - ( prices[ b ] || 0 ); } );
			} else if ( sortVal === 'price-desc' ) {
				ids.sort( function( a, b ) { return ( prices[ b ] || 0 ) - ( prices[ a ] || 0 ); } );
			} else if ( sortVal === 'date-desc' ) {
				ids.sort( function( a, b ) { return ( dates[ b ] || 0 ) - ( dates[ a ] || 0 ); } );
			}
			return ids;
		}

		function getPriceFilterRange() {
			var minInput = document.getElementById( 'gk-price-min' );
			var maxInput = document.getElementById( 'gk-price-max' );
			var minVal = minInput ? parseInt( minInput.value, 10 ) : 0;
			var maxVal = maxInput ? parseInt( maxInput.value, 10 ) : 100;
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var sliderMax = ( data && data.priceMax != null ) ? Math.ceil( data.priceMax ) : 100;
			var isDefault = minVal === 0 && maxVal >= sliderMax;
			return { min: minVal, max: maxVal, isDefault: isDefault };
		}

		function filterIdsByPrice( ids, data ) {
			if ( ! ids || ! data || ! data.prices ) return ids;
			var range = getPriceFilterRange();
			if ( range.isDefault ) return ids;
			var out = [];
			for ( var i = 0; i < ids.length; i++ ) {
				var p = ( data.prices[ ids[ i ] ] != null ) ? parseFloat( data.prices[ ids[ i ] ], 10 ) : 0;
				if ( p >= range.min && p <= range.max ) {
					out.push( ids[ i ] );
				}
			}
			return out;
		}

		function getHideOutOfStock() {
			var cb = document.getElementById( 'gk-hide-out-of-stock' );
			return cb ? cb.checked : false;
		}

		function getSelectedDevices() {
			var out = [];
			var inputs = document.querySelectorAll( '.gk-filter-devices-content input.gk-filter-checkbox[data-device-slug]:checked' );
			for ( var i = 0; i < inputs.length; i++ ) {
				var slug = inputs[ i ].getAttribute( 'data-device-slug' );
				if ( slug ) out.push( slug );
			}
			return out;
		}

		function filterIdsByDevices( ids, data ) {
			if ( ! ids || ! data || ! data.productCats ) return ids;
			var selected = getSelectedDevices();
			if ( selected.length === 0 ) return ids;
			var out = [];
			for ( var i = 0; i < ids.length; i++ ) {
				var cats = data.productCats[ ids[ i ] ] || [];
				for ( var j = 0; j < selected.length; j++ ) {
					if ( cats.indexOf( selected[ j ] ) !== -1 ) {
						out.push( ids[ i ] );
						break;
					}
				}
			}
			return out;
		}

		function getSelectedProductTypes() {
			var out = [];
			var inputs = document.querySelectorAll( '.gk-filter-product-type-content input.gk-filter-checkbox[data-type-slug]:checked' );
			for ( var i = 0; i < inputs.length; i++ ) {
				var slug = inputs[ i ].getAttribute( 'data-type-slug' );
				if ( slug ) out.push( slug );
			}
			return out;
		}

		function filterIdsByProductTypes( ids, data ) {
			if ( ! ids || ! data || ! data.productProductTypes ) return ids;
			var selected = getSelectedProductTypes();
			if ( selected.length === 0 ) return ids;
			var out = [];
			for ( var i = 0; i < ids.length; i++ ) {
				var pt = data.productProductTypes[ ids[ i ] ];
				if ( pt && selected.indexOf( pt ) !== -1 ) {
					out.push( ids[ i ] );
				}
			}
			return out;
		}

		function getSelectedCategories() {
			var out = [];
			var inputs = document.querySelectorAll( '.gk-filter-categories-content input.gk-filter-checkbox[data-cat-slug]:checked' );
			for ( var i = 0; i < inputs.length; i++ ) {
				var slug = inputs[ i ].getAttribute( 'data-cat-slug' );
				if ( slug ) out.push( slug );
			}
			return out;
		}

		function filterIdsByCategories( ids, data ) {
			if ( ! ids || ! data || ! data.productCategoryTags ) return ids;
			var selected = getSelectedCategories();
			if ( selected.length === 0 ) return ids;
			var out = [];
			for ( var i = 0; i < ids.length; i++ ) {
				var tags = data.productCategoryTags[ ids[ i ] ] || [];
				for ( var j = 0; j < selected.length; j++ ) {
					if ( tags.indexOf( selected[ j ] ) !== -1 ) {
						out.push( ids[ i ] );
						break;
					}
				}
			}
			return out;
		}

		function getSelectedGamepads() {
			var out = [];
			var inputs = document.querySelectorAll( '.gk-filter-gamepads-content input.gk-filter-checkbox[data-gamepad-slug]:checked' );
			for ( var i = 0; i < inputs.length; i++ ) {
				var slug = inputs[ i ].getAttribute( 'data-gamepad-slug' );
				if ( slug ) out.push( slug );
			}
			return out;
		}

		function filterIdsByGamepads( ids, data ) {
			if ( ! ids || ! data || ! data.productGamepads ) return ids;
			var selected = getSelectedGamepads();
			if ( selected.length === 0 ) return ids;
			var out = [];
			for ( var i = 0; i < ids.length; i++ ) {
				var pads = data.productGamepads[ ids[ i ] ] || [];
				for ( var j = 0; j < selected.length; j++ ) {
					if ( pads.indexOf( selected[ j ] ) !== -1 ) {
						out.push( ids[ i ] );
						break;
					}
				}
			}
			return out;
		}

		function getSelectedGameModes() {
			var out = [];
			var inputs = document.querySelectorAll( '.gk-filter-game-modes-content input.gk-filter-checkbox[data-mode-slug]:checked' );
			for ( var i = 0; i < inputs.length; i++ ) {
				var slug = inputs[ i ].getAttribute( 'data-mode-slug' );
				if ( slug ) out.push( slug );
			}
			return out;
		}

		function filterIdsByGameModes( ids, data ) {
			if ( ! ids || ! data || ! data.productGameModes ) return ids;
			var selected = getSelectedGameModes();
			if ( selected.length === 0 ) return ids;
			var out = [];
			for ( var i = 0; i < ids.length; i++ ) {
				var modes = data.productGameModes[ ids[ i ] ] || [];
				for ( var j = 0; j < selected.length; j++ ) {
					if ( modes.indexOf( selected[ j ] ) !== -1 ) {
						out.push( ids[ i ] );
						break;
					}
				}
			}
			return out;
		}

		function filterIdsByStock( ids, data ) {
			if ( ! ids || ! data || ! data.inStock ) return ids;
			if ( ! getHideOutOfStock() ) return ids;
			var out = [];
			for ( var i = 0; i < ids.length; i++ ) {
				if ( data.inStock[ ids[ i ] ] ) {
					out.push( ids[ i ] );
				}
			}
			return out;
		}

		function updateSearchResultsPage() {
			if ( ! document.body.classList.contains( 'gk-search-results-page' ) ) return;
			var val = searchInput ? ( searchInput.value || '' ).trim() : '';
			var gridWrap = document.getElementById( 'gk-search-results-grid' );
			if ( ! gridWrap ) return;
			var listEl = gridWrap.querySelector( '.gk-featured-products' );
			var noResultsEl = document.getElementById( 'gk-search-no-results' );
			if ( val.length === 0 ) {
				if ( searchPageAbortController ) {
					searchPageAbortController.abort();
					searchPageAbortController = null;
				}
				var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
				if ( data && data.cards && data.names ) {
					var ids = Object.keys( data.cards ).map( Number );
					ids = filterIdsByPrice( ids, data );
					ids = filterIdsByDevices( ids, data );
					ids = filterIdsByProductTypes( ids, data );
					ids = filterIdsByGameModes( ids, data );
					ids = filterIdsByCategories( ids, data );
					ids = filterIdsByGamepads( ids, data );
					ids = filterIdsByStock( ids, data );
					ids = sortProductIds( ids, data );
					var html = '';
					for ( var k = 0; k < ids.length; k++ ) {
						html += data.cards[ ids[ k ] ] || '';
					}
					if ( listEl ) listEl.innerHTML = html;
					if ( noResultsEl ) noResultsEl.style.display = 'none';
					setSearchResultsCount( ids.length );
					var paginationEl = document.getElementById( 'gk-search-pagination' );
					if ( paginationEl ) paginationEl.style.display = 'none';
					if ( typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.ready ) {
						jQuery( document.body ).trigger( 'gk_search_results_updated' );
					}
				} else {
					if ( listEl ) listEl.innerHTML = '';
					if ( noResultsEl ) noResultsEl.style.display = 'block';
					setSearchResultsCount( 0 );
					var paginationEl = document.getElementById( 'gk-search-pagination' );
					if ( paginationEl ) paginationEl.style.display = 'none';
				}
				return;
			}
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			if ( data && data.index && data.cards ) {
				var term = val.toLowerCase();
				var matched = {};
				var names = data.names || {};
				for ( var i = 0; i < data.index.length; i++ ) {
					var p = data.index[ i ];
					if ( ( p.n && p.n.indexOf( term ) === 0 ) || ( p.s && p.s.indexOf( term ) === 0 ) ) {
						matched[ p.id ] = p.n || names[ p.id ] || '';
					}
				}
				var ids = Object.keys( matched ).map( Number );
				ids = filterIdsByPrice( ids, data );
				ids = filterIdsByDevices( ids, data );
				ids = filterIdsByProductTypes( ids, data );
				ids = filterIdsByGameModes( ids, data );
				ids = filterIdsByCategories( ids, data );
				ids = filterIdsByGamepads( ids, data );
				ids = filterIdsByStock( ids, data );
				var namesForSort = {};
				for ( var mid in matched ) { namesForSort[ mid ] = matched[ mid ]; }
				ids = sortProductIds( ids, { names: namesForSort, prices: ( data.prices || {} ) } );
				var html = '';
				for ( var j = 0; j < ids.length; j++ ) {
					html += data.cards[ ids[ j ] ] || '';
				}
				if ( listEl ) listEl.innerHTML = html;
				if ( noResultsEl ) noResultsEl.style.display = ids.length ? 'none' : 'block';
				setSearchResultsCount( ids.length );
				var paginationEl = document.getElementById( 'gk-search-pagination' );
				if ( paginationEl ) paginationEl.style.display = 'none';
				if ( typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.ready ) {
					jQuery( document.body ).trigger( 'gk_search_results_updated' );
				}
				return;
			}
			if ( typeof gkPillSearch === 'undefined' || ! gkPillSearch.ajaxUrl || ! gkPillSearch.nonce ) return;
			if ( searchPageAbortController ) {
				searchPageAbortController.abort();
			}
			searchPageAbortController = new AbortController();
			var formData = new FormData();
			formData.append( 'action', 'gk_search_results_html' );
			formData.append( 'nonce', gkPillSearch.nonce );
			formData.append( 's', val );
			var priceRange = getPriceFilterRange();
			if ( ! priceRange.isDefault ) {
				formData.append( 'price_min', priceRange.min );
				formData.append( 'price_max', priceRange.max );
			}
			if ( getHideOutOfStock() ) {
				formData.append( 'hide_out_of_stock', 1 );
			}
			var devices = getSelectedDevices();
			for ( var d = 0; d < devices.length; d++ ) {
				formData.append( 'device[]', devices[ d ] );
			}
			var productTypes = getSelectedProductTypes();
			for ( var pt = 0; pt < productTypes.length; pt++ ) {
				formData.append( 'product_type[]', productTypes[ pt ] );
			}
			var categories = getSelectedCategories();
			for ( var c = 0; c < categories.length; c++ ) {
				formData.append( 'category[]', categories[ c ] );
			}
			var gamepads = getSelectedGamepads();
			for ( var gp = 0; gp < gamepads.length; gp++ ) {
				formData.append( 'gamepad[]', gamepads[ gp ] );
			}
			var gameModes = getSelectedGameModes();
			for ( var g = 0; g < gameModes.length; g++ ) {
				formData.append( 'game_mode[]', gameModes[ g ] );
			}
			fetch( gkPillSearch.ajaxUrl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin',
				signal: searchPageAbortController.signal
			} )
				.then( function( r ) { return r.json(); } )
				.then( function( res ) {
					if ( ( searchInput ? ( searchInput.value || '' ).trim() : '' ) !== val ) return;
					var html = ( res && res.data && res.data.html ) ? res.data.html : '';
					var noResults = res && res.data && res.data.noResults;
					var total = ( res && res.data && res.data.total !== undefined ) ? res.data.total : 0;
					if ( listEl ) listEl.innerHTML = html || '';
					if ( noResultsEl ) noResultsEl.style.display = noResults ? 'block' : 'none';
					setSearchResultsCount( total );
					var paginationEl = document.getElementById( 'gk-search-pagination' );
					if ( paginationEl ) paginationEl.style.display = 'none';
					if ( typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.ready ) {
						jQuery( document.body ).trigger( 'gk_search_results_updated' );
					}
				} )
				.catch( function( err ) {
					if ( err && err.name === 'AbortError' ) return;
					if ( ( searchInput ? ( searchInput.value || '' ).trim() : '' ) === val && listEl ) {
						listEl.innerHTML = '';
						setSearchResultsCount( 0 );
					}
				} );
		}

		function forceClose() {
			document.body.classList.remove( 'gk-search-open' );
			if ( searchOverlay && searchOverlay.parentNode ) {
				searchOverlay.parentNode.removeChild( searchOverlay );
			}
			searchOverlay = null;
			if ( dropdown ) {
				dropdown.setAttribute( 'hidden', '' );
				dropdown.setAttribute( 'aria-hidden', 'true' );
			}
			container.classList.remove( 'is-search-open' );
			outer.classList.remove( 'is-search-open' );
			if ( closeArea ) {
				closeArea.setAttribute( 'aria-hidden', 'true' );
			}
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			}
			if ( overlay ) {
				overlay.setAttribute( 'aria-hidden', 'true' );
			}
		}

		function closeSearch() {
			try {
				document.body.classList.remove( 'gk-search-open' );
				if ( searchOverlay && searchOverlay.parentNode ) {
					searchOverlay.parentNode.removeChild( searchOverlay );
				}
				searchOverlay = null;
				if ( dropdown ) {
					dropdown.setAttribute( 'hidden', '' );
					dropdown.setAttribute( 'aria-hidden', 'true' );
				}
				container.classList.remove( 'is-search-open' );
				outer.classList.remove( 'is-search-open' );
				if ( closeArea ) {
					closeArea.setAttribute( 'aria-hidden', 'true' );
				}
				if ( trigger ) {
					trigger.setAttribute( 'aria-expanded', 'false' );
				}
				if ( overlay ) {
					overlay.setAttribute( 'aria-hidden', 'true' );
				}
			} catch ( err ) {
				forceClose();
			}
		}

		if ( trigger ) {
			trigger.addEventListener( 'click', function( e ) {
				e.preventDefault();
				if ( container.classList.contains( 'is-search-open' ) ) {
					closeSearch();
				} else {
					openSearch();
				}
			} );
		}

		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				e.stopPropagation();
				closeSearch();
			} );
		}

		if ( searchInput ) {
			searchInput.addEventListener( 'input', onSearchInput );
			searchInput.addEventListener( 'keyup', onSearchInput );
		}

		var sortSelect = document.getElementById( 'gk-search-sort' );
		if ( sortSelect ) {
			sortSelect.addEventListener( 'change', onSearchInput );
		}

		document.addEventListener( 'gk_search_filters_changed', updateSearchResultsPage );
		document.addEventListener( 'gk_search_filters_changed', updateActiveFiltersBar );

		function updateActiveFiltersBar() {
			var bar = document.getElementById( 'gk-active-filters-bar' );
			var chipsWrap = bar ? bar.querySelector( '.gk-active-filters-chips' ) : null;
			var clearBtn = document.getElementById( 'gk-active-filters-clear-all' );
			if ( ! bar || ! chipsWrap ) return;
			var chips = [];
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var priceRange = getPriceFilterRange();
			if ( ! priceRange.isDefault ) {
				var priceLabel = ( typeof gkPillSearch !== 'undefined' && gkPillSearch.priceBetween ) ? gkPillSearch.priceBetween : 'Price between %1$s € and %2$s €';
				chips.push( { type: 'price', label: ( priceLabel.replace( '%1$s', priceRange.min ).replace( '%2$s', priceRange.max ) ) } );
			}
			if ( getHideOutOfStock() ) {
				chips.push( { type: 'hide_out_of_stock', label: ( typeof gkPillSearch !== 'undefined' && gkPillSearch.hideOutOfStock ) ? gkPillSearch.hideOutOfStock : 'Hide out of stock items' } );
			}
			var devices = getSelectedDevices();
			var deviceOpts = ( data && data.deviceOptions ) ? data.deviceOptions : {};
			for ( var d = 0; d < devices.length; d++ ) {
				chips.push( { type: 'device', value: devices[ d ], label: deviceOpts[ devices[ d ] ] || devices[ d ] } );
			}
			var productTypes = getSelectedProductTypes();
			var ptOpts = ( data && data.productTypeOptions ) ? data.productTypeOptions : {};
			for ( var pt = 0; pt < productTypes.length; pt++ ) {
				chips.push( { type: 'product_type', value: productTypes[ pt ], label: ptOpts[ productTypes[ pt ] ] || productTypes[ pt ] } );
			}
			var gameModes = getSelectedGameModes();
			var gmOpts = ( data && data.gameModeOptions ) ? data.gameModeOptions : {};
			for ( var gm = 0; gm < gameModes.length; gm++ ) {
				chips.push( { type: 'game_mode', value: gameModes[ gm ], label: gmOpts[ gameModes[ gm ] ] || gameModes[ gm ] } );
			}
			var categories = getSelectedCategories();
			var catOpts = ( data && data.categoryFilterOptions ) ? data.categoryFilterOptions : {};
			for ( var c = 0; c < categories.length; c++ ) {
				var co = catOpts[ categories[ c ] ];
				chips.push( { type: 'category', value: categories[ c ], label: ( co && co.label ) ? co.label : categories[ c ] } );
			}
			var gamepads = getSelectedGamepads();
			var gpOpts = ( data && data.gamepadOptions ) ? data.gamepadOptions : {};
			for ( var gp = 0; gp < gamepads.length; gp++ ) {
				chips.push( { type: 'gamepad', value: gamepads[ gp ], label: gpOpts[ gamepads[ gp ] ] || gamepads[ gp ] } );
			}
			if ( chips.length === 0 ) {
				bar.setAttribute( 'aria-hidden', 'true' );
				chipsWrap.innerHTML = '';
				var badge = document.getElementById( 'gk-search-filters-count-badge' );
				if ( badge ) {
					badge.setAttribute( 'aria-hidden', 'true' );
				}
				return;
			}
			bar.removeAttribute( 'aria-hidden' );
			var badge = document.getElementById( 'gk-search-filters-count-badge' );
			if ( badge ) {
				badge.textContent = String( chips.length );
				badge.removeAttribute( 'aria-hidden' );
			}
			chipsWrap.innerHTML = '';
			for ( var i = 0; i < chips.length; i++ ) {
				var chip = chips[ i ];
				var el = document.createElement( 'span' );
				el.className = 'gk-active-filters-chip';
				el.setAttribute( 'data-type', chip.type );
				if ( chip.value ) el.setAttribute( 'data-value', chip.value );
				el.appendChild( document.createTextNode( chip.label ) );
				var removeBtn = document.createElement( 'button' );
				removeBtn.type = 'button';
				removeBtn.className = 'gk-active-filters-chip-remove';
				removeBtn.setAttribute( 'aria-label', ( typeof gkPillSearch !== 'undefined' && gkPillSearch.removeFilter ) ? ( gkPillSearch.removeFilter + ': ' + chip.label ) : ( 'Remove ' + chip.label ) );
				removeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
				( function( c ) {
					removeBtn.addEventListener( 'click', function() {
						removeActiveFilter( c );
						updateActiveFiltersBar();
						document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
					} );
				} )( chip );
				el.appendChild( removeBtn );
				chipsWrap.appendChild( el );
			}
			if ( clearBtn ) {
				clearBtn.onclick = function() {
					clearAllActiveFilters();
					updateActiveFiltersBar();
					document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
				};
			}
		}

		function removeActiveFilter( chip ) {
			if ( chip.type === 'price' ) {
				var priceReset = document.getElementById( 'gk-price-reset' );
				if ( priceReset ) priceReset.click();
			} else if ( chip.type === 'hide_out_of_stock' ) {
				var cb = document.getElementById( 'gk-hide-out-of-stock' );
				if ( cb ) cb.checked = false;
			} else if ( chip.type === 'device' ) {
				var inp = document.querySelector( '.gk-filter-devices-content input[data-device-slug="' + chip.value + '"]' );
				if ( inp ) inp.checked = false;
			} else if ( chip.type === 'product_type' ) {
				var inp = document.querySelector( '.gk-filter-product-type-content input[data-type-slug="' + chip.value + '"]' );
				if ( inp ) inp.checked = false;
			} else if ( chip.type === 'game_mode' ) {
				var inp = document.querySelector( '.gk-filter-game-modes-content input[data-mode-slug="' + chip.value + '"]' );
				if ( inp ) inp.checked = false;
			} else if ( chip.type === 'category' ) {
				var inp = document.querySelector( '.gk-filter-categories-content input[data-cat-slug="' + chip.value + '"]' );
				if ( inp ) inp.checked = false;
			} else if ( chip.type === 'gamepad' ) {
				var inp = document.querySelector( '.gk-filter-gamepads-content input[data-gamepad-slug="' + chip.value + '"]' );
				if ( inp ) inp.checked = false;
			}
		}

		function clearAllActiveFilters() {
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var sliderMax = ( data && data.priceMax != null ) ? Math.ceil( data.priceMax ) : 100;
			var minIn = document.getElementById( 'gk-price-min' );
			var maxIn = document.getElementById( 'gk-price-max' );
			if ( minIn ) minIn.value = 0;
			if ( maxIn ) maxIn.value = sliderMax;
			var priceReset = document.getElementById( 'gk-price-reset' );
			if ( priceReset ) priceReset.click();
			var cb = document.getElementById( 'gk-hide-out-of-stock' );
			if ( cb ) cb.checked = false;
			var checkboxes = document.querySelectorAll( '.gk-filter-devices-content input:checked, .gk-filter-product-type-content input:checked, .gk-filter-game-modes-content input:checked, .gk-filter-categories-content input:checked, .gk-filter-gamepads-content input:checked' );
			for ( var i = 0; i < checkboxes.length; i++ ) {
				checkboxes[ i ].checked = false;
			}
			[ 'gk-devices-reset', 'gk-product-type-reset', 'gk-game-modes-reset', 'gk-categories-reset', 'gk-gamepads-reset' ].forEach( function( id ) {
				var btn = document.getElementById( id );
				if ( btn ) btn.classList.remove( 'is-visible' );
			} );
		}

		document.addEventListener( 'keydown', function( e ) {
			if ( e.key === 'Escape' && container.classList.contains( 'is-search-open' ) ) {
				closeSearch();
			}
		} );

		document.addEventListener( 'click', function( e ) {
			if ( ! container.classList.contains( 'is-search-open' ) ) {
				return;
			}
			if ( document.body.classList.contains( 'gk-search-results-page' ) ) {
				return;
			}
			if ( closeArea && closeArea.contains( e.target ) ) {
				return;
			}
			if ( searchOverlay && e.target === searchOverlay ) {
				closeSearch();
				return;
			}
			if ( ! outer.contains( e.target ) ) {
				closeSearch();
			}
		} );
	}

	function initFilterSidebar() {
		var layout = document.getElementById( 'gk-search-layout' );
		var sidebar = document.getElementById( 'gk-search-filter-sidebar' );
		var toggleBtn = document.querySelector( '.gk-search-filters-toggle' );
		var closeBtn = document.querySelector( '.gk-search-filter-sidebar-close' );
		var footer = document.getElementById( 'colophon' );
		if ( ! layout || ! toggleBtn || ! sidebar ) {
			return;
		}
		function updateSidebarHeight() {
			if ( ! layout.classList.contains( 'is-sidebar-open' ) ) {
				sidebar.style.height = '';
				return;
			}
			if ( footer ) {
				var footerTop = footer.getBoundingClientRect().top;
				var vh = window.innerHeight;
				sidebar.style.height = Math.min( vh, Math.max( 0, footerTop ) ) + 'px';
			} else {
				sidebar.style.height = window.innerHeight + 'px';
			}
		}

		function setSidebarOpen( isOpen ) {
			if ( isOpen ) {
				layout.classList.add( 'is-sidebar-open' );
				document.body.classList.add( 'gk-filter-sidebar-open' );
				updateSidebarHeight();
			} else {
				toggleBtn.blur();
				function clearHeightAfterClose( e ) {
					if ( e && e.propertyName !== 'transform' ) return;
					sidebar.removeEventListener( 'transitionend', clearHeightAfterClose );
					sidebar.style.height = '';
				}
				sidebar.addEventListener( 'transitionend', clearHeightAfterClose );
				setTimeout( function() {
					if ( ! layout.classList.contains( 'is-sidebar-open' ) ) {
						sidebar.removeEventListener( 'transitionend', clearHeightAfterClose );
						sidebar.style.height = '';
					}
				}, 280 );
				layout.classList.remove( 'is-sidebar-open' );
				document.body.classList.remove( 'gk-filter-sidebar-open' );
			}
			toggleBtn.setAttribute( 'aria-expanded', isOpen );
		}
		function toggleSidebar() {
			var isOpen = ! layout.classList.contains( 'is-sidebar-open' );
			setSidebarOpen( isOpen );
		}
		toggleBtn.addEventListener( 'click', toggleSidebar );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function() {
				setSidebarOpen( false );
			} );
		}
		var scrollTimeout;
		window.addEventListener( 'scroll', function() {
			if ( scrollTimeout ) {
				window.cancelAnimationFrame( scrollTimeout );
			}
			scrollTimeout = window.requestAnimationFrame( function() {
				updateSidebarHeight();
				scrollTimeout = null;
			} );
		}, { passive: true } );
		window.addEventListener( 'resize', updateSidebarHeight );

		function initPriceSlider() {
			var minInput = document.getElementById( 'gk-price-min' );
			var maxInput = document.getElementById( 'gk-price-max' );
			var fillEl = document.getElementById( 'gk-price-fill' );
			var valueEl = document.getElementById( 'gk-price-value' );
			var resetBtn = document.getElementById( 'gk-price-reset' );
			if ( ! minInput || ! maxInput || ! fillEl || ! valueEl ) return;
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var sliderMax = ( data && data.priceMax != null ) ? Math.ceil( data.priceMax ) : 100;
			if ( sliderMax < 1 ) sliderMax = 1;
			minInput.min = 0;
			minInput.max = sliderMax;
			minInput.step = 1;
			minInput.value = 0;
			maxInput.min = 0;
			maxInput.max = sliderMax;
			maxInput.step = 1;
			maxInput.value = sliderMax;
			function isDefault() {
				return parseInt( minInput.value, 10 ) === 0 && parseInt( maxInput.value, 10 ) >= sliderMax;
			}
			function updateResetVisibility() {
				if ( resetBtn ) {
					resetBtn.classList.toggle( 'is-visible', ! isDefault() );
				}
			}
			function updatePriceDisplay() {
				var minVal = parseInt( minInput.value, 10 );
				var maxVal = parseInt( maxInput.value, 10 );
				if ( minVal > maxVal ) {
					var t = minVal;
					minVal = maxVal;
					maxVal = t;
					minInput.value = minVal;
					maxInput.value = maxVal;
				}
				var pctMin = sliderMax > 0 ? ( minVal / sliderMax ) * 100 : 0;
				var pctMax = sliderMax > 0 ? ( maxVal / sliderMax ) * 100 : 100;
				fillEl.style.left = pctMin + '%';
				fillEl.style.width = ( pctMax - pctMin ) + '%';
				if ( maxVal >= sliderMax ) {
					valueEl.textContent = 'Between ' + minVal + ' € and MAX';
				} else {
					valueEl.textContent = 'Between ' + minVal + ' € and ' + maxVal + ' €';
				}
				updateResetVisibility();
				document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
			}
			minInput.addEventListener( 'input', updatePriceDisplay );
			maxInput.addEventListener( 'input', updatePriceDisplay );
			if ( resetBtn ) {
				resetBtn.addEventListener( 'click', function() {
					minInput.value = 0;
					maxInput.value = sliderMax;
					updatePriceDisplay();
				} );
			}
			updatePriceDisplay();
		}
		initPriceSlider();

		function initPreferencesFilter() {
			var wrap = document.querySelector( '.gk-filter-preferences' );
			var toggleBtn = document.getElementById( 'gk-preferences-toggle' );
			var content = document.getElementById( 'gk-preferences-content' );
			var checkbox = document.getElementById( 'gk-hide-out-of-stock' );
			var resetBtn = document.getElementById( 'gk-preferences-reset' );
			if ( ! wrap || ! toggleBtn || ! content || ! checkbox ) return;
			function updateResetVisibility() {
				if ( resetBtn ) {
					resetBtn.classList.toggle( 'is-visible', checkbox.checked );
				}
			}
			function onFilterChange() {
				updateResetVisibility();
				document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
			}
			toggleBtn.addEventListener( 'click', function() {
				var collapsed = wrap.classList.toggle( 'is-collapsed' );
				toggleBtn.setAttribute( 'aria-expanded', ! collapsed );
			} );
			checkbox.addEventListener( 'change', onFilterChange );
			if ( resetBtn ) {
				resetBtn.addEventListener( 'click', function() {
					checkbox.checked = false;
					onFilterChange();
				} );
			}
			updateResetVisibility();
		}
		initPreferencesFilter();

		function initDevicesFilter() {
			var wrap = document.querySelector( '.gk-filter-devices' );
			var toggleBtn = document.getElementById( 'gk-devices-toggle' );
			var content = document.getElementById( 'gk-devices-content' );
			var resetBtn = document.getElementById( 'gk-devices-reset' );
			if ( ! wrap || ! toggleBtn || ! content ) return;
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var options = ( data && data.deviceOptions ) ? data.deviceOptions : {};
			var slugs = Object.keys( options );
			content.innerHTML = '';
			for ( var i = 0; i < slugs.length; i++ ) {
				var slug = slugs[ i ];
				var name = options[ slug ] || slug;
				var label = document.createElement( 'label' );
				label.className = 'gk-filter-checkbox-label';
				var input = document.createElement( 'input' );
				input.type = 'checkbox';
				input.className = 'gk-filter-checkbox';
				input.setAttribute( 'data-device-slug', slug );
				input.setAttribute( 'aria-label', name );
				var span = document.createElement( 'span' );
				span.className = 'gk-filter-checkbox-text';
				span.textContent = name;
				label.appendChild( input );
				label.appendChild( span );
				content.appendChild( label );
			}
			function updateResetVisibility() {
				var checked = content.querySelectorAll( 'input:checked' ).length;
				if ( resetBtn ) {
					resetBtn.classList.toggle( 'is-visible', checked > 0 );
				}
			}
			function onFilterChange() {
				updateResetVisibility();
				document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
			}
			content.addEventListener( 'change', onFilterChange );
			toggleBtn.addEventListener( 'click', function() {
				var collapsed = wrap.classList.toggle( 'is-collapsed' );
				toggleBtn.setAttribute( 'aria-expanded', ! collapsed );
			} );
			if ( resetBtn ) {
				resetBtn.addEventListener( 'click', function() {
					var inputs = content.querySelectorAll( 'input' );
					for ( var k = 0; k < inputs.length; k++ ) {
						inputs[ k ].checked = false;
					}
					onFilterChange();
				} );
			}
			updateResetVisibility();
		}
		initDevicesFilter();

		function initProductTypeFilter() {
			var wrap = document.querySelector( '.gk-filter-product-type' );
			var toggleBtn = document.getElementById( 'gk-product-type-toggle' );
			var content = document.getElementById( 'gk-product-type-content' );
			var resetBtn = document.getElementById( 'gk-product-type-reset' );
			if ( ! wrap || ! toggleBtn || ! content ) return;
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var options = ( data && data.productTypeOptions ) ? data.productTypeOptions : {};
			var slugs = Object.keys( options );
			content.innerHTML = '';
			for ( var i = 0; i < slugs.length; i++ ) {
				var slug = slugs[ i ];
				var name = options[ slug ] || slug;
				var label = document.createElement( 'label' );
				label.className = 'gk-filter-checkbox-label';
				var input = document.createElement( 'input' );
				input.type = 'checkbox';
				input.className = 'gk-filter-checkbox';
				input.setAttribute( 'data-type-slug', slug );
				input.setAttribute( 'aria-label', name );
				var span = document.createElement( 'span' );
				span.className = 'gk-filter-checkbox-text';
				span.textContent = name;
				label.appendChild( input );
				label.appendChild( span );
				content.appendChild( label );
			}
			function updateResetVisibility() {
				var checked = content.querySelectorAll( 'input:checked' ).length;
				if ( resetBtn ) {
					resetBtn.classList.toggle( 'is-visible', checked > 0 );
				}
			}
			function onFilterChange() {
				updateResetVisibility();
				document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
			}
			content.addEventListener( 'change', onFilterChange );
			toggleBtn.addEventListener( 'click', function() {
				var collapsed = wrap.classList.toggle( 'is-collapsed' );
				toggleBtn.setAttribute( 'aria-expanded', ! collapsed );
			} );
			if ( resetBtn ) {
				resetBtn.addEventListener( 'click', function() {
					var inputs = content.querySelectorAll( 'input' );
					for ( var k = 0; k < inputs.length; k++ ) {
						inputs[ k ].checked = false;
					}
					onFilterChange();
				} );
			}
			updateResetVisibility();
		}
		initProductTypeFilter();

		function initGameModesFilter() {
			var wrap = document.querySelector( '.gk-filter-game-modes' );
			var toggleBtn = document.getElementById( 'gk-game-modes-toggle' );
			var content = document.getElementById( 'gk-game-modes-content' );
			var resetBtn = document.getElementById( 'gk-game-modes-reset' );
			if ( ! wrap || ! toggleBtn || ! content ) return;
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var options = ( data && data.gameModeOptions ) ? data.gameModeOptions : {};
			var slugs = Object.keys( options );
			content.innerHTML = '';
			for ( var i = 0; i < slugs.length; i++ ) {
				var slug = slugs[ i ];
				var name = options[ slug ] || slug;
				var label = document.createElement( 'label' );
				label.className = 'gk-filter-checkbox-label';
				var input = document.createElement( 'input' );
				input.type = 'checkbox';
				input.className = 'gk-filter-checkbox';
				input.setAttribute( 'data-mode-slug', slug );
				input.setAttribute( 'aria-label', name );
				var span = document.createElement( 'span' );
				span.className = 'gk-filter-checkbox-text';
				span.textContent = name;
				label.appendChild( input );
				label.appendChild( span );
				content.appendChild( label );
			}
			function updateResetVisibility() {
				var checked = content.querySelectorAll( 'input:checked' ).length;
				if ( resetBtn ) {
					resetBtn.classList.toggle( 'is-visible', checked > 0 );
				}
			}
			function onFilterChange() {
				updateResetVisibility();
				document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
			}
			content.addEventListener( 'change', onFilterChange );
			toggleBtn.addEventListener( 'click', function() {
				var collapsed = wrap.classList.toggle( 'is-collapsed' );
				toggleBtn.setAttribute( 'aria-expanded', ! collapsed );
			} );
			if ( resetBtn ) {
				resetBtn.addEventListener( 'click', function() {
					var inputs = content.querySelectorAll( 'input' );
					for ( var k = 0; k < inputs.length; k++ ) {
						inputs[ k ].checked = false;
					}
					onFilterChange();
				} );
			}
			updateResetVisibility();
		}
		initGameModesFilter();

		function initCategoriesFilter() {
			var wrap = document.querySelector( '.gk-filter-categories' );
			var toggleBtn = document.getElementById( 'gk-categories-toggle' );
			var content = document.getElementById( 'gk-categories-content' );
			var resetBtn = document.getElementById( 'gk-categories-reset' );
			if ( ! wrap || ! toggleBtn || ! content ) return;
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var options = ( data && data.categoryFilterOptions ) ? data.categoryFilterOptions : {};
			var slugs = Object.keys( options );
			content.innerHTML = '';
			for ( var i = 0; i < slugs.length; i++ ) {
				var slug = slugs[ i ];
				var opt = options[ slug ];
				var name = ( opt && opt.label ) ? opt.label : slug;
				var count = ( opt && typeof opt.count === 'number' ) ? opt.count : 0;
				var label = document.createElement( 'label' );
				label.className = 'gk-filter-checkbox-label';
				var input = document.createElement( 'input' );
				input.type = 'checkbox';
				input.className = 'gk-filter-checkbox';
				input.setAttribute( 'data-cat-slug', slug );
				input.setAttribute( 'aria-label', name + ' (' + count + ')' );
				var span = document.createElement( 'span' );
				span.className = 'gk-filter-checkbox-text';
				span.appendChild( document.createTextNode( name + ' ' ) );
				var countSpan = document.createElement( 'span' );
				countSpan.className = 'gk-filter-category-count';
				countSpan.textContent = '(' + count + ')';
				span.appendChild( countSpan );
				label.appendChild( input );
				label.appendChild( span );
				content.appendChild( label );
			}
			function updateResetVisibility() {
				var checked = content.querySelectorAll( 'input:checked' ).length;
				if ( resetBtn ) {
					resetBtn.classList.toggle( 'is-visible', checked > 0 );
				}
			}
			function onFilterChange() {
				updateResetVisibility();
				document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
			}
			content.addEventListener( 'change', onFilterChange );
			toggleBtn.addEventListener( 'click', function() {
				var collapsed = wrap.classList.toggle( 'is-collapsed' );
				toggleBtn.setAttribute( 'aria-expanded', ! collapsed );
			} );
			if ( resetBtn ) {
				resetBtn.addEventListener( 'click', function() {
					var inputs = content.querySelectorAll( 'input' );
					for ( var k = 0; k < inputs.length; k++ ) {
						inputs[ k ].checked = false;
					}
					onFilterChange();
				} );
			}
			updateResetVisibility();
		}
		initCategoriesFilter();

		function initGamepadsFilter() {
			var wrap = document.querySelector( '.gk-filter-gamepads' );
			var toggleBtn = document.getElementById( 'gk-gamepads-toggle' );
			var content = document.getElementById( 'gk-gamepads-content' );
			var resetBtn = document.getElementById( 'gk-gamepads-reset' );
			if ( ! wrap || ! toggleBtn || ! content ) return;
			var data = typeof gkPillSearch !== 'undefined' && gkPillSearch.productsData;
			var options = ( data && data.gamepadOptions ) ? data.gamepadOptions : {};
			var slugs = Object.keys( options );
			content.innerHTML = '';
			for ( var i = 0; i < slugs.length; i++ ) {
				var slug = slugs[ i ];
				var name = options[ slug ] || slug;
				var label = document.createElement( 'label' );
				label.className = 'gk-filter-checkbox-label';
				var input = document.createElement( 'input' );
				input.type = 'checkbox';
				input.className = 'gk-filter-checkbox';
				input.setAttribute( 'data-gamepad-slug', slug );
				input.setAttribute( 'aria-label', name );
				var span = document.createElement( 'span' );
				span.className = 'gk-filter-checkbox-text';
				span.textContent = name;
				label.appendChild( input );
				label.appendChild( span );
				content.appendChild( label );
			}
			function updateResetVisibility() {
				var checked = content.querySelectorAll( 'input:checked' ).length;
				if ( resetBtn ) {
					resetBtn.classList.toggle( 'is-visible', checked > 0 );
				}
			}
			function onFilterChange() {
				updateResetVisibility();
				document.dispatchEvent( new CustomEvent( 'gk_search_filters_changed' ) );
			}
			content.addEventListener( 'change', onFilterChange );
			toggleBtn.addEventListener( 'click', function() {
				var collapsed = wrap.classList.toggle( 'is-collapsed' );
				toggleBtn.setAttribute( 'aria-expanded', ! collapsed );
			} );
			if ( resetBtn ) {
				resetBtn.addEventListener( 'click', function() {
					var inputs = content.querySelectorAll( 'input' );
					for ( var k = 0; k < inputs.length; k++ ) {
						inputs[ k ].checked = false;
					}
					onFilterChange();
				} );
			}
			updateResetVisibility();
		}
		initGamepadsFilter();
		updateActiveFiltersBar();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function() {
			init();
			initFilterSidebar();
		} );
	} else {
		init();
		initFilterSidebar();
	}
}() );

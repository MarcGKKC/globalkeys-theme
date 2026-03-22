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
			if ( sortVal === 'name-asc' ) {
				ids.sort( function( a, b ) { return ( names[ a ] || '' ).localeCompare( names[ b ] || '' ); } );
			} else if ( sortVal === 'name-desc' ) {
				ids.sort( function( a, b ) { return ( names[ b ] || '' ).localeCompare( names[ a ] || '' ); } );
			} else if ( sortVal === 'price-asc' ) {
				ids.sort( function( a, b ) { return ( prices[ a ] || 0 ) - ( prices[ b ] || 0 ); } );
			} else if ( sortVal === 'price-desc' ) {
				ids.sort( function( a, b ) { return ( prices[ b ] || 0 ) - ( prices[ a ] || 0 ); } );
			}
			return ids;
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

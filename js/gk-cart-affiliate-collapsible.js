/**
 * Cart summary: collapsible code rows (affiliate, discount) + / −.
 */
(function () {
	'use strict';

	var COLLAPSIBLES = [
		{
			blockSel: '.gk-cart-summary__affiliate--collapsible',
			headerSel: '[data-gk-affiliate-header]',
			skipLinkSel: '.gk-cart-summary__affiliate-asterisk',
			panelSel: '.gk-cart-summary__affiliate-panel',
			inputSel: '.gk-cart-summary__affiliate-input',
			rowSel: '[data-gk-affiliate-row]',
			toggleSel: '[data-gk-affiliate-toggle-icon]',
			iconSel: '[data-gk-affiliate-icon-char]',
			i18nKey: 'affiliate',
			defaultExpanded: true,
		},
		{
			blockSel: '.gk-cart-summary__discount--collapsible',
			headerSel: '[data-gk-discount-header]',
			skipLinkSel: null,
			panelSel: '.gk-cart-summary__discount-panel',
			inputSel: '.gk-cart-summary__discount-input',
			rowSel: '[data-gk-discount-row]',
			toggleSel: '[data-gk-discount-toggle-icon]',
			iconSel: '[data-gk-discount-icon-char]',
			i18nKey: 'discount',
		},
	];

	function ariaForToggle( cfg, expanded ) {
		var i18n = window.gkCartAffiliateCollapse && window.gkCartAffiliateCollapse.i18n;
		if ( ! i18n || ! cfg.i18nKey ) {
			return;
		}
		if ( cfg.i18nKey === 'discount' ) {
			return expanded ? i18n.hideDiscount : i18n.showDiscount;
		}
		return expanded ? i18n.hide : i18n.show;
	}

	function setExpanded( block, expanded, cfg, opts ) {
		opts = opts || {};
		var panel = block.querySelector( cfg.panelSel );
		var input = panel ? panel.querySelector( cfg.inputSel ) : null;
		var rowBtn = block.querySelector( cfg.rowSel );
		var iconBtn = block.querySelector( cfg.toggleSel );
		var iconText = iconBtn ? iconBtn.querySelector( cfg.iconSel ) : null;

		block.classList.toggle( 'is-collapsed', ! expanded );
		block.classList.toggle( 'is-expanded', expanded );

		if ( panel ) {
			panel.hidden = ! expanded;
		}

		var str = expanded ? 'true' : 'false';
		if ( rowBtn ) {
			rowBtn.setAttribute( 'aria-expanded', str );
		}
		if ( iconBtn ) {
			iconBtn.setAttribute( 'aria-expanded', str );
			var label = ariaForToggle( cfg, expanded );
			if ( label ) {
				iconBtn.setAttribute( 'aria-label', label );
			}
		}
		if ( iconText ) {
			iconText.textContent = expanded ? '\u2212' : '+';
		}

		if ( expanded && input && ! opts.skipFocus ) {
			window.setTimeout( function () {
				input.focus( { preventScroll: true } );
			}, 0 );
		}
	}

	function onHeaderClick( e, cfg ) {
		var header = e.currentTarget;
		var block = header.closest( cfg.blockSel );
		if ( ! block ) {
			return;
		}
		if ( cfg.skipLinkSel && e.target.closest( cfg.skipLinkSel ) ) {
			return;
		}

		var isIconBtn = e.target.closest( cfg.toggleSel );
		var expanded = block.classList.contains( 'is-expanded' );

		if ( expanded ) {
			if ( isIconBtn ) {
				setExpanded( block, false, cfg, {} );
			}
			return;
		}
		setExpanded( block, true, cfg, {} );
	}

	function initBlock( block, cfg ) {
		var header = block.querySelector( cfg.headerSel );
		if ( ! header ) {
			return;
		}
		header.addEventListener( 'click', function ( ev ) {
			onHeaderClick( ev, cfg );
		} );
		var initial = cfg.defaultExpanded === true;
		setExpanded( block, initial, cfg, { skipFocus: initial } );
	}

	function init() {
		COLLAPSIBLES.forEach( function ( cfg ) {
			document.querySelectorAll( cfg.blockSel ).forEach( function ( block ) {
				initBlock( block, cfg );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();

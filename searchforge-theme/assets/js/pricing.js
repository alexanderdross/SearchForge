/**
 * SearchForge Theme — Pricing toggle (monthly/annual).
 * Placeholder for future monthly/annual toggle functionality.
 */
( function() {
	'use strict';

	const toggle = document.querySelector( '.sf-pricing-toggle' );
	if ( ! toggle ) return;

	toggle.addEventListener( 'change', function() {
		const isAnnual = this.checked;
		document.querySelectorAll( '[data-price-monthly]' ).forEach( function( el ) {
			el.textContent = isAnnual
				? el.getAttribute( 'data-price-annual' )
				: el.getAttribute( 'data-price-monthly' );
		} );
	} );
} )();

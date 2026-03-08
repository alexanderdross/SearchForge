/**
 * SearchForge Theme — Mobile navigation toggle.
 */
( function() {
	'use strict';

	const toggle = document.querySelector( '.sf-header__toggle' );
	const menu   = document.getElementById( 'sf-mobile-menu' );

	if ( ! toggle || ! menu ) return;

	toggle.addEventListener( 'click', function() {
		const expanded = this.getAttribute( 'aria-expanded' ) === 'true';
		this.setAttribute( 'aria-expanded', String( ! expanded ) );
		menu.hidden = expanded;

		if ( ! expanded ) {
			const firstLink = menu.querySelector( 'a' );
			if ( firstLink ) firstLink.focus();
		}
	} );

	// Close on Escape.
	document.addEventListener( 'keydown', function( e ) {
		if ( e.key === 'Escape' && ! menu.hidden ) {
			toggle.setAttribute( 'aria-expanded', 'false' );
			menu.hidden = true;
			toggle.focus();
		}
	} );
} )();

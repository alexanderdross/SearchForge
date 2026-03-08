/**
 * SearchForge Theme — FAQ accordion with hash navigation.
 */
( function() {
	'use strict';

	const buttons = document.querySelectorAll( '.sf-faq__question' );

	function openItem( button ) {
		const answerId = button.getAttribute( 'aria-controls' );
		const answer   = document.getElementById( answerId );

		button.setAttribute( 'aria-expanded', 'true' );
		if ( answer ) answer.hidden = false;
	}

	function closeItem( button ) {
		const answerId = button.getAttribute( 'aria-controls' );
		const answer   = document.getElementById( answerId );

		button.setAttribute( 'aria-expanded', 'false' );
		if ( answer ) answer.hidden = true;
	}

	buttons.forEach( function( button ) {
		button.addEventListener( 'click', function() {
			const expanded = this.getAttribute( 'aria-expanded' ) === 'true';

			// Close all others.
			buttons.forEach( function( other ) {
				if ( other !== button ) closeItem( other );
			} );

			// Toggle current.
			if ( expanded ) {
				closeItem( this );
			} else {
				openItem( this );
				// Update URL hash without scrolling.
				var item = this.closest( '.sf-faq__item' );
				if ( item && item.id ) {
					history.replaceState( null, '', '#' + item.id );
				}
			}
		} );
	} );

	// Open FAQ item matching URL hash on page load.
	function openFromHash() {
		var hash = window.location.hash.substring( 1 );
		if ( ! hash ) return;

		var target = document.getElementById( hash );
		if ( ! target || ! target.classList.contains( 'sf-faq__item' ) ) return;

		var btn = target.querySelector( '.sf-faq__question' );
		if ( btn ) {
			openItem( btn );
			target.scrollIntoView( { behavior: 'smooth', block: 'center' } );
		}
	}

	openFromHash();
	window.addEventListener( 'hashchange', openFromHash );
} )();

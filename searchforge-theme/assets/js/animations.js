/**
 * SearchForge Theme — Scroll-triggered fade-in animations.
 */
( function() {
	'use strict';

	// Respect reduced motion preference.
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

	const observer = new IntersectionObserver(
		function( entries ) {
			entries.forEach( function( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'sf-visible' );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
	);

	document.querySelectorAll( '.sf-section' ).forEach( function( section ) {
		section.classList.add( 'sf-animate' );
		observer.observe( section );
	} );
} )();

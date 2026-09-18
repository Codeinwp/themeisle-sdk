/* global themeisleSDKAiConnect */
import './ai-connect.css';

( function() {
	'use strict';

	const init = () => {
		const cfg = window.themeisleSDKAiConnect || {};
		const modal = document.getElementById( 'ti-ai-connect' );
		if ( ! modal ) {
			return;
		}

		const labels = cfg.labels || {};
		const dialog = modal.querySelector( '.ti-ai-dialog' );
		const errorBox = modal.querySelector( '[data-error]' );
		let enabled = false;
		let productKey = cfg.current || '';
		let lastFocus = null;

		const post = ( action, extra ) => {
			const body = new URLSearchParams( Object.assign( { action, nonce: cfg.nonce }, extra || {} ) );
			return window.fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body } )
				.then( ( response ) => response.json().catch( () => ( { success: false } ) ) );
		};

		const fill = ( text, name ) => ( text || '' ).replace( '%s', name );

		const setLinks = ( links ) => {
			modal.querySelectorAll( '.ti-ai-client' ).forEach( ( a ) => {
				const href = ( links || {} )[ a.dataset.client ];
				if ( ! href ) {
					return;
				}
				a.href = href;
				// cursor:// is a protocol handler: a new tab would stay behind, blank.
				if ( href.indexOf( 'http' ) !== 0 ) {
					a.removeAttribute( 'target' );
				}
			} );
		};

		const renderPrompts = ( product ) => {
			const list = modal.querySelector( '[data-prompts]' );
			list.textContent = '';
			( product.prompts || [] ).forEach( ( prompt, index ) => {
				const item = document.createElement( 'li' );
				const text = document.createElement( 'span' );
				text.id = 'ti-ai-prompt-' + index;
				text.textContent = '“' + prompt + '”';
				text.dataset.raw = prompt;
				const button = document.createElement( 'button' );
				button.type = 'button';
				button.className = 'button button-small ti-ai-when-on';
				button.dataset.copy = '#' + text.id;
				button.textContent = labels.copy || 'Copy';
				item.appendChild( text );
				item.appendChild( button );
				list.appendChild( item );
			} );
		};

		const render = () => {
			const product = ( cfg.products || {} )[ productKey ] || { name: '', prompts: [] };
			dialog.dataset.enabled = enabled ? '1' : '0';
			modal.querySelector( '[data-title]' ).textContent = fill( labels.title, product.name );
			modal.querySelector( '[data-lead]' ).textContent = fill( labels.lead, product.name );
			modal.querySelector( '[data-endpoint]' ).value = cfg.endpoint || '';
			// The URL is known up front but inert until the connector is enabled.
			modal.querySelectorAll( '.ti-ai-copyrow input, .ti-ai-copyrow .button' ).forEach( ( el ) => {
				el.disabled = ! enabled;
			} );
			modal.querySelectorAll( '.ti-ai-client' ).forEach( ( a ) => {
				a.setAttribute( 'aria-disabled', enabled ? 'false' : 'true' );
				a.tabIndex = enabled ? 0 : -1;
			} );
			setLinks( cfg.links );
			renderPrompts( product );
		};

		const open = ( key ) => {
			if ( key && ( cfg.products || {} )[ key ] ) {
				productKey = key;
			}
			lastFocus = document.activeElement;
			render();
			modal.hidden = false;
			document.body.classList.add( 'modal-open' );
			( enabled ? modal.querySelector( '.ti-ai-client' ) : modal.querySelector( '[data-enable]' ) ).focus();
		};

		const close = () => {
			modal.hidden = true;
			document.body.classList.remove( 'modal-open' );
			if ( lastFocus && lastFocus.focus ) {
				lastFocus.focus();
			}
		};

		const enable = ( button ) => {
			button.disabled = true;
			button.textContent = labels.enabling || 'Enabling…';
			errorBox.hidden = true;
			post( cfg.actions.enable, { product: productKey } ).then( ( response ) => {
				button.disabled = false;
				button.textContent = labels.enable || 'Enable Easy MCP Connector';
				if ( ! response || ! response.success ) {
					errorBox.textContent = ( response && response.data && response.data.message ) || labels.error_install || '';
					errorBox.hidden = false;
					return;
				}
				enabled = true;
				cfg.endpoint = response.data.endpoint || cfg.endpoint;
				cfg.links = response.data.links || cfg.links;
				render();
				modal.querySelector( '.ti-ai-client' ).focus();
				// The invitation is answered: the notice has nothing left to say.
				document.querySelectorAll( '[data-ti-ai-notice]' ).forEach( ( notice ) => notice.remove() );
			} ).catch( () => {
				button.disabled = false;
				button.textContent = labels.enable || 'Enable Easy MCP Connector';
				errorBox.textContent = labels.error_install || '';
				errorBox.hidden = false;
			} );
		};

		const copy = ( selector, button ) => {
			const el = document.querySelector( selector );
			if ( ! el ) {
				return;
			}
			const text = el.value !== undefined ? el.value : ( el.dataset.raw || el.textContent );
			const done = () => {
				const label = button.textContent;
				button.textContent = labels.copied || 'Copied';
				button.classList.add( 'is-copied' );
				setTimeout( () => {
					button.textContent = label;
					button.classList.remove( 'is-copied' );
				}, 1400 );
			};
			if ( window.navigator.clipboard ) {
				window.navigator.clipboard.writeText( text ).then( done );
			} else if ( el.select ) {
				el.select();
				document.execCommand( 'copy' );
				done();
			}
		};

		document.addEventListener( 'click', ( event ) => {
			const target = event.target;
			const opener = target.closest( '[data-ti-ai-connect]' );
			if ( opener ) {
				event.preventDefault();
				return open( opener.dataset.tiAiConnect );
			}
			// Core's common.js adds the X and removes the notice; remember it for this user.
			if ( target.closest( '[data-ti-ai-notice] .notice-dismiss' ) ) {
				post( cfg.actions.dismiss );
				return;
			}
			if ( ! modal.contains( target ) ) {
				return;
			}
			if ( target.closest( '[data-close]' ) ) {
				event.preventDefault();
				return close();
			}
			const client = target.closest( '.ti-ai-client' );
			if ( client && ! enabled ) {
				event.preventDefault();
				return;
			}
			const enableButton = target.closest( '[data-enable]' );
			if ( enableButton ) {
				return enable( enableButton );
			}
			const copyButton = target.closest( '[data-copy]' );
			if ( copyButton ) {
				return copy( copyButton.dataset.copy, copyButton );
			}
		} );

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' && ! modal.hidden ) {
				close();
			}
		} );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );

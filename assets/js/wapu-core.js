( function( $, settings ) {

	'use strict';

	var wapuCore = {
		css: {
			rating: '[data-init="post-raing"]',
			popupTrigger: '[data-init="popup"]',
			popupClose: '.wapu-close',
			docSearch: '[data-init="docs"]',
			docInput: 'input[name="_docs_query"]',
			docMsg: '.doc-search__msg',
			docMsgError: 'msg-error',
			popupOverlay: '.wapu-popup__overlay',
			faqOpen: '.faq-post__title',
			tabsNavItem: '.account-tabs__nav-item-link',
			addToCart: '.download-add-to-cart',
		},

		objects: {
			popup: null,
			popupConent: null,
			loader: '<div class="wapu-loader"></div>',
			clipboard: null
		},

		init: function() {

			if ( 'undefined' !== typeof Clipboard && 'undefined' !== typeof Clipboard.prototype.defaultTarget ) {

				wapuCore.objects.clipboard = new Clipboard( '.copy-to-clipboard', {
					text: function( trigger ) {
						return trigger.getAttribute( 'data-copy' );
					}
				});

				wapuCore.objects.clipboard.on( 'success', wapuCore.clipboardSuccessCallback );
			}

			$( document )
				.on( 'click.wapuCore', wapuCore.css.popupTrigger, wapuCore.openPopup )
				.on( 'click.wapuCore', wapuCore.css.popupOverlay, wapuCore.closePopup )
				.on( 'click.wapuCore', wapuCore.css.popupClose, wapuCore.closePopup )
				.on( 'click.wapuCore', wapuCore.css.rating, wapuCore.processRating )
				.on( 'click.wapuCore', wapuCore.css.docSearch, wapuCore.processDocSearch )
				.on( 'click.wapuCore', wapuCore.css.faqOpen, wapuCore.openFaq )
				.on( 'click.wapuCore', wapuCore.css.tabsNavItem, wapuCore.switchTabs )
				.on( 'click.wapuCore', wapuCore.css.addToCart, wapuCore.addToCart )
				.on( 'focus.wapuCore', wapuCore.css.docInput, wapuCore.removeError )
				.on( 'keyup.wapuCore', wapuCore.css.docInput, wapuCore.removeError )
				.on( 'keyup.wapuCore', wapuCore.css.docInput, wapuCore.openOnEnter )
				.on( 'wapuCorePopupOpened', wapuCore.getTicketWidget )
				.on( 'wapuCorePopupOpened', wapuCore.getVideo );

			this.loadFirstTab();

		},

		addToCart: function( event ) {

			event.preventDefault();

			var $this      = $( this ),
				downloadID = $this.data( 'download' );

			$this.html( settings.addToCart.processing );

			$.ajax({
				url: settings.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'edd_add_to_cart',
					download_id: downloadID,
					price_ids: [ downloadID ],
					post_data: $.param( {
						download_id: downloadID,
						edd_action: 'add_to_cart',
					} )
				},
			}).done( function( response ) {
				$this.html( settings.addToCart.added );
				$this.after( settings.addToCart.checkoutLink );
			});

		},

		loadFirstTab: function() {

			if ( ! $( '.account-tabs' ).length ) {
				return;
			}

			var hash      = window.location.hash.substr( 1 ),
				firstTab  = 'settings',
				$nav      = $( '.account-tabs__nav' ),
				$content  = $( '.account-tabs__content' );

			if ( hash ) {
				firstTab = hash;
			}

			$nav.find( '[href="#' + firstTab + '"]' ).closest( '.account-tabs__nav-item' ).addClass( 'active-item' );
			$content.find( '#' + firstTab ).addClass( 'active-item' );

		},

		switchTabs: function( event ) {

			event.preventDefault();

			var $this    = $( this ),
				$item    = $this.closest( '.account-tabs__nav-item' ),
				$content = $( '.account-tabs__content' ),
				tab      = $this.attr( 'href' );

			$item
				.addClass( 'active-item' )
				.siblings( '.active-item' )
				.removeClass( 'active-item' );

			$content
				.find( tab )
				.addClass( 'active-item' )
				.siblings( '.active-item' )
				.removeClass( 'active-item' );

			history.pushState( null, null, tab );

		},

		clipboardSuccessCallback: function( event ) {

			$( event.trigger ).addClass( 'copied' );

		},

		openFaq: function( event ) {

			var $trigger = $( this ),
				$content = $trigger.next(),
				$inner   = $( '.faq-post__inner', $content ),
				height   = $inner.outerHeight();

			if ( $trigger.hasClass( 'faq-active' ) ) {
				$trigger.removeClass( 'faq-active' );
				$content.animate( { height: 0 }, 300 );
			} else {
				$trigger.addClass( 'faq-active' );
				$content.animate( { height: height }, 300 );
			}


		},

		openPopup: function( event ) {

			var $this = $( this );

			if ( undefined !== event ) {
				event.preventDefault();
			}

			if ( null === wapuCore.objects.popup ) {
				wapuCore.objects.popup       = $( '.wapu-popup' );
				wapuCore.objects.popupConent = wapuCore.objects.popup.find( '.wapu-popup__data' );
			}

			wapuCore.objects.popup.removeClass( 'popup-hidden' );

			$( document ).trigger( 'wapuCorePopupOpened', [ $this ] );
		},

		getVideo: function( event, $from ) {

			var postId = $from.data( 'post' );

			if ( ! postId ) {
				return;
			}

			wapuCore.objects.popupConent.html( wapuCore.objects.loader );

			$.ajax({
				url: settings.ajaxurl,
				type: 'get',
				dataType: 'html',
				data: {
					action: 'wapu_core_get_video',
					id: postId
				}
			}).done( function( response ) {
				wapuCore.objects.popupConent.html( response );
			});

		},

		getTicketWidget: function( event, $from ) {
			var widget  = $from.data( 'widget' ),
				content = window.wapuCorePopupTemplates[ widget ];

			wapuCore.objects.popupConent.html( content );
		},

		openOnEnter: function( event ) {

			if( 13 === event.keyCode ){
				$( wapuCore.css.docSearch ).trigger( 'click.wapuCore' );
			}

		},

		processDocSearch: function( event ) {

			var $this    = $( this ),
				btnText  = $this.html(),
				$input   = $( wapuCore.css.docInput ),
				$results = $input.closest('.docs-search').next( 'div[data-search-results]' ),
				query    = $input.val(),
				$msg     = $( wapuCore.css.docMsg );

			if ( $this.hasClass( 'in-progress' ) ) {
				return;
			}

			if ( '' === query ) {
				$input.addClass( 'error' );
			}

			if ( $input.hasClass( 'error' ) ) {
				return;
			}

			$results.html( '' );

			$this.html( wapuCore.objects.loader ).addClass( 'in-progress' );
			$msg.html( '' ).removeClass( wapuCore.css.docMsgError );

			$.ajax({
				url: settings.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'wapu_core_search_docs',
					query: query
				}
			}).done( function( response ) {

				$this.html( btnText ).removeClass( 'in-progress' );

				if ( true === response.success ) {
					$results.html( response.data.message );
				} else {
					$msg.html( response.data.message ).addClass( wapuCore.css.docMsgError );
					$input.addClass( 'error' );
				}

			});

		},

		removeError: function() {

			var $msg  = $( wapuCore.css.docMsg ),
				$this = $( this );

			if ( $this.hasClass( 'error' ) ) {
				$( this ).removeClass( 'error' );
				$msg.html( '' ).removeClass( wapuCore.css.docMsgError );
			}
		},

		closePopup: function( event ) {

			event.preventDefault();

			wapuCore.objects.popup.addClass( 'popup-hidden' );
			wapuCore.objects.popupConent.html( '' );

		},

		processRating: function( event ) {

			var $this   = $( this ),
				data    = $this.data(),
				$parent = $this.parent(),
				replace = false;

			event.preventDefault();

			if ( wapuCore.isRated( data ) ) {
				$parent.replaceWith( settings.postRating.rated );
				return;
			}

			if ( $this.hasClass( 'in-progress' ) || $parent.hasClass( 'in-progress' ) ) {
				return;
			}

			$this.addClass( 'in-progress' );
			$parent.addClass( 'in-progress' );

			$.ajax({
				url: settings.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: data
			}).done( function( response ) {

				$this.removeClass( 'in-progress' );
				$parent.removeClass( 'in-progress' );

				if ( true === response.success ) {
					wapuCore.setRated( data );
					replace = response.data.replace;
					$this.closest( '.wapu-post-rating__counts' ).attr({
						'data-likes' : response.data.rating.likes,
						'data-dislikes' : response.data.rating.dislikes,
						'data-diff' : response.data.rating.diff,
					});
				} else {
					replace = response.data.error;
				}

				if ( false !== replace ) {
					$parent.replaceWith( replace );
				}

			});
		},

		setRated: function( data ) {

			var ratedPosts = localStorage.getItem( 'wapuRated' );

			if ( null === ratedPosts ) {
				ratedPosts = [];
			} else {
				ratedPosts = JSON.parse( ratedPosts );
			}

			ratedPosts.push( data.id );
			localStorage.setItem( 'wapuRated', JSON.stringify( ratedPosts ) );

		},

		isRated: function( data ) {

			var ratedPosts = localStorage.getItem( 'wapuRated' ),
				index      = false;

			if ( null === ratedPosts ) {
				return false;
			}

			ratedPosts = JSON.parse( ratedPosts );
			index      = ratedPosts.indexOf( data.id );

			if ( 0 > index ) {
				return false;
			} else {
				return true;
			}

		}

	};

	wapuCore.init();

}( jQuery, window.wapuCoreSettings ) );

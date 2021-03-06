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
			headerCart: '.header-cart__item',
			headerCartClose: '.cart-close',
			contentTrigger: '.content-trigger',
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
				.on( 'click.wapuCore', wapuCore.css.headerCart, wapuCore.openCartPopup )
				.on( 'click.wapuCore', wapuCore.css.headerCartClose, wapuCore.closeCartPopup )
				.on( 'click.wapuCore', wapuCore.css.contentTrigger, wapuCore.expandThemeContent )
				.on( 'focus.wapuCore', wapuCore.css.docInput, wapuCore.removeError )
				.on( 'keyup.wapuCore', wapuCore.css.docInput, wapuCore.removeError )
				.on( 'keyup.wapuCore', wapuCore.css.docInput, wapuCore.openOnEnter )
				.on( 'wapuCorePopupOpened', wapuCore.getTicketWidget )
				.on( 'wapuCorePopupOpened', wapuCore.getVideo );

			this.loadFirstTab();
			this.loadCartData();

		},

		expandThemeContent: function( event ) {
			$( this ).prev( '.downloads-content-box__inner' ).toggleClass( 'content-expanded' );
		},

		loadCartData: function() {

			var $headerCart = $( '.header-cart' );

			if ( ! $headerCart.length ) {
				return;
			}

			$.ajax({
				url: settings.api.ajaxUri,
				type: 'GET',
				dataType: 'json',
				data: {
					action: settings.api.endpoints.cart
				},
			}).done( function( response ) {

				$headerCart.find( '.header-cart__count' ).html( response.count );
				$( '.cart-popup[data-popup="account"]' ).prepend( response.account );
				$( '.cart-popup[data-popup="cart"] .cart-content' ).html( response.contents );

			} );

		},

		closeCartPopup: function( event ) {
			event.preventDefault();
			$( this ).closest( '.cart-popup' ).removeClass( 'cart-popup-active' );
		},

		openCartPopup: function( event ) {

			event.preventDefault();

			var $this         = $( this ),
				$popups       = $( '.cart-popups' ),
				popup         = $this.data( 'open' ),
				$currentPopup = $popups.find( 'div[data-popup="' + popup + '"]' );

			if ( $currentPopup.hasClass( 'cart-popup-active' ) ) {
				$currentPopup.removeClass( 'cart-popup-active' );
			} else {

				$popups.find( '.cart-popup-active' ).removeClass( 'cart-popup-active' );
				$currentPopup.addClass( 'cart-popup-active' );

				$( document ).trigger( 'wapuCartPopupOpened', [ popup, $currentPopup ] );
			}

		},

		getCartContents: function( event, slug, $el ) {

			if ( 'cart' !== slug ) {
				return;
			}

			if ( $el.hasClass( 'cart-loaded' ) ) {
				return;
			}

			$.ajax({
				url: settings.ajaxurl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: 'wapu_get_cart_content'
				},
			}).done( function( response ) {
				$el.addClass( 'cart-loaded' ).find( '.cart-content' ).html( response.data );
			});

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

	var $themesListing = $( '#themes-listing' ),
		queryData      = $themesListing.data( 'query' );

	if ( $themesListing.length ) {
		new Vue({
			el: '#themes-listing',
			data: {
				page: 1,
				pages: 1,
				loaded: false,
				sortbyList: queryData.sortby,
				sortby: 'latest',
				sortbyShow: false,
				posts: [],
				showCartPopup: false,
				showWishlistPopup: false,
				cart: null,
				addedToCart: false,
				checkoutURL: '',
				cartLabel: 'Add to Cart',
				wishListLoaded: false,
				wishListContent: '',
				wishlistData: {},
				moreLabel: 'Load More',
			},
			methods: {
				sortThemes: function( key ) {

					this.sortby     = key;
					this.sortbyShow = false;
					this.page       = 1;
					this.loaded     = false;

					this.posts.splice( 0, this.posts.length );

					this.updateThemesList();

				},
				salesLabel: function( sales ) {
					if ( 1 == sales ) {
						return ' sale';
					} else {
						return ' sales';
					}
				},
				addToCart: function( post ) {
					this.cart          = post;
					this.showCartPopup = true;
				},
				addToWishlist: function( post ) {

					var self = this;

					self.showWishlistPopup = true;
					self.cart              = post;

					$.ajax({
						url: settings.api.ajaxUri,
						type: 'GET',
						dataType: 'json',
						data: {
							action: settings.api.endpoints.getWishListModal,
							theme: post.id
						},
					}).done( function( response ) {
						self.wishListContent = response.lists;
						self.wishListLoaded  = true;
					} );
				},
				closePopups: function() {
					this.showCartPopup     = false;
					this.showWishlistPopup = false;
					this.addedToCart       = false;
					this.cartLabel         = 'Add to Cart';
					this.wishListLoaded    = false;
					this.wishListContent   = '';
					this.wishlistData      = {};
					this.cart              = null;
				},
				processAddToCart: function( id ) {

					var self = this;

					self.cartLabel      = 'Adding...';

					$.ajax({
						url: settings.api.uri + settings.api.endpoints.addToCart,
						type: 'GET',
						dataType: 'json',
						data: {
							theme: id
						},
					}).done( function( response ) {
						if ( response.checkout ) {
							self.checkoutURL = response.checkout;
							self.addedToCart = true;
						} else {
							self.cartLabel = 'Error. Please try again later';
						}
					} );

				},
				loadMore: function() {

					this.moreLabel = 'Loading...';
					this.page++;
					this.updateThemesList();

				},
				updateThemesList: function() {

					var self = this;

					$.ajax({
						url: settings.api.uri + settings.api.endpoints.themes,
						type: 'GET',
						dataType: 'json',
						data: {
							page: self.page,
							sort: self.sortby,
							per_page: queryData.per_page,
							category: queryData.category,
							thumb_size: queryData.thumb_size
						},
					}).done( function( response ) {

						self.loaded    = true;
						self.page      = response.page;
						self.pages     = response.total_pages;
						self.moreLabel = 'Load More';

						response.themes.forEach( function( item ) {
							self.posts.push( item );
						} );

					} );

				}
			},
			mounted: function() {

				var self = this;

				$( self.$el ).removeClass( 'hidden' );

				self.updateThemesList();

				$( self.$el ).on( 'click', '.edd-wl-save', function( event ) {

					event.preventDefault();

					var $this = $( this ),
						$form = $this.closest( '.listing-wl' ),
						data  = {
							download_id: self.cart.id,
							price_ids: [ self.cart.id ],
							new_or_existing: $form.find( 'input[name="list-options"]:checked' ).val(),
							list_name: $form.find( 'input[name="list-name"]' ).val(),
							list_status: $form.find( 'select[name="list-status"] option:selected' ).val(),
						};

					$this.html( 'Saving...' );

					if ( $form.find( 'select[name="user-lists"]' ).length ) {
						data.list_id = $form.find( 'select[name="user-lists"] option:selected' ).val();
					}

					$.ajax({
						url: settings.api.uri + settings.api.endpoints.addToWishlist,
						type: 'GET',
						dataType: 'json',
						data: data,
					}).done( function( response ) {
						self.wishListContent = response.result;
						$this.html( 'Save' );
					} );

				} );

				$( document ).on( 'click', function() {
					self.sortbyShow = false;
				});

			}
		});
	}

}( jQuery, window.wapuCoreSettings ) );

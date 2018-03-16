<?php
/**
 * Main themes list content template
 */
?>
<div id="themes-listing" data-query='<?php echo json_encode( $query_args ); ?>'>
	<div class="themes-listing-sortby">
		<div class="themes-listing-sortby__label">
			Sort by:
		</div>
		<div class="themes-listing-sortby__current" v-html="sortbyList[ sortby ]" @click.stop="sortbyShow = !sortbyShow"></div>
		<transition name="sortby">
			<div v-if="sortbyShow" class="themes-listing-sortby__list" @click.stop="sortbyShow = true">
				<div class="themes-listing-sortby__list-item" v-for="(sortLabel, sortKey) in sortbyList" @click.stop="sortThemes( sortKey )">{{ sortLabel }}</div>
			</div>
		</transition>
	</div>
	<div v-if="loaded">
		<div class="themes-listing">
			<div class="themes-listing__item" v-for="post in posts">
				<div class="themes-listing__item-inner">
					<div v-if="post.thumb" class="themes-listing__item-thumb">
						<img :src="post.thumb">
						<a class="":href="post.live_demo">
							<i class="nc-icon-mini tech_desktop-screen"></i>
							Live Demo
						</a>
					</div>
					<div class="themes-listing__item-content">
						<div v-if="post.topics" class="themes-listing__item-topics">
							<div class="themes-listing__item-topic" v-for="topic in post.topics">{{ topic }}</div>
						</div>
						<div class="themes-listing__item-title">
							<a class="themes-listing__item-link" :href="post.url">{{ post.title }}</a>
						</div>
						<div v-if="post.rating" class="themes-listing__item-rating" v-html="post.rating"></div>
						<div class="themes-listing__item-footer">
							<div class="themes-listing__item-meta">
								<div v-if="post.sale_price" class="themes-listing__item-price">
									<del v-html="post.price"></del>
									<ins v-html="post.sale_price"></ins>
								</div>
								<div v-else class="themes-listing__item-price" v-html="post.price"></div>
								<div class="themes-listing__item-sales">
									{{ post.sales }}{{ salesLabel( post.sales ) }}
								</div>
							</div>
							<div class="themes-listing__item-actions">
								<button class="themes-listing__item-wishlist" @click="addToWishlist( post )">
									<i class="nc-icon-mini ui-2_favourite-28"></i>
								</button>
								<button class="themes-listing__item-add-to-cart" @click="addToCart( post )">
									<i class="nc-icon-mini shopping_cart-simple"></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-if="pages > page" class="themes-listing-more">
			<button @click="loadMore">{{ moreLabel }}</button>
		</div>
	</div>
	<div v-if="showCartPopup" :class="[ 'wapu-popup', 'listing-popup' ]">
		<div class="wapu-popup__overlay" @click="closePopups"></div>
		<div class="wapu-popup__content">
			<div class="listing-cart">
				<div class="listing-cart__item">
					<img class="listing-cart__thumb":src="cart.thumb">
					<div class="listing-cart__content">
						<div class="listing-cart__title">
							<a :href="cart.url">{{ cart.title }}</a>
						</div>
						<div v-if="cart.sale_price" class="listing-cart__price">
							<del v-html="cart.price"></del>
							<ins v-html="cart.sale_price"></ins>
						</div>
						<div v-else class="listing-cart__price" v-html="cart.price"></div>
					</div>
				</div>
				<div class="listing-cart__actions">
					<button class="listing-cart__cancel" @click="closePopups">Cancel</button>
					<div v-if="!addedToCart">
						<button class="listing-cart__submit" @click="processAddToCart( cart.id )">
							{{ cartLabel }}
						</button>
					</div>
					<div v-else>
						<a class="listing-cart__cancel" :href="checkoutURL">Go To Checkout</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div v-if="showWishlistPopup" :class="[ 'wapu-popup', 'listing-popup' ]">
		<div class="wapu-popup__overlay" @click="closePopups"></div>
		<div class="wapu-popup__content">
			<div v-if="!wishListLoaded" class="listing-wl-loading">
				Loading
			</div>
			<div v-else class="listing-wl" v-html="wishListContent"></div>
		</div>
	</div>
</div>
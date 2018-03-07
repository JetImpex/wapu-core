<?php
/**
 * Main themes list content template
 */
?>
<div id="themes-listing" data-query='<?php echo json_encode( $query_args ); ?>'>
	<div class="themes-listing" v-if="loaded">
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
							<button class="themes-listing__item-wishlist" @click="addToWishlist( post.id )">
								<i class="nc-icon-mini ui-2_favourite-28"></i>
							</button>
							<button class="themes-listing__item-add-to-cart" @click="addToCart( post.id )">
								<i class="nc-icon-mini shopping_cart-simple"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div v-if="showCartPopup" class="add-to-cart-popup listing-popup">
		<div class="listing-popup__content"></div>
		<div class="listing-popup__close" @click="closePopups"><i class="nc-icon-mini ui-1_simple-remove"></i></div>
	</div>
	<div v-if="showWishlistPopup" class="wishlist-popup listing-popup">
		<div class="listing-popup__content"></div>
		<div class="listing-popup__close" @click="closePopups"><i class="nc-icon-mini ui-1_simple-remove"></i></div>
	</div>
</div>
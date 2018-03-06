<?php
/**
 * Cart teamplte
 */
?>
<div class="header-cart">
	<a href="#" class="header-cart__item header-cart-account" data-open="account">
		<i class="nc-icon-mini users_single-05"></i>
		My Account
	</a>
	<a href="#" class="header-cart__item header-cart-link"  data-open="cart">
		<i class="nc-icon-mini shopping_cart-simple"></i>
		Cart
		<span class="header-cart__count"><?php
			if ( function_exists( 'edd_get_cart_quantity' ) ) {
				echo edd_get_cart_quantity();
			}
		?></span>
	</a>
</div>
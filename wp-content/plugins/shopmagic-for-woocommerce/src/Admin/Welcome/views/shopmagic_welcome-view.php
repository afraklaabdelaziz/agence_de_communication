<?php
/**
 * Welcome Page View
 *
 * Welcome page content i.e. HTML/CSS/PHP.
 *
 * @since   1.0.0
 * @package SHOPMAGIC
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$polish_language_version = get_locale() === 'pl_PL';
?>
<style>
	.sm-wrap .sm-step-counter {
		line-height: 1.8;
		margin-left: 4em;
	}
	.sm-wrap .sm-step-counter li::marker {
		content: 'Step #' counter(list-item) ': ';
		font-weight: 800;
	}
	.sm-wrap .sm-step-counter li p {
		font-size: inherit;
	}
	.margin-y-2 {
		margin-top: 2em;
		margin-bottom: 2em;
	}
	.sm-wrap .sm-button-large {
		font-size: 18px !important;
	}
	.sm-wrap .image {
		width: 50%;
	}
	.sm-dashicon-large {
		font-size: 50px;
		margin: 24px 0;
		line-height: 1.2;
	}
</style>
<div class="wrap about-wrap sm-wrap">

	<h1><?php esc_html_e( 'ShopMagic - Follow-Up Emails & Marketing Automation for Your WooCommerce Store', 'shopmagic-for-woocommerce' ); ?></h1>
	<p class="about-text">
		<?php esc_html_e( 'Start increasing your revenue, customer loyalty and satisfaction with user-friendly emails based on order status.', 'shopmagic-for-woocommerce' ); ?>
	</p>

	<div class="feature-section">
		<h3><?php esc_html_e( 'Set up your automated email', 'shopmagic-for-woocommerce' ); ?></h3>
		<p>
		<?php
		echo wp_kses(
			__(
				'Each automation may consist of three elements: <b>Event</b> (e.g. <i>Order Completed</i>), which is a trigger.
						<b>Filter</b> (e.g. <i>Order - Items</i>), which you can use if you want to send your email to selected group of customers and
						<b>Action</b> (e.g. <i>Send Email</i>) in which you can create your message to the recipient.',
				'shopmagic-for-woocommerce'
			),
			[
				'b' => [],
				'i' => [],
			]
		);
		?>
		</p>
		<p>
		<?php
		echo sprintf(
			wp_kses( __( 'Start by creating a new <a href="%1$s">Automation →</a> or choose one of the <a href="%2$s">ready-to-use Recipes</a>.', 'shopmagic-for-woocommerce' ), [ 'a' => [ 'href' => [] ] ] ),
			esc_url( admin_url( 'post-new.php?post_type=shopmagic_automation' ) ),
			esc_url( admin_url( 'edit.php?post_type=shopmagic_automation#recipes_tab' ) )
		);
		?>
			</p>
		<ol class="sm-step-counter">
			<li>
				<p>
				<?php
				echo wp_kses(
					__( 'If you decide to create an automation from scratch, select an <b>Event</b> which will be a trigger for your automation. You can select from a variety of events, from <i>New Order</i> email, through <i>Order Pending</i> to <i>Order Completed</i>. We covered all of the WooCommerce order statuses.', 'shopmagic-for-woocommerce' ),
					[
						'b' => [],
						'i' => [],
					]
				);
				?>
				</p>
				<?php
				if ( $polish_language_version ) {
					$event_image_url = 'shopmagic-for-woocommerce/assets/images/event-pl.png';
				} else {
					$event_image_url = 'shopmagic-for-woocommerce/assets/images/event.png';
				}
				?>
				<img class="image" width="475" height="120" src="<?php echo esc_url( plugins_url( $event_image_url ) ); ?>" alt="<?php esc_attr_e( 'Event setter for automation with Order Completed selected', 'shopmagic-for-woocommerce' ); ?>>">
			</li>
			<li>
				<p>
				<?php
				echo wp_kses(
					__( 'Optionally - assign a <b>Filter</b>. Use it if you want to send your automation to the selected group of customers only.', 'shopmagic-for-woocommerce' ),
					[ 'b' => [] ]
				);
				?>
				</p>
				<?php
				if ( $polish_language_version ) {
					$filter_image_url = 'shopmagic-for-woocommerce/assets/images/filter-pl.png';
				} else {
					$filter_image_url = 'shopmagic-for-woocommerce/assets/images/filter.png';
				}
				?>
				<img class="image" width="475" height="100" src="<?php echo esc_url( plugins_url( $filter_image_url ) ); ?>" alt="<?php esc_attr_e( 'Filter setter for automation with Order Items selected', 'shopmagic-for-woocommerce' ); ?>>">
			</li>
			<li>
				<p>
				<?php
				echo wp_kses(
					__( 'Choose an <b>Action</b>. Select an Action type and enter data in action fields, as described. Do not forget to Publish your automation.', 'shopmagic-for-woocommerce' ),
					[ 'b' => [] ]
				);
				?>
				</p>
				<?php
				if ( $polish_language_version ) {
					$action_image_url = 'shopmagic-for-woocommerce/assets/images/action-pl.png';
				} else {
					$action_image_url = 'shopmagic-for-woocommerce/assets/images/action.png';
				}
				?>
				<img class="image" width="475" height="140" src="<?php echo esc_url( plugins_url( $action_image_url ) ); ?>" alt="<?php esc_attr_e( 'Action setter for automation with Send Email selected', 'shopmagic-for-woocommerce' ); ?>>">
			</li>
		</ol>
		<p>
			<b><?php esc_html_e( 'You Got It!', 'shopmagic-for-woocommerce' ); ?></b>
			<?php esc_html_e( 'After you hit "Publish" your automated emails will start flying to your customers\' inboxes.', 'shopmagic-for-woocommerce' ); ?>
		</p>
	</div>
	<div class="about__section is-fullwidth margin-y-2">
		<iframe width="1050" height="560" src="https://www.youtube-nocookie.com/embed/UIBnaT_peHc?controls=0"
				title="YouTube video player" frameborder="0"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
				allowfullscreen></iframe>
	</div>

	<?php if ( ! $this->is_pro_active ) : ?>
		<div class="has-1-columns">
			<h2><?php esc_html_e( 'Achieve more with ShopMagic PRO', 'shopmagic-for-woocommerce' ); ?></h2>

			<p class="lead-description"><?php esc_html_e( 'Sell more in less time and increase your conversion rate with ShopMagic PRO. These add-ons will get you more happy and loyal customers:', 'shopmagic-for-woocommerce' ); ?></p>
		</div>

		<div class="about__section has-3-columns is-fullwidth">
			<div class="column is-edge-to-edge">
				<i class="dashicons sm-dashicon-large dashicons-clock"></i>
				<h3><?php esc_html_e( 'Delayed Actions', 'shopmagic-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Delay your emails by minutes, hours, days or weeks after the original event. Schedule your emails to a precise future date.', 'shopmagic-for-woocommerce' ); ?></p>
			</div>

			<div class="column is-edge-to-edge">
				<i class="dashicons sm-dashicon-large dashicons-admin-comments"></i>
				<h3><?php esc_html_e( 'Review Requests', 'shopmagic-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Add review requests with direct links to purchased products. Create automated responses for customers who will review your products.', 'shopmagic-for-woocommerce' ); ?></p>
			</div>

			<div class="column is-edge-to-edge">
				<i class="dashicons sm-dashicon-large dashicons-admin-tools"></i>
				<h3><?php esc_html_e( 'Manual Actions', 'shopmagic-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Execute manual actions whenever you need them. Send one-time personalized emails to selected group of customers directly from your WordPress dashboard.', 'shopmagic-for-woocommerce' ); ?></p>
			</div>
		</div>

		<div class="about__section has-3-columns is-fullwidth">
			<div class="column is-edge-to-edge">
				<i class="dashicons sm-dashicon-large dashicons-tag"></i>
				<h3><?php esc_html_e( 'Customer Coupons', 'shopmagic-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Adds ability to create personalized coupon codes for customers and send them automatically.', 'shopmagic-for-woocommerce' ); ?></p>
			</div>

			<div class="column is-edge-to-edge">
				<i class="dashicons sm-dashicon-large dashicons-filter"></i>
				<h3><?php esc_html_e( 'Advanced Filters', 'shopmagic-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Segment your customers. Target a selected group of customers with emails, coupons or discounts. There are more than 20 filters available for you!', 'shopmagic-for-woocommerce' ); ?></p>
			</div>

			<div class="column is-edge-to-edge">
				<i class="dashicons sm-dashicon-large dashicons-cart"></i>
				<h3><?php esc_html_e( 'Abandoned Carts', 'shopmagic-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Recover abandoned carts and increase your sales. Create a set of follow-up email reminders. Ready to use, for guests and registered customers.', 'shopmagic-for-woocommerce' ); ?></p>
			</div>
		</div>

		<?php
		if ( $polish_language_version ) {
			$url = 'https://www.wpdesk.pl/sklep/shopmagic/';
		} else {
			$url = 'https://shopmagic.app/pricing/';
		}
		?>
		<br>
		<div class="aligncenter margin-y-2">
			<a class="button button-primary button-hero sm-button-large" href="<?php echo esc_url( $url ); ?>?utm_source=welcome-screen&utm_medium=button&utm_campaign=shopmagic-welcome" target="blank" class="proButton"><?php esc_html_e( 'Get ShopMagic PRO', 'shopmagic-for-woocommerce' ); ?></a>
		</div>
	<?php endif; ?>
</div>

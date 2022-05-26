<?php
/**
 * @var WPDesk\ShopMagic\Action\Action[] $available_actions
 * @var \WP_Post $post
 * @var \WPDesk\ShopMagic\Action\ActionFactory2 $action_factory
 */

use WPDesk\ShopMagic\Action\HasCustomAdminHeader;
?>

<div class="sm-actions-wrap">
	<div class="postbox action-form-table" id="action-area-stub">
		<button type="button" class="handlediv button-link" aria-expanded="false">
			<span class="screen-reader-text">
				<?php esc_html_e( 'Toggle panel: Action', 'shopmagic-for-woocommerce' ); ?>
			</span>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>

		<div class="error-icon"><span class="dashicons dashicons-warning"></span>
			<div class="error-icon-tooltip">
				<?php esc_html_e( 'Network connection error', 'shopmagic-for-woocommerce' ); ?>
			</div>
		</div>
		<div class="spinner"></div>

		<h2>
			<span class="action_wrap">
				<?php esc_html_e( 'Action #', 'shopmagic-for-woocommerce' ); ?>
				<span class="action_number">0</span>:
			</span>

			<select class="action_main_select" name="_action_stub" id="_action_stub">
				<option value=""><?php esc_html_e( 'Select...', 'shopmagic-for-woocommerce' ); ?></option>
				<?php foreach ( $available_actions as $slug => $display_action ) { ?>
					<option value="<?php echo esc_attr( $slug ); ?> "><?php echo esc_html( $display_action->get_name() ); ?></option>
				<?php } ?>
			</select>

			<span class="action_title" id="_action_title_stub"></span>
		</h2>

		<div class="inside">
			<table class="shopmagic-table">
				<tr class="shopmagic-field">
					<td class="shopmagic-label">
						<label for="action_title_stub" id="action_title_label_stub">
							<?php esc_html_e( 'Description', 'shopmagic-for-woocommerce' ); ?>
						</label>

						<p class="content">
							<?php esc_html_e( 'Description for your reference', 'shopmagic-for-woocommerce' ); ?>
						</p>
					</td>

					<td class="shopmagic-input">
						<input type="text" name="action_title_stub" id="action_title_stub"/>
					</td>
				</tr>

				<tr>
					<td id="action-settings-area_occ" class="config-area" colspan="2">
						<?php
						/**
						 * @ignore Only for delayed actions plugin.
						 * @deprecated 2.34.0 Use general shopmagic/core/action/config/before hook
						 */
						do_action( 'shopmagic_automation_action_settings', 'occ', [] ); // 'occ' like key

						do_action( 'shopmagic/core/action/config/before', 'occ', new \ShopMagicVendor\WPDesk\Persistence\Adapter\ArrayContainer() );
						?>
					</td>
				</tr>

				<tr>
					<td class="config-area" colspan="2">
						<table id="action-config-area-stub"></table>
					</td>
				</tr>

				<tr>
					<td class="shopmagic-action-footer" colspan="2">
						<button class="remove-action button button-large" onclick="removeAction(this)" type="button">
							<?php esc_html_e( 'Remove Action', 'shopmagic-for-woocommerce' ); ?>
						</button>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<?php
	// For each stored action we create action area. This is duplicate for template code above.
	// Maybe better is make template file for this area and load it via some kind of templating script.
	$actions_data = get_post_meta( $post->ID, \WPDesk\ShopMagic\Admin\Automation\Metabox\ActionMetabox::META_KEY_ACTIONS, true );
	if ( is_array( $actions_data ) ) {
		foreach ( array_values( $actions_data ) as $key => $single_action_data ) {
			$sm_action = $action_factory->get_action( $single_action_data['_action'] );
			?>
			<div class="postbox action-form-table closed" id="action-area-<?php echo esc_attr( $key ); ?>">
				<button type="button" class="handlediv button-link" aria-expanded="false">
					<span class="screen-reader-text">Toggle panel: Action</span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>

				<div class="error-icon"><span class="dashicons dashicons-warning"></span>
					<div class="error-icon-tooltip">
						<?php esc_html_e( 'Network connection error', 'shopmagic-for-woocommerce' ); ?>
					</div>
				</div>
				<div class="spinner"></div>

				<h2 class="hndle ui-sortable-handle">
					<span class="action_wrap"><?php esc_html_e( 'Action #', 'shopmagic-for-woocommerce' ); ?>
						<span class="action_number"><?php echo esc_attr( (string) ( $key + 1 ) ); ?></span>:
					</span>

					<select class="action_main_select tips" name="actions[<?php echo esc_attr( $key ); ?>][_action]" id="_actions_<?php echo esc_attr( $key ); ?>_action"
							data-tip=" <?php esc_attr_e( 'In order to change action, remove the old one and a new one.', 'shopmagic-for-woocommerce' ); ?>"
							disabled>
						<option value=""><?php esc_html_e( 'Select...', 'shopmagic-for-woocommerce' ); ?></option>
						<?php foreach ( $available_actions as $slug => $display_action ) { ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $single_action_data['_action'] ); ?> >
								<?php echo esc_html( $display_action->get_name() ); ?>
							</option>
						<?php } ?>
					</select>

					<input type="hidden" name="actions[<?php echo esc_attr( $key ); ?>][_action]" value="<?php echo esc_attr( $single_action_data['_action'] ); ?>">
					<span class="action_title" id="_action_title_<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $single_action_data['_action_title'] ); ?></span>
					<?php if ( $sm_action instanceof HasCustomAdminHeader ) : ?>
						<?php echo $sm_action->render_header( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</h2>

				<div class="inside">
					<table class="shopmagic-table">
						<tr class="shopmagic-field">
							<td class="shopmagic-label">
								<label for="action_title_input_<?php echo esc_attr( $key ); ?>">
									<?php esc_html_e( 'Description', 'shopmagic-for-woocommerce' ); ?>
								</label>

								<p class="content">
									<?php esc_html_e( 'Description for your reference', 'shopmagic-for-woocommerce' ); ?>
								</p>
							</td>

							<td class="shopmagic-input">
								<input type="text" class="action_title_input"
										name="actions[<?php echo esc_attr( $key ); ?>][_action_title]"
										id="action_title_input_<?php echo esc_attr( $key ); ?>"
										value="<?php echo esc_attr( $single_action_data['_action_title'] ); ?>">
							</td>
						</tr>

						<tr>
							<td id="action-settings-area_<?php echo esc_attr( $key ); ?>" class="config-area" colspan="2">
								<?php
								$notice_name    = 'action-delay';
								$time_dismissed = get_user_meta( get_current_user_id(), 'shopmagic_ignore_notice_' . $notice_name, true );
								$show_after     = ( $time_dismissed ) ? $time_dismissed + MONTH_IN_SECONDS : ''; // Will show again after 1 month.
								if ( ! is_plugin_active( 'shopmagic-delayed-actions/shopmagic-delayed-actions.php' ) && time() > $show_after ) :
									$url = ( get_locale() === 'pl_PL' ) ? // phpcs:ignore Squiz.ControlStructures.InlineIfDeclaration.NotSingleLine
										'https://www.wpdesk.pl/sklep/shopmagic/?utm_source=delayed-actions&utm_medium=notice&utm_campaign=e08' :
										'https://shopmagic.app/products/shopmagic-delayed-actions/?utm_source=delayed-actions&utm_medium=notice&utm_campaign=e08';
									?>
									<table id="sm_id_<?php echo esc_attr( $key ); ?>" class="shopmagic-table">
										<tbody>
										<tr class="shopmagic-field">
											<td class="shopmagic-label">
												<label for="action_delay_checkbox_<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Delay', 'shopmagic-for-woocommerce' ); ?></label>
											</td>
											<td class="shopmagic-input">
												<section class="notice notice-info is-dismissible" data-notice-name="<?php echo esc_attr( $notice_name ); ?>">
													<p><strong>
														<?php esc_html_e( 'Did you know that you can delay emails by minutes, hours, days or even weeks?', 'shopmagic-for-woocommerce' ); ?>
														<a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php esc_html_e( 'Check out our Delayed Actions add-on', 'shopmagic-for-woocommerce' ); ?> &rarr;</a>
													</strong></p>
												</section></td>
										</tr>
										</tbody>
									</table>
								<?php endif; ?>
								<?php
								/** @ignore Only for delayed actions plugin. */
								do_action( 'shopmagic_automation_action_settings', $key, $single_action_data );

								/**
								 * External plugin can hook into action settings and add own fields to the editor area.
								 *
								 * @param string $prefix
								 * @param \Psr\Container\ContainerInterface $single_action_data
								 */
								do_action( 'shopmagic/core/action/config/before', "actions[$key]", new \ShopMagicVendor\WPDesk\Persistence\Adapter\ArrayContainer( $single_action_data ) );
								?>
							</td>
						</tr>

						<tr>
							<td class="config-area" colspan="2">
								<table id="action-config-area-<?php echo esc_attr( $key ); ?>"></table>
							</td>
						</tr>

						<tr>
							<td class="shopmagic-action-footer" colspan="2">
								<button class="button button-large" onclick="removeAction(this)" type="button">
									<?php esc_html_e( 'Remove Action', 'shopmagic-for-woocommerce' ); ?>
								</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php
		}
	}
	?>
	<div class="shopmagic-tfoot">
		<div class="shopmagic-fr">
			<button id="add-new-action" class="button button-primary button-large" onclick="addNewAction()" type="button">
				<?php esc_html_e( '+ New Action', 'shopmagic-for-woocommerce' ); ?>
			</button>
		</div>
	</div>
</div>

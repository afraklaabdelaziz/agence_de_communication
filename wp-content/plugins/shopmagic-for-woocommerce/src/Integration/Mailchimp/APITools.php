<?php

namespace WPDesk\ShopMagic\Integration\Mailchimp;

use Psr\Log\LoggerAwareTrait;
use WPDesk\ShopMagic\Customer\UserAsCustomer;

/**
 * MailChimp Tools for ShopMagic
 *
 * @since   1.0.0
 */
class APITools extends MailChimp {
	use LoggerAwareTrait;
	function __construct( $api_key, $verify_ssl = false ) {
		parent::__construct( $api_key, $verify_ssl );
	}

	/**
	 * @param \WP_User $user
	 * @param string $mailchimp_list_id
	 * @param string $mailchimp_doubleoptin
	 *
	 * @return bool
	 * @deprecated 2.34.1 We no longer rely on WP_User directly
	 */
	public function add_member_from_user( \WP_User $user, $mailchimp_list_id, $mailchimp_doubleoptin ) {
		$basic_params = $this->prepare_basic_params( $user->user_email, $user->user_firstname, $user->user_lastname, $mailchimp_doubleoptin );

		return $this->add_to_list( $basic_params, $mailchimp_list_id );
	}

	public function add_member_from_user_customer( UserAsCustomer $user, string $mailchimp_list_id, string $mailchimp_doubleoptin ): bool {
		$basic_params = $this->prepare_basic_params( $user->get_email(), $user->get_first_name(), $user->get_last_name(), $mailchimp_doubleoptin );

		return $this->add_to_list( $basic_params, $mailchimp_list_id );
	}

	/**
	 * @param string $email
	 * @param string $mailchimp_list_id
	 * @param string $mailchimp_doubleoptin
	 *
	 * @return bool
	 */
	public function add_member_from_email( $email, $mailchimp_list_id, $mailchimp_doubleoptin ) {
		$basic_params = $this->prepare_basic_params( $email, '', '', $mailchimp_doubleoptin );

		return $this->add_to_list( $basic_params, $mailchimp_list_id );
	}

	/**
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $double_optin
	 *
	 * @return array
	 */
	private function prepare_basic_params( $email, $first_name, $last_name, $double_optin ) {
		if ( in_array( strtolower( $double_optin ), [ 'on', 'yes' ] ) ) {
			$member_status = 'pending';
		} else {
			$member_status = 'subscribed';
		}

		return [
			'email_address' => $email,
			'status'        => $member_status,
			'merge_fields'  => [
				'FNAME' => $first_name,
				'LNAME' => $last_name,
			],
		];
	}

	/**
	 * @param array $params
	 * @param string $list_id
	 *
	 * @return bool true on success
	 */
	private function add_to_list( array $params, $list_id ) {
		$response = $this->post(
			'lists/' . $list_id . '/members',
			$params
		);

		if ( $this->logger ) {
			$this->logger->debug( 'Response from MailChimp: ' . json_encode( [ 'response' => $response ] ) );
		}

		return is_array( $response );
	}

	/**
	 * @param \WC_Abstract_Order|\WC_Order_Refund $order
	 * @param string $mailchimp_list_id
	 * @param string $doubleoptin
	 *
	 * @return bool
	 */
	public function add_member_from_order( $order, $mailchimp_list_id, $doubleoptin ) {
		if ( $mailchimp_list_id === '' || $this->getApiKey() === false ) {
			return false;
		}

		// Get further information settings.
		$mailchimp_further_information = [
			'LNAME'   => get_option( 'wc_settings_tab_mailchimp_info_lname', false ),
			'ADDRESS' => get_option( 'wc_settings_tab_mailchimp_info_address', false ),
			'CITY'    => get_option( 'wc_settings_tab_mailchimp_info_city', false ),
			'STATE'   => get_option( 'wc_settings_tab_mailchimp_info_state', false ),
			'COUNTRY' => get_option( 'wc_settings_tab_mailchimp_info_country', false ),
		];

		$billing_email = $order->billing_email;
		$billing_fname = $order->billing_first_name;

		// Last name depends on the further information settings.
		$billing_lname = ( $mailchimp_further_information['LNAME'] === 'yes' ? $order->billing_last_name : '' );

		$billing_address = $order->billing_address_1 . ' ' . $order->billing_address_2;
		$billing_city    = $order->billing_city;
		$billing_state   = $order->billing_state;
		$billing_country = $order->billing_country;

		if ( ! empty( $billing_email ) && ! filter_var( $billing_email, FILTER_VALIDATE_EMAIL ) === false ) {
			$mailchimp_add_member_params = $this->prepare_basic_params( $billing_email, $billing_fname, $billing_lname, $doubleoptin );

			// Look for new 'merge-fields' and add them if necessary
			// Get new merge-fields 'TAG'=>'name'.
			$mailchimp_new_mergefields = [
				'ADDRESS' => 'Address',
				'CITY'    => 'City',
				'STATE'   => 'State',
				'COUNTRY' => 'Country',
			];

			foreach ( $mailchimp_new_mergefields as $tag => $name ) {

				// If information checked checked on the settings.
				if ( $mailchimp_further_information[ $tag ] === 'yes' ) {
					$mailchimp_add_mergefield_params = [
						'tag'  => $tag,
						'name' => $name,
						'type' => 'text',
					];

					// MailChimp API Call for adding new merge-field.
					$this->add_merge_field( $mailchimp_list_id, $mailchimp_add_mergefield_params );

					if ( ! $this->success() ) {
						error_log( $this->getLastError() );
					}
					// Change params depending further information settings ( from WC settings ShopMagic ).

					switch ( $tag ) {
						case 'ADDRESS':
							$mailchimp_add_member_params['merge_fields']['ADDRESS'] = $billing_address;
							break;

						case 'CITY':
							$mailchimp_add_member_params['merge_fields']['CITY'] = $billing_city;
							break;

						case 'STATE':
							$mailchimp_add_member_params['merge_fields']['STATE'] = $billing_state;
							break;

						case 'COUNTRY':
							$mailchimp_add_member_params['merge_fields']['COUNTRY'] = $billing_country;
							break;
					}
				}
			}

			$result = $this->add_to_list( $mailchimp_add_member_params, $mailchimp_list_id );

			if ( ! $this->success() ) {
				error_log( $this->getLastError() );
			}

			return $result;
		}
	}

	private function add_merge_field( $mailchimp_list_id, $params ) {

		// MailChimp API Call for adding new merge-field.
		$this->post(
			'lists/' . $mailchimp_list_id . '/merge-fields',
			$params
		);
	}

	/**
	 * Extract the lists names and id to be used on options for the select element 'List name'
	 *
	 * @return string[]
	 */
	public function get_all_lists_options() {
		// Get the list of lists.
		$lists_options = [
			'0' => __( 'Select...', 'shopmagic-for-woocommerce' ),
		];
		$lists         = $this->get( 'lists?count=1000' );

		if ( $this->success() ) {
			if ( count( $lists['lists'] ) > 0 ) {
				// If one list or more.
				foreach ( $lists['lists'] as $key => $list_obj ) {
					$lists_options[ $list_obj['id'] ] = $list_obj['name'] . ' [' . $list_obj['id'] . ']';
				}
			} else {
				// If no lists yet or an error.
				$lists_options = [
					'0' => __( 'No lists are set yet!', 'shopmagic-for-woocommerce' ),
				];
			}
		} else {
			// If an error is there.
			$lists_options = [
				'0' => __( 'Please make sure to provide Mailchimp API key!', 'shopmagic-for-woocommerce' ),
			];
		}

		return $lists_options;
	}
}

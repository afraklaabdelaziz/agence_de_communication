<?php

namespace WPDesk\ShopMagic\AutomationOutcome;

use DateTimeImmutable;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Database\Abstraction\DAO\Item;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Representation of singular Outcome.
 */
final class Outcome implements Item {
	const SLUG = 'shopmagic_single_outcome';

	/** @var string */
	private $execution_id;

	/** @var \DateTimeImmutable */
	private $updated;

	/** @var bool */
	private $success;

	/** @var int */
	private $automation_id;

	/** @var int */
	private $id;

	/** @var int */
	private $action_index;

	/** @var string */
	private $action_name;

	/** @var Customer */
	private $customer;

	/** @var string */
	private $customer_email;

	/** @var bool */
	private $finished;

	/** @var DateTimeImmutable */
	private $created;

	/** @var string */
	private $automation_name;

	public function __construct(
		int $id,
		string $execution_id,
		int $automation_id,
		string $automation_name,
		int $action_index,
		string $action_name,
		Customer $customer,
		string $customer_email,
		bool $success,
		bool $finished,
		\DateTimeImmutable $created,
		\DateTimeImmutable $updated
	) {
		$this->id              = $id;
		$this->execution_id    = $execution_id;
		$this->automation_id   = $automation_id;
		$this->automation_name = $automation_name;
		$this->action_index    = $action_index;
		$this->action_name     = $action_name;
		$this->customer        = $customer;
		$this->customer_email  = $customer_email;
		$this->success         = $success;
		$this->finished        = $finished;
		$this->created         = $created;
		$this->updated         = $updated;
	}

	public function get_action_index(): int {
		return $this->action_index;
	}

	public function get_action_name(): string {
		return $this->action_name;
	}

	public function get_automation_id(): int {
		return $this->automation_id;
	}

	public function get_automation_name(): string {
		return $this->automation_name;
	}

	public function get_created(): DateTimeImmutable {
		return $this->created;
	}

	public function get_customer_email(): string {
		return $this->customer_email;
	}

	public function get_customer(): Customer {
		return $this->customer;
	}

	public function is_finished(): bool {
		return $this->finished;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_execution_id(): string {
		return $this->execution_id;
	}

	public function get_success(): bool {
		return $this->success;
	}

	public function get_changed_fields(): array {
		return [];
	}

	public function has_changed(): bool {
		return false;
	}

	public function set_last_inserted_id( int $id ) {
	}

	public function get_updated(): DateTimeImmutable {
		return $this->updated;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function normalize(): array {
		$vars = get_object_vars( $this );

		$vars['created'] = WordPressFormatHelper::date_as_mysql( $this->created );
		$vars['updated'] = WordPressFormatHelper::date_as_mysql( $this->updated );

		if ( $this->customer->is_guest() ) {
			$vars['guest_id'] = CustomerFactory::convert_customer_guest_id_to_number( (string) $this->customer->get_id() );
			$vars['user_id']  = null;
		} else {
			$vars['user_id']  = $this->customer->get_id();
			$vars['guest_id'] = null;
		}

		return $vars;
	}

	public function get_url(): string {
		return AutomationPostType::get_url() . '&' . http_build_query(
			[
				'page' => self::SLUG,
				'id'   => $this->id,
			]
		);
	}
}

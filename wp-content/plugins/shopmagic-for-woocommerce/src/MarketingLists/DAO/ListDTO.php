<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\MarketingLists\DAO;

use DateTimeImmutable;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

final class ListDTO implements \WPDesk\ShopMagic\Database\Abstraction\DAO\Item {

	/** @var int */
	private $id;

	/** @var int */
	private $list_id;

	/** @var string */
	private $email;

	/** @var bool */
	private $type;

	/** @var bool */
	private $active;

	/** @var DateTimeImmutable */
	private $updated;

	/** @var DateTimeImmutable */
	private $created;

	/** @var bool */
	private $changed = false;

	public function __construct(
		int $id,
		int $list_id,
		string $email,
		bool $active,
		bool $type,
		DateTimeImmutable $created,
		DateTimeImmutable $updated
	) {
		$this->id      = $id;
		$this->list_id = $list_id;
		$this->email   = $email;
		$this->active  = $active;
		$this->type    = $type;
		$this->created = $created;
		$this->updated = $updated;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_email(): string {
		return $this->email;
	}

	public function get_type(): bool {
		return $this->type;
	}

	public function is_active(): bool {
		return $this->active;
	}

	public function get_list_id(): int {
		return $this->list_id;
	}

	public function get_updated(): DateTimeImmutable {
		return $this->updated;
	}

	public function get_created(): DateTimeImmutable {
		return $this->created;
	}

	public function get_changed_fields(): array {
		return [];
	}

	public function has_changed(): bool {
		return $this->id === 0 || $this->changed;
	}

	/** @return void */
	public function set_active( bool $active ) {
		$this->changed = $this->active !== $active;
		$this->updated = new DateTimeImmutable( 'now', wp_timezone() );
		$this->active  = $active;
	}

	public function set_last_inserted_id( int $id ) {
		$this->id = $id;
	}

	public function normalize(): array {
		$vars = get_object_vars( $this );

		$vars['updated'] = WordPressFormatHelper::datetime_as_mysql( $this->updated );
		$vars['created'] = WordPressFormatHelper::datetime_as_mysql( $this->created );

		return $vars;
	}
}

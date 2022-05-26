<?php


namespace WPDesk\ShopMagic\Guest;

/**
 * Guest DTO. Guest from given data.
 *
 * @package WPDesk\ShopMagic\Business
 */
class Guest {

	/** @var int|null */
	private $id;

	/** @var string */
	private $email;

	/** @var \DateTimeInterface */
	private $created;

	/** @var \DateTimeInterface */
	private $updated;

	/** @var string */
	private $tracking_key;

	/** @var string[] */
	private $metadata;

	/**
	 * Guest constructor.
	 *
	 * @param int|null $id Null when no yet synced with db.
	 * @param string $email
	 * @param string $tracking_key
	 * @param \DateTimeInterface $created
	 * @param \DateTimeInterface $updated
	 * @param string[] $metadata
	 */
	public function __construct( $id, $email, $tracking_key, \DateTimeInterface $created, \DateTimeInterface $updated, array $metadata ) {
		$this->id           = $id;
		$this->email        = $email;
		$this->tracking_key = $tracking_key;
		$this->created      = $created;
		$this->updated      = $updated;
		$this->metadata     = $metadata;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Is in the db.
	 *
	 * @return bool
	 */
	public function is_saved() {
		return $this->id !== null;
	}

	/**
	 * @param $id
	 */
	public function sync_with_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function get_tracking_key() {
		return $this->tracking_key;
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_meta_value( $name ) {
		return isset( $this->metadata[ $name ] ) ? $this->metadata[ $name ] : '';
	}

	public function set_meta_value( string $name, $value ) {
		return $this->metadata[ $name ] = $value;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has_meta_value( $name ) {
		return isset( $this->metadata[ $name ] );
	}

	/**
	 * @return string[]
	 */
	public function get_all_metadata() {
		return $this->metadata;
	}

	/**
	 * @return \DateTimeInterface
	 */
	public function get_created() {
		return $this->created;
	}

	/**
	 * @return \DateTimeInterface
	 */
	public function get_updated() {
		return $this->updated;
	}
}

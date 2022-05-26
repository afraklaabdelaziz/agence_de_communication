<?php

namespace WPDesk\ShopMagic\Database;

use ShopMagicVendor\WPDesk\Logger\BasicLoggerFactory;
use WPDesk\ShopMagic\LoggerFactory;

/**
 * Implementation for standard where/order/limit data parsing.
 *
 * @package WPDesk\ShopMagic\Database
 */
trait RepositoryOrderingTrait {
	/**
	 * @param array<string, string> $order Order clauses in format [ field => asc|desc, field2 => asc|desc ]
	 *
	 * @return string
	 */
	private function order_array_to_sql( array $order ) {
		if ( empty( $order ) ) {
			return '';
		}
		$order_clauses = [];
		foreach ( $order as $key => $val ) {
			$order_clauses[] = "{$key} {$val}";
		}

		return ' ORDER BY ' . $this->sanitize_sql_orderby( implode( ',', $order_clauses ) );
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 *
	 * @see sanitize_sql_orderby()
	 */
	private function sanitize_sql_orderby( $value ) {
		if ( preg_match( '/^\s*(([a-z0-9_.]+|`[a-z0-9_.]+`)(\s+(ASC|DESC))?\s*(,\s*(?=[a-z0-9_`.])|$))+$/i', $value ) || preg_match( '/^\s*RAND\(\s*\)\s*$/i', $value ) ) {
			return $value;
		}

		LoggerFactory::get_logger()->alert( "Invalid ORDER BY sanitization for value: {$value}" );

		return '1=1';
	}

	private function limit_offset_to_sql( $limit, $offset ) {
		return " LIMIT {$limit} OFFSET {$offset}";
	}

	/**
	 * @param array $where
	 *
	 * @return string
	 */
	private function where_array_to_sql( array $where ) {
		if ( empty( $where ) ) {
			return ' 1 = 1 ';
		}
		$where_clauses = [];
		$where_values  = [];
		foreach ( $where as $key => $val ) {
			if ( is_array( $val ) ) {
				$where_clauses[] = "{$val['field']} {$val['condition']} %s";
				$where_values[]  = $val['value'];
			} else {
				$where_clauses[] = "{$key} = %s";
				$where_values[]  = $val;
			}
		}

		if ( property_exists( $this, 'wpdb' ) ) { // fallback when property is used.
			return $this->wpdb->prepare( ' ' . implode( ' AND ', $where_clauses ), $where_values );
		}

		return $this->get_wpdb()->prepare( ' ' . implode( ' AND ', $where_clauses ), $where_values );
	}
}

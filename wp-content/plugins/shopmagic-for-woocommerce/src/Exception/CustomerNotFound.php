<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Exception;

use RuntimeException;

final class CustomerNotFound extends RuntimeException implements ShopMagicException {}

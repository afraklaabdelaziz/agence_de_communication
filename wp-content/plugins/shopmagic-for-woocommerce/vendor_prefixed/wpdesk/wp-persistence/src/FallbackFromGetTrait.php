<?php

namespace ShopMagicVendor\WPDesk\Persistence;

use Psr\Container\NotFoundExceptionInterface;
trait FallbackFromGetTrait
{
    public function get_fallback(string $id, $fallback = null)
    {
        try {
            return $this->get($id);
        } catch (\Psr\Container\NotFoundExceptionInterface $e) {
            return $fallback;
        }
    }
}

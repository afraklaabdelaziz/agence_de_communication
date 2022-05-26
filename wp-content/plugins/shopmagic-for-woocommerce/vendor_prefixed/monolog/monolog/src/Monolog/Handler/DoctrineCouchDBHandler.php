<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ShopMagicVendor\Monolog\Handler;

use ShopMagicVendor\Monolog\Logger;
use ShopMagicVendor\Monolog\Formatter\NormalizerFormatter;
use ShopMagicVendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \ShopMagicVendor\Monolog\Handler\AbstractProcessingHandler
{
    private $client;
    public function __construct(\ShopMagicVendor\Doctrine\CouchDB\CouchDBClient $client, $level = \ShopMagicVendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new \ShopMagicVendor\Monolog\Formatter\NormalizerFormatter();
    }
}

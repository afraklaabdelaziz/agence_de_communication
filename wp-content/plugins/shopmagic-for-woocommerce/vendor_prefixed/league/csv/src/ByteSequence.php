<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ShopMagicVendor\League\Csv;

/**
 * Defines constants for common BOM sequences.
 */
interface ByteSequence
{
    /**
     *  UTF-8 BOM sequence.
     */
    const BOM_UTF8 = "﻿";
    /**
     * UTF-16 BE BOM sequence.
     */
    const BOM_UTF16_BE = "\xfe\xff";
    /**
     * UTF-16 LE BOM sequence.
     */
    const BOM_UTF16_LE = "\xff\xfe";
    /**
     * UTF-32 BE BOM sequence.
     */
    const BOM_UTF32_BE = "\x00\x00\xfe\xff";
    /**
     * UTF-32 LE BOM sequence.
     */
    const BOM_UTF32_LE = "\xff\xfe\x00\x00";
}

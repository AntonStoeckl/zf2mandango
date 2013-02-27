<?php
/**
 * ZF2 Mandango
 *
 * @link      hhttps://github.com/AntonStoeckl/zf2mandango for the canonical source repository
 * @copyright Copyright (c) 2013 Anton Stöckl
 * @license   MIT License - see bundled LICENSE file
 */

namespace Zf2mandango\Mondator\Config;

/**
 * ParserFactoryInterface
 *
 * An interface for a factory for Mondator config file parsers.
 */
interface ParserFactoryInterface
{
    /**
     * @param string $type
     * @return callable|boolean
     */
    public function getParser($type);
}

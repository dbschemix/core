<?php

declare(strict_types=1);

namespace dbschemix\core;

use dbschemix\core\exception\ConfigurationException;
use dbschemix\core\internal\template\Factory;
use dbschemix\core\template\FactoryInterface;

/**
 * @api
 */
final readonly class Config
{
    /**
     * @param non-empty-string $table
     * @throws ConfigurationException
     */
    public function __construct(
        public string $table = 'migration',
        public FactoryInterface $templFactory = new Factory(),
    ) {
        if (preg_match('/^\w+$/', $table) !== 1) {
            throw new ConfigurationException(
                "Table name '$table' contains invalid characters."
            );
        }
    }
}

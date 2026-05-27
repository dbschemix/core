<?php

declare(strict_types=1);

namespace dbschemix\core\connection;

use dbschemix\core\command\CommandInterface;
use dbschemix\core\exception\ConnectionException;
use dbschemix\core\Config;

/**
 * @api
 */
interface DriverInterface
{
    /**
     * @return non-empty-lowercase-string
     */
    public function getName(): string;

    /**
     * @return non-empty-lowercase-string
     */
    public function getSourceName(): string;

    /**
     * @return non-empty-string
     */
    public function getSetupPath(): string;

    /**
     * @throws ConnectionException
     */
    public function makeCommand(Config $config): CommandInterface;
}

<?php

declare(strict_types=1);

namespace dbschemix\core;

use dbschemix\core\exception\ActionException;
use dbschemix\core\exception\ConfigurationException;
use dbschemix\core\exception\ConnectionException;
use dbschemix\core\exception\InitializationException;

interface MigratorInterface
{
    /**
     * @throws ActionException
     * @throws ConfigurationException If the driver is not implemented
     * @throws ConnectionException
     */
    public function init(): void;

    /**
     * @throws ConfigurationException
     */
    public function create(InputOptions $args = new InputOptions()): void;

    /**
     * @throws ActionException
     * @throws ConfigurationException If the driver is not implemented
     * @throws ConnectionException
     * @throws InitializationException initialization step is required
     */
    public function up(InputOptions $args = new InputOptions()): void;

    /**
     * @throws ActionException
     * @throws ConfigurationException If the driver is not implemented
     * @throws ConnectionException
     * @throws InitializationException initialization step is required
     */
    public function down(InputOptions $args = new InputOptions()): void;

    /**
     * @throws ActionException
     * @throws ConfigurationException If the driver is not implemented
     * @throws ConnectionException
     */
    public function fixture(InputOptions $args = new InputOptions()): void;

    /**
     * @throws ActionException
     * @throws ConfigurationException If the driver is not implemented
     * @throws ConnectionException
     * @throws InitializationException initialization step is required
     */
    public function redo(InputOptions $args = new InputOptions()): void;

    /**
     * @throws ActionException
     * @throws ConfigurationException If the driver is not implemented
     * @throws ConnectionException
     * @throws InitializationException initialization step is required
     */
    public function verify(InputOptions $args = new InputOptions()): void;
}

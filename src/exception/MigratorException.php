<?php

declare(strict_types=1);

namespace dbschemix\core\exception;

use RuntimeException;

/**
 * Root of the dbschemix\core exception hierarchy.
 *
 * All exceptions thrown by the migrator descend from this class. Consumers
 * may catch this base type for generic handling, or catch a concrete subtype
 * (ActionException, ConfigurationException, ConnectionException,
 * InitializationException, PrepareException) for targeted handling. Driver
 * implementations in downstream packages may throw any subtype of this
 * hierarchy.
 *
 * @api
 */
abstract class MigratorException extends RuntimeException
{
}

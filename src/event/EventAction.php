<?php

declare(strict_types=1);

namespace dbschemix\core\event;

/**
 * @psalm-internal dbschemix\core
 */
enum EventAction
{
    case up;
    case down;
    case repeatable;
    case fixture;
    case initialization;
}

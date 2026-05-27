<?php

declare(strict_types=1);

namespace dbschemix\core\internal\filesystem;

/**
 * @psalm-internal dbschemix\core\internal\filesystem
 */
enum ActionType: string
{
    case UP = 'up';
    case DOWN = 'down';
}

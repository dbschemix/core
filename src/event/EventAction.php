<?php

declare(strict_types=1);

namespace dbschemix\core\event;

enum EventAction
{
    case up;
    case down;
    case repeatable;
    case fixture;
    case initialization;
}

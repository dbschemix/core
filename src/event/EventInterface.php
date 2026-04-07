<?php

declare(strict_types=1);

namespace dbschemix\core\event;

interface EventInterface
{
    public function getName(): string;

    public function getMessage(): string;
}

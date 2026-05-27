<?php

declare(strict_types=1);

namespace dbschemix\core\event;

/**
 * @api
 */
interface EventInterface
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * @return non-empty-string
     */
    public function getMessage(): string;
}

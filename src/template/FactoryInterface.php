<?php

declare(strict_types=1);

namespace dbschemix\core\template;

/**
 * @api
 */
interface FactoryInterface
{
    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public function makeName(string $name): string;

    public function makeBody(): string;
}

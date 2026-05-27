<?php

declare(strict_types=1);

namespace dbschemix\core\internal\template;

use Override;
use dbschemix\core\template\FactoryInterface;

/**
 * @psalm-internal dbschemix\core
 */
final readonly class Factory implements FactoryInterface
{
    #[Override]
    public function makeName(string $name): string
    {
        return sprintf('%d_%s.sql', gmdate('YmdHi'), $name);
    }

    #[Override]
    public function makeBody(): string
    {
        return <<<CODE_WRAP
        -- @up
        -- SQL CODE
        
        -- @down
        -- SQL CODE
        CODE_WRAP;
    }
}

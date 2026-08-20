<?php

namespace Tests\Concerns;

use Tests\Support\BoostTestSchema;

/**
 * Baut das Push-Schema in der SQLite-In-Memory-DB auf und raeumt es wieder ab.
 */
trait UsesBoostSchema
{
    protected function setUpUsesBoostSchema(): void
    {
        BoostTestSchema::create();
    }

    protected function tearDownUsesBoostSchema(): void
    {
        BoostTestSchema::drop();
    }
}

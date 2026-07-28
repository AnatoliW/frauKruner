<?php

namespace Tests\Concerns;

use Tests\Support\UploadTestSchema;

/**
 * Baut vor jedem Test das Alt-Schema in der SQLite-In-Memory-DB auf
 * und räumt es danach wieder ab.
 *
 * Laravel ruft `setUp<TraitName>()` / `tearDown<TraitName>()` automatisch auf
 * (Illuminate\Foundation\Testing\Concerns\InteractsWithTestCaseLifecycle::setUpTraits).
 */
trait UsesUploadSchema
{
    protected function setUpUsesUploadSchema(): void
    {
        UploadTestSchema::create();
    }

    protected function tearDownUsesUploadSchema(): void
    {
        UploadTestSchema::drop();
    }
}

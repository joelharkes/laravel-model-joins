<?php

declare(strict_types=1);

namespace Joelharkes\LaravelModelJoins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
    }

    public function register()
    {
        Relation::mixin(new RelationMethods);
        Builder::mixin(new JoinsModels);
    }
}

<?php

declare(strict_types=1);

namespace Joelharkes\LaravelModelJoins;

use Illuminate\Database\Eloquent\Builder;
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
    }

    public function register()
    {

        Builder::mixin(new JoinsModels);
    }
}

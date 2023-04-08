<?php

declare(strict_types=1);

namespace Joelharkes\LaravelModelJoins;

class RelationMethods
{
    /**
     * Get the throughParent for the HasManyThrough relationship.
     */
    public function getThroughParent()
    {
        return function () {
            return $this->throughParent;
        };
    }

    /**
     * Get the farParent for the HasManyThrough relationship.
     */
    public function getFarParent()
    {
        return function () {
            return $this->farParent;
        };
    }
}

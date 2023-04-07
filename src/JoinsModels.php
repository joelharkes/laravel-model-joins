<?php

declare(strict_types=1);

namespace Joelharkes\LaravelModelJoins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @mixin Builder
 */
class JoinsModels
{
    public function joinMany()
    {
        /**
         * @param  class-string<Model>|Model|Builder<Model>  $model
         * @param  string  $joinType
         * @param  string|null  $overrideJoinColumnName
         * @return static
         */
        return function ($model, string $joinType = 'inner', ?string $overrideJoinColumnName = null, ?string $tableAlias = null): static {
            /** @var Builder $builder */
            $builder = match (true) {
                is_string($model) => (new $model())->newQuery(),
                $model instanceof Builder => $model,
                $model instanceof Model => $model->newQuery(),
                $model instanceof Relation => $model->getQuery(),
            };

            return $this->joinManyOn($this->getModel(), $builder, $joinType, null, $overrideJoinColumnName, $tableAlias);
        };
    }

    public function joinOne()
    {
        /**
         * @param  class-string|Model|Builder<Model>  $model
         * @param  string  $joinType
         * @param  string|null  $overrideBaseColumn
         * @return static
         */
        return function ($model, string $joinType = 'inner', ?string $overrideBaseColumn = null, ?string $tableAlias = null): static {
            $builder = match (true) {
                is_string($model) => (new $model())->newQuery(),
                $model instanceof Builder => $model,
                $model instanceof Model => $model->newQuery(),
                $model instanceof Relation => $model->getQuery(),
            };

            $this->joinOneOn($this->getModel(), $builder, $joinType, $overrideBaseColumn, null, $tableAlias);

            return $this;
        };
    }

    public function joinManyOn()
    {
        return function (Model $baseModel, Builder $builderToJoin, ?string $joinType = 'inner', ?string $overrideBaseColumnName = null, ?string $overrideJoinColumnName = null, ?string $tableAlias = null): static {
            $modelToJoin = $builderToJoin->getModel();
            $aliasToUse = $tableAlias ? ($modelToJoin->getTable().' as '.$tableAlias) : $modelToJoin->getTable();
            if ($tableAlias) {
                // override table name to properly qualify table names.
                // todo decide if need to reset after join to avoid weird side effects.
                $modelToJoin->setTable($tableAlias);
            }
            $manyJoinColumnName = $overrideJoinColumnName ?? (Str::singular($baseModel->getTable()).'_'.$baseModel->getKeyName());
            $baseColumnName = $overrideBaseColumnName ?? $baseModel->getKeyName();
            $this->join(
                $aliasToUse, fn (JoinClause $join) => $join->on(
                    $modelToJoin->qualifyColumn($manyJoinColumnName),
                    '=',
                    $baseModel->qualifyColumn($baseColumnName),
                )->addNestedWhereQuery($builderToJoin->applyScopes()->getQuery()),
                    type: $joinType
            );

            return $this;
        };
    }

    public function joinOneOn()
    {
        return function (Model $baseModel, Builder $builderToJoin, string $joinType = 'inner', string $overrideBaseColumnName = null, string $overrideJoinColumnName = null, ?string $tableAlias = null): static {
            $modelToJoin = $builderToJoin->getModel();
            $aliasToUse = $tableAlias ? ($modelToJoin->getTable().' as '.$tableAlias) : $modelToJoin->getTable();
            if ($tableAlias) {
                // override table name to properly qualify table names.
                // todo decide if need to reset after join to avoid weird side effects.
                $modelToJoin->setTable($tableAlias);
            }
            $joinColumnName = $overrideBaseColumnName ?? $modelToJoin->getKeyName();
            $baseColumnName = $overrideJoinColumnName ?? (Str::singular($modelToJoin->getTable()).'_'.$modelToJoin->getKeyName());
            $this->join(
                $aliasToUse, fn (JoinClause $join) => $join->on(
                $modelToJoin->qualifyColumn($joinColumnName),
                '=',
                $baseModel->qualifyColumn($baseColumnName),
            )->addNestedWhereQuery($builderToJoin->getQuery()),
                type: $joinType
            );
            $this->applyScopesWith($builderToJoin->getScopes(), $modelToJoin);

            return $this;
        };
    }

    public function joinRelation()
    {
        return function (string $relation, string $joinType = 'inner', bool $aliasAsRelations = false) {
            $relationsToJoin = str($relation)->explode('.');
            $contextQuery = $this;

            foreach ($relationsToJoin as $relationName){
                $relationClass = Relation::noConstraints(fn () => $contextQuery->getModel()->$relationName());
                $queryToJoin = $relationClass->getQuery();
                assert($relationClass instanceof Relation);

                if ($relationClass instanceof HasOneOrMany) {
                    $this->joinManyOn($contextQuery->getModel(), $queryToJoin, $joinType, $relationClass->getQualifiedParentKeyName(), $relationClass->getForeignKeyName(), $aliasAsRelations ? $relationName : null);
                } elseif ($relationClass instanceof BelongsTo) {
                    $this->joinOneOn($contextQuery->getModel(), $queryToJoin, $joinType, null, $relationClass->getForeignKeyName(), $aliasAsRelations ? $relationName : null);
                }

                // The model in this query builder should now be properly aliased for next usage.
                $contextQuery = $queryToJoin;
            }

            return $this;
        };
    }

    public function applyScopesWith()
    {
        /**
         * @param  Scope[]  $scopes
         * @param  Model  $model
         * @return static
         */
        return function (array $scopes, Model $model) {
            foreach ($scopes as $scope) {
                $scope->apply($this, $model);
            }

            return $this;
        };
    }

    public function getScopes()
    {
        /**
         * @return array<Scope>
         */
        return function () {
            return $this->scopes;
        };
    }
}

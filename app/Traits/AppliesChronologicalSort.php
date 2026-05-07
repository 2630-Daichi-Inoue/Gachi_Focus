<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AppliesChronologicalSort
{
    protected function applyChronologicalSort(Builder $q, ?string $sort, string $column, string $ascKey): void
    {
        $q->orderBy($column, $sort === $ascKey ? 'asc' : 'desc')->latest('id');
    }
}

<?php

namespace App\Traits;

use App\Models\History;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;
use ReflectionMethod;

trait TracksHistoryTrait
{
  protected function track($model, $systemlog_id = null)
  {
    $excludedFields = ['updated_at', 'created_at', 'remember_token'];
    $original = $model->getOriginal();
    $changes = collect($model->getDirty())->except($excludedFields);
    $changeLog = [];

    // dd($changeLog);

    if ($changes->isNotEmpty()) {
      $changeLog = collect($changes)->mapWithKeys(function ($newValue, $field) use ($original) {
        return [$field => [
          'old' => $original[$field] ?? null,
          'new' => $newValue,
        ]];
      });
    }

    $relationships = $this->getRelations($model);
    $oldRelationshipValues = session('old_relationship_values');
    foreach ($relationships as $relation) {
      $origVal = [];
      if (isset($oldRelationshipValues[$relation])) {
        $origVal = $oldRelationshipValues[$relation];
      }
      $relationship = $model->$relation();
      if ($relationship instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
        $newVal = $relationship ? ($relationship->pluck('id')->toArray() ?? []) : [];
        if (!empty(array_diff($newVal, $origVal)) || !empty(array_diff($origVal, $newVal))) {
          $changeLog[$relation] = [
            'old' => $origVal ?? null,
            'new' => $newVal ?? null,
          ];
        }
      } elseif ($relationship instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
        $newVal = $relationship ? ($relationship->get()->map(function ($item) {
          return $item->makeHidden(['created_at', 'updated_at'])->toArray();
        })->toArray() ?? []) : [];

        // dd($origVal, $newVal);

        if ($newVal === $origVal) {
        } else {
          $changeLog[$relation] = [
            'old' => $origVal ?? null,
            'new' => $newVal ?? null,
          ];
        }
      }
    }
    session()->forget('old_relationship_values');

    if (!empty($changeLog)) {
      History::create([
        'systemlog_id' => $systemlog_id,
        'user_id' => Auth::id(),
        'reference_table' => $model->getTable(),
        'model_type' => get_class($model),
        'model_id' => $model->id,
        'body' => json_encode($changeLog),
      ]);
    }
  }

  protected function getRelations(Model $model)
  {
    $reflection = new ReflectionClass($model);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

    $relations = [];

    foreach ($methods as $method) {
      $methodName = $method->getName();
      if ($method->class !== get_class($model)) {
        continue;
      }

      if ($method->getNumberOfParameters() > 0) {
        continue;
      }


      if (method_exists($model, $methodName)) {
        $relation = $model->{$methodName}();
        if (
          $relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany ||
          $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany
        ) {
          $relations[] = $methodName;
        }
      }
    }

    return $relations;
  }

  protected function storeAllOldRelationshipValues($model)
  {
    session()->forget('old_relationship_values');
    $values = [];
    $relationships = $this->getRelations($model);
    foreach ($relationships as $relation) {
      $relationship = $model->$relation();
      if ($relationship instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
        $origRel = $relationship ? ($relationship->pluck('id')->toArray() ?? []) : [];
      } elseif ($relationship instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
        $origRel = $relationship ? ($relationship->get()->map(function ($item) {
          return $item->makeHidden(['created_at', 'updated_at'])->toArray();
        })->toArray() ?? []) : [];
      }
      $values[$relation] = $origRel ?? [];
    }
    session()->put('old_relationship_values', $values);
  }
}

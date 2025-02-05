<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
  protected $model;
  protected $resource;

  public function __construct(Model $model,  $resource = null)
  {
    $this->model = $model;
    $this->resource = $resource;
  }

  public function all()
  {
    return response()->json($this->resource ? $this->resource::collection($this->model->all()) : $this->model->all(), 200);
  }

  public function paginate($request)
  {
    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);
    $data = $this->model->simplePaginate($perPage, ['*'], 'page', $page);

    return response()->json($this->resource ? $this->resource::collection($data) : $data, 200);
  }

  public function find($id)
  {
    $record = $this->model->find($id);
    if ($record) {
      return response()->json($this->resource ? $this->resource::make($record) : $record, 200);
    }
    return response()->json(['message' => 'record not found'], 404);
  }

  public function create(array $attributes)
  {
    $record = $this->model->create($attributes);
    try {
      return response()->json($this->resource ? $this->resource::make($record) : $record, 201);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function update($id, array $attributes)
  {
    $record = $this->model->find($id);
    if (!$record) {
      return response()->json(['message' => 'record not found'], 404);
    }
    try {
      return response()->json($this->resource ? $this->resource::make($record->update($attributes)) : $record, 200);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function delete($id)
  {
    $record = $this->model->find($id);
    if (!$record) {
      return response()->json(['message' => 'record not found'], 404);
    }
    return response()->json($record->delete(), 200);
  }
}

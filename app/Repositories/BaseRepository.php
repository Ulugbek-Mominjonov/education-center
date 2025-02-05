<?php

namespace App\Repositories;

use App\Http\Requests\PaginateRequest;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
  protected $model;

  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  public function all()
  {
    return $this->model->all();
  }

  public function paginate($request)
  {
    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);

    return $this->model->simplePaginate($perPage, ['*'], 'page', $page);
  }

  public function find($id)
  {
    return $this->model->find($id);
  }

  public function create(array $attributes)
  {
    return $this->model->create($attributes);
  }

  public function update($id, array $attributes)
  {
    $record = $this->model->find($id);
    if ($record) {
      return $record->update($attributes);
    }
    return null;
  }

  public function delete($id)
  {
    $record = $this->model->find($id);
    if ($record) {
      return $record->delete();
    }
    return false;
  }
}

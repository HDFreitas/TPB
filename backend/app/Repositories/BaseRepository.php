<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

abstract class BaseRepository
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass;

    /**
     * Create a new instance of the repository.
     */
    public function __construct()
    {
        if (!$this->modelClass) {
            throw new \Exception("Model class must be set in " . get_class($this));
        }
    }

    /**
     * Get a new query builder for the model.
     *
     * @return EloquentQueryBuilder|QueryBuilder
     */
    protected function newQuery()
    {
        return app($this->modelClass)->newQuery();
    }

    /**
     * Execute the query and get the results.
     *
     * @param EloquentQueryBuilder|QueryBuilder|null $query
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    protected function doQuery($query = null, $take = 15, $paginate = true)
    {
        if (is_null($query)) {
            $query = $this->newQuery();
        }

        if ($paginate === true) {
            return $query->paginate($take);
        }

        if ($take > 0 || $take === false) {
            $query->take($take);
        }

        return $query->get();
    }

    /**
     * Returns all records.
     * If $take is false then brings all records
     * If $paginate is true returns Paginator instance.
     *
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function getAll($take = 15, $paginate = true)
    {
        return $this->doQuery(null, $take, $paginate);
    }

    /**
     * Get a listing of the resource as a collection.
     *
     * @param string $column
     * @param string|null $key
     * @return Collection
     */
    public function lists($column, $key = null)
    {
        // pluck() is the new lists() in Laravel 5.3+
        return $this->newQuery()->pluck($column, $key);
    }

    /**
     * Retrieves a record by his id
     * If fail is true fires ModelNotFoundException.
     *
     * @param int $id
     * @param bool $fail
     * @return Model|null
     */
    public function findById($id, $fail = true)
    {
        if ($fail) {
            return $this->newQuery()->findOrFail($id);
        }

        return $this->newQuery()->find($id);
    }

    /**
     * Alias for findById with fail=false
     *
     * @param int $id
     * @return Model|null
     */
    public function find($id)
    {
        return $this->findById($id, false);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param mixed $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id)
    {
        return $this->findById($id, true);
    }

    /**
     * Find a model by specified column.
     *
     * @param string $field
     * @param mixed $value
     * @param bool $fail
     * @return Model|null
     */
    public function findByField($field, $value, $fail = false)
    {
        $query = $this->newQuery()->where($field, $value);
        
        if ($fail) {
            return $query->firstOrFail();
        }
        
        return $query->first();
    }

    /**
     * Find records by specified field with value in array.
     *
     * @param string $field
     * @param array $values
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function findWhereIn($field, array $values, $take = 15, $paginate = true)
    {
        $query = $this->newQuery()->whereIn($field, $values);
        
        return $this->doQuery($query, $take, $paginate);
    }

    /**
     * Create a new record in the database.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data)
    {
        return app($this->modelClass)->create($data);
    }

    /**
     * Update a record in the database.
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update($id, array $data)
    {
        $model = $this->findById($id);
        $model->update($data);
        
        return $model;
    }

    /**
     * Delete a record from the database.
     *
     * @param int $id
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id)
    {
        return $this->findById($id)->delete();
    }

    /**
     * Get the count of records.
     *
     * @return int
     */
    public function count()
    {
        return $this->newQuery()->count();
    }

    /**
     * Get records with relations.
     *
     * @param array $relations
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function with(array $relations, $take = 15, $paginate = true)
    {
        $query = $this->newQuery()->with($relations);
        
        return $this->doQuery($query, $take, $paginate);
    }

    /**
     * Order results by specified column.
     *
     * @param string $column
     * @param string $direction
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function orderBy($column, $direction = 'asc', $take = 15, $paginate = true)
    {
        $query = $this->newQuery()->orderBy($column, $direction);
        
        return $this->doQuery($query, $take, $paginate);
    }

    /**
     * Filter results by given query params.
     *
     * @param array $where
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function search(array $where, $take = 15, $paginate = true)
    {
        $query = $this->newQuery();
        
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                $query->where($field, $condition, $val);
            } else {
                $query->where($field, '=', $value);
            }
        }
        
        return $this->doQuery($query, $take, $paginate);
    }
}
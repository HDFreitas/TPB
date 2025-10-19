<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

interface BaseInterface
{
    /**
     * Get all records with optional pagination.
     *
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function getAll($take = 15, $paginate = true);

    /**
     * Get a listing of the resource as a collection.
     *
     * @param string $column
     * @param string|null $key
     * @return Collection
     */
    public function lists($column, $key = null);

    /**
     * Find a record by its ID.
     *
     * @param int $id
     * @param bool $fail
     * @return Model|null
     */
    public function findById($id, $fail = true);

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param mixed $id
     * @return Model
     */
    public function findOrFail($id);

    /**
     * Find by field.
     *
     * @param string $field
     * @param mixed $value
     * @param bool $fail
     * @return Model|null
     */
    public function findByField($field, $value, $fail = false);

    /**
     * Find where in.
     *
     * @param string $field
     * @param array $values
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function findWhereIn($field, array $values, $take = 15, $paginate = true);

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data);

    /**
     * Update a record.
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update($id, array $data);

    /**
     * Delete a record.
     *
     * @param int $id
     * @return bool|null
     */
    public function delete($id);

    /**
     * Get count of records.
     *
     * @return int
     */
    public function count();

    /**
     * Load relations.
     *
     * @param array $relations
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function with(array $relations, $take = 15, $paginate = true);

    /**
     * Order by.
     *
     * @param string $column
     * @param string $direction
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function orderBy($column, $direction = 'asc', $take = 15, $paginate = true);

    /**
     * Search by multiple fields.
     *
     * @param array $where
     * @param int|bool $take
     * @param bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function search(array $where, $take = 15, $paginate = true);
}
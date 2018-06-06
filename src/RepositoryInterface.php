<?php

namespace Meiko\Crud;

interface RepositoryInterface
{
    public function get(int $id) : array;
    public function getByColumnValue(string $name, string $columName) : array;
    public function getAll() : array;
    public function delete(int $id) : bool;
    public function insert($values) : bool;
    public function insertItem(CrudInterface $item) : bool;
    public function update($id, $values) : bool;
    public function updateItem(CrudInterface $item) : bool;
}
<?php

namespace Meiko\Crud;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

abstract class BasicRepository implements RepositoryInterface
{
    private $adapter;
    private $table;
    private $id_column;
    private $object_name;
    private $mapResultsToObject;
    
    public function __construct(Adapter $adapter, string $table, string $id_column)
    {
        $this->adapter = $adapter;
        $this->table = $table;
        $this->id_column = $id_column;
    }
    
    public function getAll(): array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result =  $statement->execute();
        return $this->mapResult($result);
    }

    public function get(int $id): array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        $select->where([$this->id_column => $id]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $this->mapResult($result);
    }

    public function getByColumnValue(string $value, string $columnName): array
    {        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        $select->where([$columnName => $value]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $this->mapResult($result);
    }
    
    /*
    public function getWhere(array $values): array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        
        foreach($values as $key => $value)
            $select->where([$key => $value]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $this->mapResult($result);
    }
    */
     
    public function insert($values) : bool
    {
        $sql = new Sql($this->adapter);
        $insert = $sql->insert();
        $insert->into($this->table);
        $insert->values($values);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        \Zend\Debug\Debug::dump($statement->execute($values));
         return true;
    }
    
    public function insertItem(CrudInterface $item): bool
    {
        $params = get_object_vars($item);
        $sql = new Sql($this->adapter);
        $insert = $sql->insert();
        $insert->into($this->table);
        $insert->values($params);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute($params);
        return true;
    }

    public function update($id, $values): bool
    {
        $sql = new Sql($this->adapter);
        $update = $sql->update();
        $update->table($this->table);
        $update->set($values);
        $update->where([$this->id_column => $id]);
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();
        return true;
    }
    
    public function updateItem(CrudInterface $item): bool
    {
        $params = get_object_vars($item);
        $sql = new Sql($this->adapter);
        $update = $sql->update();
        $update->table($this->table);
        $update->set($params);
        $update->where([$this->id_column => $item->getId()]);
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $result =  $statement->execute($params);
        return true;
    }

    public function delete(int $id): bool
    {
        $sql = new Sql($this->adapter);
        $delete = $sql->delete();
        $delete->from($this->table);
        $delete->where([$this->id_column => $id]);

        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();
        return true;
    }
    
    private function mapResult($result)
    {
        if($this->mapResultsToObject)
            return $this->mapResultToArrayOfObjects($result);
        return $this->mapResultToArray($result);
    }
    
    private function mapResultToArrayOfObjects($result)
    {
        $objects = [];
        foreach($result as $row)
        {
            $object = new \ReflectionClass($this->object_name);
            $object = $object->newInstance();
            foreach($row as $key => $value)
            {
                if($key == $this->id_column)
                {
                    $object->setId($value);
                    continue;
                }
                $object->$key = $value;
            }
            $objects[] = $object;
        }
        return $objects;
    }
    
    private function mapResultToArray($result)
    {
        $users = null;
        foreach($result as $row)
        {
            $users[] = $row;
        }
        return $users;
    }
    
    public function getReturnValueClassName() : string
    {
        if($this->mapResultsToObject)
            return $this->object_name;
        else return 'array';
    }
    
    public function returnValuesAsObject(string $object_name)
    {
        $this->mapResultsToObject = true;
        $this->object_name = $object_name;
    }
    
    public function returnValuesAsArray()
    {
        $this->mapResultsToObject = false;
        $this->object_name = '';
    }
    
    public function isObjectMappingActive()
    {
        return $this->mapResultsToObject;
    }
    
}
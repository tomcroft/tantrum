<?php

namespace tantrum\Database;

use tantrum\Core,
    tantrum\QueryBuilder;

class Connection extends Core\Module
{

    private $pdoConnection;
    private $adaptor;
    private $schema;
    private $statement;
    private $transactionCounter = 0;

    public function setPdoConnection(\PDO $pdoConnection)
    {
        $this->pdoConnection = $pdoConnection;
    }

    // TODO: Can we typehint this?
    public function setAdaptor($adaptor)
    {
        $this->adaptor = $adaptor;
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function begin()
    {
        if(!$this->transactionCounter++) {
            return $this->pdoConnection->beginTransaction();
        }
       return $this->transactionCounter >= 0;
    }

    public function commit()
    {
        if(!--$this->transactionCounter) {
            $this->pdoConnection->commit();
        }
        return $this->transactionCounter >= 0;
    }

    public function rollback()
    {
        if($this->transactionCounter >= 0) { 
            $this->transactionCounter = 0; 
            return $this->pdoConnection->rollback(); 
        } 
        $this->transactionCounter = 0;
        return false;
    }

    public function getEntity($table)
    {
        $entity = self::newInstance('tantrum\Database\Entity');
        $entity->setTable($table);
        $entity->setConnection($this);
        return $entity;
    }

    public function getColumnDefinitions($table)
    {
        $query = $this->adaptor->getColumnDefinitions($this->schema, $table);
        $this->query($query);
        $fields = $this->fetchAll('tantrum\QueryBuilder\Field');
        return $fields;
    }
    
    public function query(QueryBuilder\Query $query)
    {
        $adaptor = $this->adaptor;
        switch ($query->getType()) {
            case QueryBuilder\Query::SELECT: 
                $queryString = $adaptor::formatSelect($query);
                $parameters = $query->getParameters();
                break;
            case QueryBuilder\Query::INSERT:
                $queryString = $adaptor::formatInsert($query);
                $parameters = array_values($query->getFields()->toArray());
                $parameters = !is_null($query->getDuplicateFieldsForUpdate())
                    ? array_merge($parameters, array_values($query->getDuplicateFieldsForUpdate()->toArray()))
                    : $parameters;
                break;
            case QueryBuilder\Query::DELETE:
                $queryString = $adaptor::formatDelete($query);
                $parameters = $query->getParameters();
                break;
            case QueryBuilder\Query::UPDATE:
                $queryString = $adaptor::formatUpdate($query);
                $parameters = array_merge(array_values($query->getFields()->toArray()), $query->getParameters());
                break;
            default:
                throw new Exception\DatabaseException('Query Type Not Handled');
        } try {
            $this->pdoConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); 
            if($this->statement = $this->pdoConnection->prepare($queryString)) {
                $this->statement->execute($parameters);
                return $this->checkErrors($this->statement, $queryString);
            } else {
                //$this->connection->debugDumpParams();
                throw new Exception\DatabaseException("Prepare statement failed:\r\n".print_r($this->pdoConnection->errorInfo(), 1)."\r\n".$queryString);
            }
        } catch(\PDOException $e) {
            throw new Exception\DatabaseException($e->getMessage());
        }
    }
    
    public function getInsertId()
    {
        return $this->pdoConnection->lastInsertId(); 
    }
    
    public function getAffectedRows()
    {
        return $this->statement->rowCount();
    }
    
    public function fetch($className=null, $constructorArgs=array())
    {
        if(!empty($className))
        {
            $this->statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $className, $constructorArgs);
        } else {
            if(count($constructorArgs) > 0) {
                throw new Exception\DatabaseException('Constructor arguments passed without a class name');
            }
            $this->statement->setFetchMode(\PDO::FETCH_ASSOC);
        }
        return $this->statement->fetch();
        
    }
    
    public function fetchAll($className=null, $constructorArgs=array())
    {
        if(!empty($className))
        {
            $this->statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $className, $constructorArgs);
        } else {
            if(count($constructorArgs) > 0) {
                throw new Exception\DatabaseException('Constructor arguments passed without a class name');
            }
            $this->statement->setFetchMode(\PDO::FETCH_ASSOC);
        }
        return $this->statement->fetchAll();
    }
    
    protected function checkErrors($pdo, $queryString)
    {
        $errors = $pdo->errorInfo();
        if($errors[0] > 0) {
            throw new Exception\DatabaseException($errors[2].":\r\n".$queryString);
        }

        return true;
    }
}
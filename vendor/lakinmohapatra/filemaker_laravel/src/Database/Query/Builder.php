<?php namespace laravel_filemaker\Database\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use FileMaker;

//use filemaker_laravel\Database\Connection;

class Builder extends BaseBuilder
{
    protected $fmConnection;

   /**
     * All of the available clause operators.
     *
     * @var array
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '=='
    ];

    
    /**
     * It contains fields to be returned.
     *
     * @var array
     */
    protected $fmFields;
    
    /**
     * It contains fields to be returned.
     *
     * @var array
     */
    protected $fmScript;

   /**
   * Create a new query builder instance.
   *
   * @param  \Illuminate\Database\ConnectionInterface  $connection
   * @param  \Illuminate\Database\Query\Grammars\Grammar  $grammar
   * @param  \Illuminate\Database\Query\Processors\Processor  $processor
   * @return void
   */
    public function __construct(
        \Illuminate\Database\ConnectionInterface $connection,
        \Illuminate\Database\Query\Grammars\Grammar $grammar,
        \Illuminate\Database\Query\Processors\Processor $processor
    ) {
        parent::__construct($connection, $grammar, $processor);
        //set_error_handler(null);
       //set_exception_handler(null);
        $this->fmConnection = $this->getFMConnection();
    }

    /**
     * Insert a new record into the database.
     *
     * @param void
     * @return FileMaker Connection Object
     */
    protected function getFMConnection()
    {
        return $this->connection->getConnection();
    }

    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return false;
        }

        $insertCommand = $this->fmConnection->newAddCommand($this->from);

        foreach ($values as $attributeName => $attributeValue) {
            $insertCommand->setField($attributeName, $attributeValue);
        }

        $results = $insertCommand->execute();

        if (FileMaker::isError($results)) {
            return false;
        }

        return true;
    }

    /**
     * Update record(s) into the database.
     *
     * @param  array  $columns
     * @return bool
     */
    public function update(array $columns)
    {
        $result = $this->getFindResults($columns);
        $msg = false;
        if(!FileMaker::isError($result) && $result->getFetchCount() > 0) {
            $records = $result->getRecords();
            foreach($records as $record) {
                $recordId = $record->getRecordId();

                $command = $this->fmConnection->newEditCommand($this->from, $recordId);
                if(FileMaker::isError($command)) {
                    return $msg;
                }
                foreach($columns as $key => $value) {
                    if ($key != 'updated_at') {
                        $command->setField($key, $value);
                    }
                }
                $result = $command->execute();
                if (!FileMaker::isError($result)) {
                    $msg = true;
                }
                else {
                    $msg = $result->getMessage();
                }
            }
        }
        else {
            $msg = $result->getMessage();
        }
        return $msg;
    }

    /**
     * Delete a record from the database.
     *
     * @param  array  $attribute
     * @return bool
     */
    public function delete($attribute = null) {

        if(!is_null($attribute)) {
            $this->wheres = $attribute;
        }
        $command = $this->fmConnection->newFindCommand($this->from);
        $this->addBasicFindCriterion($this->wheres, $command);

        $result = $command->execute();

        if(!FileMaker::isError($result) && $result->getFetchCount() > 0) {
            $records = $result->getRecords();
            foreach($records as $record) {
                $record->delete();
            }
        }
        else {
            $msg = $result->getMessage();
            echo $msg;
        }
        return $msg;
    }

   /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return array|static[]
     */
    public function get($columns = ['*'])
    {
        $results = $this->getFindResults();

        if (FileMaker::isError($results)) {
            return array();
        }

        return $this->getFMResult($columns, $results);
    }

    /**
     * Delete a record from the database.
     *
     * @param  array  $columns
     * @return bool
     */
    protected function getFindResults($columns)
    {
        if (property_exists($this, 'fmScript') && ! empty($this->fmScript)) {
            return $this->executeFMScript($this->fmScript);
        }
        else {
            if ($this->isOrCondition($this->wheres)) {
                $command = $this->compoundFind($columns);
            } else {
                $command = $this->basicFind();
            }
            
            $this->fmOrderBy($command);
            $this->setRange($command);
            
            return $command->execute();
        }        
    }

    /**
     * FileMaker And operation using newFindCommand
     *
     * @param void
     * @return FileMaker Command
     */
    protected function basicFind()
    {
        $command = $this->fmConnection->newFindCommand($this->from);
        $this->addBasicFindCriterion($this->wheres, $command);

        return $command;
    }
    
    /**
     * FileMaker OR operation using newFindRequest
     *
     * @param void
     * @return FileMaker Command
     */
    protected function compoundFind()
    {
        $orColumns = array();
        $andColumns = array();

        foreach ($this->wheres as $where) {
            $eloquentBoolean = strtolower($where['boolean']);
            if ($eloquentBoolean === 'or') {
                $orColumns[] = $where;
            } elseif ($eloquentBoolean === 'and') {
                $andColumns[] = $where;
            }
        }

        return $this->newFindRequest($orColumns, $andColumns);
    }

    /**
     * Add a FileMaker "order by" clause to the query.
     *
     * @param FileMaker Command
     * @return void
     */
    public function fmOrderBy($command)
    {
        $i = 1;
        foreach($this->orders as $order) {
            $direction = $order['direction'] == 'desc'
                         ? FILEMAKER_SORT_DESCEND
                         : FILEMAKER_SORT_ASCEND;
            $command->addSortRule($order['column'], $i, $direction);
            $i++;
        }
    }

    /**
     * Add "Offset/start" clause to the query.
     *
     * @param Integer $offset
     * @return \filemaker_laravel\Database\Query\Builder|static
     */
    public function skip($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Add "Offset/start" clause to the query.
     *
     * @param Integer $limit
     * @return \filemaker_laravel\Database\Query\Builder|static
     */
    public function limit($limit)
    {

        $this->limit = $limit;
        return $this;
    }

    /**
     * Set range to the query.
     *
     * @param FileMaker Command
     * @return \filemaker_laravel\Database\Query\Builder|static
     */
    public function setRange($command)
    {
        $command->setRange($this->offset, $this->limit);
        return $this;
    }

    /**
     * Set range to the query.
     *
     * @param FileMaker Command
     * @return \filemaker_laravel\Database\Query\Builder|static
     */
    protected function getFMResult($columns, $results = array())
    {
        if (empty($columns) || empty($results)) {
            return false;
        }

        $records = $results->getRecords();
        $this->fmFields = $results->getFields();

        foreach ($records as $record) {
            $eloquentRecords[] = $this->getFMFieldValues($record, $columns);
        }

        return $eloquentRecords;
    }

    /**
     * Make an array of fields.
     *
     * @param array $fmRecord
     * @param array/string $columns
     * @return array $eloquentRecord
     */
    protected function getFMFieldValues($fmRecord = array(), $columns = '')
    {
        if (empty($fmRecord) || empty($columns)) {
            return false;
        }

        if (in_array('*', $columns)) {
            $columns = $this->fmFields;
        }
        
        if (is_string($columns)) {
            $columns = [$columns];
        }
        
        foreach ($columns as $column) {
            $eloquentRecord[$column] = $this->getIndivisualFieldValues($fmRecord, $column);
        }

        return $eloquentRecord;
    }

    /**
     * Returns value of FileMaker field
     *
     * @param array $fmRecord
     * @param array/string $columns
     * @return integer/string value
     */
    protected function getIndivisualFieldValues($fmRecord, $column)
    {
        return in_array($column, $this->fmFields)
               ? $fmRecord->getField($column)
               : '';
    }

    /**
     * Add FileMaker Criterions for And/Or operations
     *
     * @param array $wheres
     * @param array $findCommand
     * @return void
     */
    protected function addBasicFindCriterion($wheres = array(), $findCommand = array())
    {
        if (empty($wheres) || empty($findCommand)) {
            return false;
        }

        foreach ($wheres as $where) {
            $operator = $where['operator'] === '=' ? '==' : $where['operator'];

            $findCommand->addFindCriterion(
                $where['column'],
                $operator . $where['value']
            );
        }
    }

    /**
     * Add FileMaker newCompundFindCommand for OR operations
     *
     * @param array $orColumns
     * @param array $andColumns
     * @return FileMaker Command
     */
    protected function newFindRequest($orColumns, $andColumns)
    {
        $findRequests = array();
        foreach ($orColumns as $orColumn) {
            $findRequest =  $this->fmConnection->newFindRequest($this->from);
            $this->addBasicFindCriterion([$orColumn], $findRequest);
            $this->addBasicFindCriterion($andColumns, $findRequest);
            $findRequests[] = $findRequest;
        }

        $compoundFind = $this->fmConnection->newCompoundFindCommand($this->from);
        $i = 1;
        foreach ($findRequests as $findRequest) {
            $compoundFind->add($i, $findRequest);
            $i++;
        }

        return $compoundFind;
    }

    /**
    * Used to check for and/or condition
    *
    * @param array $wheres
    * @return bool
    */
    protected function isOrCondition($wheres = array())
    {
        if (empty($wheres)) {
            return false;
        }

        return in_array('or', array_pluck($wheres, 'boolean'));
    }
    
    /**
    * Execute FileMaker script
    *
    * @param array $scriptAttributes
    * @return FileMaker Result
    */
    public function executeFMScript($scriptAttributes = array())
    {
        $results = array();

        $scriptCommand = $this->fmConnection
                              ->newPerformScriptCommand($this->from,
                                                        $scriptAttributes['scriptName'],
                                                        $scriptAttributes['params']);
        return $scriptCommand->execute();
    }

    /**
    * set name and params in an array
    *
    * @param string $scriptName
    * @param string $params
    * @return void
    */
    public function performScript($scriptName = '', $params = '')
    {
        $this->fmScript = array(
            'scriptName' => $scriptName,
            'params' => $params
        );
    }
}

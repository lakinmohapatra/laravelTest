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
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'like binary', 'not like', 'between', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', '=='
    ];
    
   /**
   * Create a new query builder instance.
   *
   * @param  \Illuminate\Database\ConnectionInterface  $connection
   * @param  \Illuminate\Database\Query\Grammars\Grammar  $grammar
   * @param  \Illuminate\Database\Query\Processors\Processor  $processor
   * @return void
   */
   public function __construct(\Illuminate\Database\ConnectionInterface $connection,
                               \Illuminate\Database\Query\Grammars\Grammar $grammar,
                               \Illuminate\Database\Query\Processors\Processor $processor)
   {
       parent::__construct($connection, $grammar, $processor);
       set_error_handler(null);
      set_exception_handler(null);
       $this->fmConnection = $this->getFMConnection();
   }
    
   protected function getFMConnection()
   {
      return $this->connection->getConnection();
   }
   
   /*public function update($attributes = array())
   {
        echo $this->from;
   }*/
   
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
      
      return $results->getRecords();
   }
   
   public function update(array $values)
   {
       if (empty($values)) {
        return false;
      }
       
   }
   
   /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return array|static[]
     */
    public function get($columns = ['*'])
    {
        $original = $this->columns;
      
        if (is_null($original)) {
            $this->columns = $columns;
        }

        $results = $this->processor->processSelect($this, $this->runSelect());

        $this->columns = $original;

        return $results;
    }
}
<?php

namespace Kitbs\Blocks\Definitions;

use InvalidArgumentException;

class SchemaDefinition extends AbstractDefinition
{   
    protected $attributes = [
        'table'       => null,
        'class'       => null,
        'connection'  => null,
        'fields'      => [],
        'increments'  => 'id',
        'timestamps'  => null,
        'softDeletes' => false,
        'create'      => true,
        'action'      => 'create',
        'name'        => null,
        'engine'      => null,
        'charset'     => null,
        'collation'   => null,
        'indexes'     => [],
        'statements'  => null,
    ];
    
    protected $patternFile = 'migrations/create';
    
    public function setDefinition(array $attributes)
    {
        if (!isset($attributes['table']) && !isset($attributes['model'])) {
            throw new InvalidArgumentException('Schema definition must have either a table or model name.');
        }
        
        $attributes = array_merge(array_except($attributes, ['migration']), $attributes['migration']);
        
        if (!isset($attributes['table'])) {
            $attributes['table'] = snake_case(str_plural($attributes['model']));
        }
        
        if (!isset($attributes['create']) || $attributes['create']) {
            $attributes['name'] = 'create_' . $attributes['table'] . '_table';
            $attributes['action'] = 'create';
        }
        else {
            $attributes['name'] = 'modify_' . $attributes['table'] . '_table';
            $attributes['action'] = 'table';
            $this->patternFile = 'migrations/update';
        }
        
        $attributes['class'] = studly_case($attributes['name']);
        
        foreach ($attributes['fields'] as &$field) {
            $field['table'] = $attributes['table'];
            if ($attributes['action'] == 'create') {
                unset($field['first']);
                unset($field['after']);
            }
            $field = new SchemaColumnDefinition($field, $this->block);
        }
        
        $attributes['fields'] = collect($attributes['fields'])->keyBy('name');
        
        foreach ($attributes['indexes'] as &$field) {
            $field['table'] = $attributes['table'];
            $field = new SchemaIndexDefinition($field, $this->block);
        }
        
        $attributes['indexes'] = collect($attributes['indexes'])->keyBy('name');
        
        parent::setDefinition($attributes);

//         foreach ($this->indexes as $index) {
//         CHECK INDEXES HAVE MATCHING COLUMNS
//         }
        
    }
    
    protected function getConnectionOutput()
    {
        return $this->connection ? 'connection(\''.$this->connection.'\')->' : null;
    }
    
    protected function getIndexesOutput()
    {
        $output = PHP_EOL;
        
        foreach ($this->indexes as $field) {
            $output .= $field->output();
        }
        
        return $output;
    }
    
    protected function getEngineOutput()
    {
        return $this->engine ? PHP_EOL.'            $table->engine = \''.$this->engine.'\';'.PHP_EOL : null;
    }
    
    protected function getCharsetOutput()
    {
        return $this->charset ? PHP_EOL.'            $table->charset = \''.$this->charset.'\';'.PHP_EOL : null;
    }
    
    protected function getCollationOutput()
    {
        return $this->collation ? PHP_EOL.'            $table->collation = \''.$this->collation.'\';'.PHP_EOL : null;
    }
    
    protected function getFieldsOutput()
    {
        $output = PHP_EOL;
        
        foreach ($this->fields as $field) {
            $output .= $field->output();
        }
        
        return $output;
    }
    
    protected function getStatementsOutput()
    {
        if ($this->statements) {
            if (is_array($this->statements)) {
                $statements = '';
                
                foreach ($this->statements as $statement)
                {
                    $statement = str_replace('{:table:}', $this->table, $statement);
                    
                    if (!str_contains($statement, 'DB::')) {
                        if (!ends_with(trim($statement), ';')) {
                            $statement .= ';';
                        }
                        $statements .= '        DB::statement(\''.$statement.'\');'.PHP_EOL;
                    }
                    else {
                        $statements .= '        '.$statement.PHP_EOL;
                    }
                }
                
            }
            else {
                $statements = str_replace('{:table:}', $this->table, $this->statements);
                $statements = explode(PHP_EOL, $statements);
                $statements = '            '.implode(PHP_EOL.'            ', $statements);
            }
            
            return PHP_EOL.$statements;
        }
    }
}
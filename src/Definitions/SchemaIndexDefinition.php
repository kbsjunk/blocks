<?php

namespace Kitbs\Blocks\Definitions;

use InvalidArgumentException;

class SchemaIndexDefinition extends AbstractDefinition
{   
    protected $attributes = [
        'fields'     => [],
        'table'      => null,
        'indexName'  => null,
        'name'       => null,
        'index'      => null,
        'references' => null,
        'on'         => null,
        'onDelete'   => null,
        'onUpdate'   => null,
    ];
        
    protected $pattern = '            $table->{:index:}({:fields:}{:indexName:}){:references:}{:on:}{:onUpdate:}{:onDelete:};'.PHP_EOL;
    
    public function setDefinition(array $attributes) {
        
        if (!isset($attributes['fields']) && !isset($attributes['field'])) {
            throw new InvalidArgumentException('Schema index definition must specify one or more fields.');
        }
        
        if (!isset($attributes['fields'])) {
            $attributes['fields'] = head((array) $attributes['field']);
        }
        
        if (!isset($attributes['index'])) {
            $attributes['index'] = true;
        }
        
        if ($attributes['index'] == 'foreign') {
            
            if (is_array($attributes['fields'])) {
                throw new InvalidArgumentException('A foreign key index ' . json_encode($attributes['fields']) . ' must not be an array of fields.');
            }
            
            if(ends_with($attributes['fields'], '_id')) {
                $references = 'id';
                $on = str_plural(substr($attributes['fields'], 0, -3));
            }
            
            if (!isset($attributes['references']) && isset($references)) {
                $attributes['references'] = $references;
            }
            else {
                throw new InvalidArgumentException('A foreign key index ' . json_encode($attributes['fields']) . ' must have a \'references\' parameter.');
            }
            
            if (!isset($attributes['on']) & isset($on)) {
                $attributes['on'] = str_plural($on);
            }
            else {
                throw new InvalidArgumentException('A foreign key index ' . json_encode($attributes['fields']) . ' must have an \'on\' parameter.');
            }
            
        }
        else {
            unset($attributes['references']);
            unset($attributes['on']);
            unset($attributes['onDelete']);
        }
                
        $attributes['name'] = $this->createIndexName($attributes['table'], $attributes['index'], (array) $attributes['fields']);
        
        if (empty(@$attributes['indexName'])) {
            $attributes['name'] = $this->createIndexName($attributes['table'], $attributes['index'], (array) $attributes['fields']);
        }
        else {
            $attributes['name'] = $attributes['indexName'];
        }
        
        parent::setDefinition($attributes);

    }
    
    protected function getIndexOutput()
    {
        return camel_case($this->index);
    }
    
    protected function getFieldsOutput()
    {
        if (is_array($this->fields)) {
            return json_encode($this->fields);
        }
        
        return '\''.$this->fields.'\'';
    }

    protected function getReferencesOutput()
    {
        return $this->references ? '->references(\'' . $this->references .'\')' : null;
    }

    protected function getOnOutput()
    {
        return $this->on ? '->on(\'' . $this->on .'\')' : null;
    }

    protected function getOnDeleteOutput()
    {
        return $this->onDelete ? '->onDelete(\'' . $this->onDelete .'\')' : null;
    }

    protected function getOnUpdateOutput()
    {
        return $this->onUpdate ? '->onUpdate(\'' . $this->onUpdate .'\')' : null;
    }

    protected function getIndexNameOutput()
    {
        return $this->indexName ? ', \'' . $this->indexName .'\'' : null;
    }
    
    /**
     * Create a default index name for the table.
     *
     * @param  string  $table
     * @param  string  $type
     * @param  array   $columns
     * @return string
     */
    protected function createIndexName($table, $type, array $columns)
    {
        $index = strtolower($table.'_'.implode('_', $columns).'_'.$type);
        return str_replace(['-', '.'], '_', $index);
    }
}
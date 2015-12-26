<?php

namespace Kitbs\Blocks\Definitions;

use InvalidArgumentException;

class SchemaColumnDefinition extends AbstractDefinition
{   
    protected $attributes = [
        'name'       => null,
        'type'       => null,
        'parameters' => [],
        'index'      => null,
        'nullable'   => null,
        'unsigned'   => null,
        'default'    => null,
        'charset'    => null,
        'collation'  => null,
        'comment'    => null,
        'after'      => null,
        'first'      => false,
        'position'   => null,
        'predefined' => false,
    ];
    
    protected $allowedParameters = [
        'string'  => ['length'],
        'decimal' => ['precision', 'scale'],
        'double'  => ['precision', 'scale'],
        'enum'    => ['values'],
        'morphs'  => ['indexName'],
    ];
    
    protected $pattern = '            $table->{:type:}({:name:}{:parameters:}){:unsigned:}{:nullable:}{:index:}{:default:}{:position:}{:charset:}{:collation:}{:comment:};'.PHP_EOL;
    
    public function setDefinition(array $attributes) {

        if (is_string($attributes)) {
            if (in_array((string) $attributes, ['timestamps','timestampsTz','nullableTimestamps','rememberToken'])) {
                $attributes = [
                    'predefined' => true,
                    'name' => $attributes,
                    'type' => $attributes,
                ];
            }
            else {
                $attributes = [
                    'name' => $attributes,
                    'type' => $attributes,
                ];
            }
        }
        else {
            if (in_array((string) @$attributes['type'], ['timestamps','timestampsTz','nullableTimestamps','rememberToken'])) {
                $attributes = [
                    'predefined' => true,
                    'name' => $attributes['type'],
                    'type' => $attributes['type'],
                ];
            }
        }
        
        if (empty(@$attributes['name'])) {
            throw new InvalidArgumentException('Schema column definition must have a name.');
        }
        
        if (empty(@$attributes['type'])) {
            $attributes['type'] == 'string';
        }
        
        if (starts_with($attributes['type'], 'unsigned')) {
            $attributes['unsigned'] = true;
            $attributes['type'] = lcfirst(substr($attributes['type'], 8));
        }
        
        if (isset($this->allowedParameters[$attributes['type']])) {
            $allowedParameters = $this->allowedParameters[$attributes['type']];

            if (!isset($attributes['parameters']) && $parameters = array_only($attributes, $allowedParameters)) {
                $attributes['parameters'] = $parameters;
            }
        }            
        
        if (!isset($attributes['index']) && @$attributes['unique']) {
            $attributes['index'] = 'unique';
        }
        
        parent::setDefinition($attributes);
        
        if ($this->predefined) {
            $this->pattern = '            $table->{:type:}();'.PHP_EOL;
        }

    }
    
    protected function getTypeOutput()
    {
        return camel_case($this->type);
    }
    
    protected function getNameOutput()
    {              
        return '\''.snake_case($this->name).'\'';
    }
    
    protected function getParametersOutput()
    {
        
        $attributes = $this->attributes;
        
        foreach ($attributes['parameters'] as &$parameter) {
            if (is_array($parameter)) {
                $parameter = json_encode($parameter);
            }
            else {
                $parameter = $this->needsQuotes($parameter);
            }
        }
        
        return $attributes['parameters'] ? ', '.implode(', ', $attributes['parameters']) : null;

    }
    
    protected function getUnsignedOutput()
    {
        return $this->unsigned ? '->unsigned()' : null;
    }
    
    protected function getNullableOutput()
    {
        return $this->nullable ? '->nullable()' : null;
    }
    
    protected function getPositionOutput()
    {
        return $this->first ? '->first()' : ($this->after ? '->after(\'' . $this->after .'\')' : null);
    }
    
    protected function getCommentOutput()
    {
        return $this->comment ? '->comment(\'' . $this->comment .'\')' : null;
    }
    
    protected function getDefaultOutput()
    {
        return $this->default ? '->default(' . $this->needsQuotes($this->default) .')' : null;
    }
    
    protected function getIndexOutput()
    {
        if ($this->index) {
            if (in_array((string) $this->index, ['unique', 'primary'])) {
                return '->'.$this->index.'()';
            }
            
            return '->index()';
        }
    }
    
    protected function getCharsetOutput()
    {
        return $this->charset ? '->charset(\''.$this->charset.'\')' : null;
    }
    
    protected function getCollationOutput()
    {
        return $this->collation ? '->collate(\''.$this->collation.'\')' : null;
    }
    
    protected function needsQuotes($string)
    {
        $quote = !is_numeric($string) ? '\'' : null;
        return $quote.$string.$quote;
    }
}
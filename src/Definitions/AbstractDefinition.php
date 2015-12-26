<?php

namespace Kitbs\Blocks\Definitions;

use InvalidArgumentException;
use Illuminate\Support\Fluent;

abstract class AbstractDefinition extends Fluent
{   
    protected $block;
    protected $attributes = [];
    protected $pattern;
    protected $patternFile;
    
    public function __construct(array $attributes, BlockDefinition $block)
    {
        $this->setBlock($block);
        $this->setDefinition($attributes);
    }
    
    public function setDefinition(array $attributes)
    {
        if (isset($attributes['pattern'])) {
            $this->pattern = $attributes['pattern'];
            $this->patternFile = null;
        }
        elseif (isset($attributes['patternFile'])) {
            $this->patternFile = $attributes['patternFile'];
            $this->pattern = null;
        }
        
        $attributes = array_only($attributes, array_keys($this->attributes));
                
        $this->attributes = array_merge($this->attributes, $attributes);
    }
    
    public function getDefinition()
    {
        return $this->attributes;
    }
    
    public function getDefinitionKey($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }
    
    public function setDefinitionKey($key, $value)
    {
        return $this->attributes[$key] = $value;
    }
    
    protected function setBlock(BlockDefinition $block)
    {
        $this->block = $block;
    }
    
    protected function getPattern()
    {
        if ($this->pattern) {
            return $this->pattern;
        }
        elseif ($this->patternFile) {
            $patternFile = $this->block->findPatternFile($this->patternFile);
            return $this->block->getFile($patternFile);
        }
               
        throw new InvalidArgumentException("{get_class($this)} must have a pattern string or pattern file defined.");
        
    }
        
    public function output()
    {
        $output = $this->getPattern();
        
        foreach ($this->attributes as $key => $value) {
            
            $method = 'get'.studly_case($key).'Output';
            
            if (method_exists($this, $method)) {
                $value = $this->$method();
            }
                
            $output = str_replace('{:'.$key.':}', $value, $output);
        }
        
        return $output;
    }
    
}
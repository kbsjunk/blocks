<?php

namespace Kitbs\Blocks\Definitions;

use InvalidArgumentException;

class LangDefinition extends AbstractDefinition
{   
    protected $attributes = [
        'model'  => null,
        'table'  => null,
        'title'  => null,
        'plural' => null,
        'labels' => [],
        'helps'  => [],
    ];
    
    protected $patternFile = 'lang';
    
    public function setDefinition(array $attributes)
    {
        
        if (!isset($attributes['title'])) {
            if (isset($attributes['model'])) {
                $attributes['title'] = mb_convert_case(preg_replace('/(.)(?=[A-Z])/', '$1 ', $attributes['model']), MB_CASE_TITLE, 'UTF-8');
                $attributes['plural'] = str_plural($attributes['title']);
            }
            elseif (isset($attributes['table'])) {
                $attributes['plural'] = mb_convert_case(str_replace('_', ' ', $attributes['table']), MB_CASE_TITLE, 'UTF-8');
                $attributes['title'] = str_singular($attributes['plural']);
            }
            else {
                throw new InvalidArgumentException('Lang definition must have either a title, a table name or a model name.');
            }
            
        }
        elseif (is_array($attributes['title'])) {
            if (!isset($attributes['title']['plural']) && isset($attributes['title']['singular'])) {
                $attributes['plural'] = str_plural($attributes['title']['singular']);
                $attributes['title'] = $attributes['title']['singular'];
            }
            elseif (isset($attributes['title']['plural']) && !isset($attributes['title']['singular'])) {
                $attributes['plural'] = $attributes['title']['plural'];
                $attributes['title'] = str_singular($attributes['title']['plural']);
            }
            elseif (isset($attributes['title']['plural']) && isset($attributes['title']['singular'])) {
                $attributes['plural'] = $attributes['title']['plural'];
                $attributes['title'] = $attributes['title']['singular'];
            }
            else {
                throw new InvalidArgumentException('Lang definition must have either a singular or plural title.');
            }
        }
        else {
            $attributes['plural'] = str_plural($attributes['title']);
        }
        
        $labels = [];
        $helps = [];
        
//         foreach ($attributes['fields'] as $field) {
//             $labels[] = new LangLabelDefinition($field, $this->block);
//             $helps[] = new LangHelpDefinition($field, $this->block);
//         }
        
        $attributes['labels'] = collect($labels)->keyBy('name');
        $attributes['helps'] = collect($labels)->keyBy('name');
        
        parent::setDefinition($attributes);
        
    }
            
    protected function getLabelsOutput()
    {
        $output = PHP_EOL;
        
        foreach ($this->labels as $field) {
            $output .= $field->output();
        }
        
        return $output;
    }
            
    protected function getHelpsOutput()
    {
        $output = PHP_EOL;
        
        foreach ($this->helps as $field) {
            $output .= $field->output();
        }
        
        return $output;
    }
}
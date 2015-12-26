<?php

namespace Kitbs\Blocks\Traits;

trait LoadsDefinitions
{
    protected $definition;
    
    protected function loadDefinitionFile()
    {
        $this->definition = app()->make('Kitbs\Blocks\Definitions\BlockDefinition');
        
        $file = $this->argument('file');
        
        $this->definition->load($file);
    }
        

}

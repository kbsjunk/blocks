<?php

namespace Kitbs\Blocks\Definitions;

use Symfony\Component\Yaml\Parser;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

class BlockDefinition
{

    protected $yaml;
    protected $files;
    protected $definition;
    
    protected $migration;
    protected $model;
    protected $lang;
    
    public function __construct(Filesystem $files, Parser $yaml)
    {
        $this->files = $files;
        $this->yaml = $yaml;
    }
    
    public function load($path)
    {
        $path = $this->findDefinitionFile($path);
        
        $file = $this->getFile($path);

        $yaml = $this->yaml->parse($file);
        
        $this->definition = $this->parseDefinition($path, $yaml);

    }
    
    private function findFile($paths, $file, array $extensions = [], $fileType = '')
    {
        if ($this->files->exists($file)) {
            return $file;
        }
        
        foreach ($paths as $path) {
            foreach ($extensions as $extension) {
                $fullPath = $path.'/'.$file.'.block.'.$extension;

                if ($this->files->exists($fullPath)) {
                    return $fullPath;
                }
            }
        }
        
        throw new InvalidArgumentException("Block $fileType file [$file] not found.");
                    
    }
    
    public function findDefinitionFile($file)
    {
        $paths = [
            resource_path('blocks/definitions'),
            __DIR__.'/../../blocks/definitions',
        ];
        
        return $this->findFile($paths, $file, ['yaml', 'yml'], 'definition');
                    
    }
    
    public function findPatternFile($file)
    {
        $paths = [
            resource_path('blocks/patterns'),
            __DIR__.'/../../blocks/patterns',
        ];
        
        return $this->findFile($paths, $file, ['php'], 'pattern');
                    
    }
    
    public function getFile($path)
    {
        return $this->files->get($path);
    }
    
    public function putFile($path, $contents)
    {
        return $this->files->put($path, $contents);
    }
    
    private function parseDefinition($path, $yaml)
    {
        $this->parseMigration($yaml);
        $this->parseLang($yaml);
               
//         throw new InvalidArgumentException("The file at {$path} is not a valid Block definition.");
    }
    
    private function parseMigration($yaml)
    {
        $this->migration = new SchemaDefinition($yaml, $this);
    }
    
    public function getMigration()
    {
        return $this->migration;
    }
    
    private function parseLang($yaml)
    {
        $this->lang = new LangDefinition($yaml, $this);
    }
    
    public function getLang()
    {
        return $this->lang;
    }
    
}
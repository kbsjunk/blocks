<?php
namespace Kitbs\Blocks\Overrides;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;

class MigrationCreator extends BaseMigrationCreator
{
    public function overrideCreate($name, $path, $contents)
    {
        $path = $this->getPath($name, $path);
        
        $this->files->put($path, $contents);
        
        $this->firePostCreateHooks();
        
        return $path;
    }
}
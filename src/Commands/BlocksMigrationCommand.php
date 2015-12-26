<?php

namespace Kitbs\Blocks\Commands;

use Kitbs\Blocks\Traits\LoadsDefinitions;
use Illuminate\Console\Command;

use Illuminate\Support\Composer;
use Kitbs\Blocks\Overrides\MigrationCreator;

class BlocksMigrationCommand extends Command
{
    use LoadsDefinitions;
   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blocks:migration {file}
                            {--path= : The location where the migration file should be created.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blocks command to generate a migration file.';
    
    /**
     * The migration creator instance.
     *
     * @var \Illuminate\Database\Migrations\MigrationCreator
     */
    protected $creator;
    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;
    
    /**
     * Create a new migration install command instance.
     *
     * @param  \Illuminate\Database\Migrations\MigrationCreator  $creator
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct();
        
        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->loadDefinitionFile();

        $migration = $this->definition->getMigration();

        $name = $migration->getDefinitionKey('name');
        
        $this->writeMigration($name, $migration->output());
        $this->composer->dumpAutoloads();
    }
    
    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $table
     * @param  bool    $create
     * @return string
     */
    protected function writeMigration($name, $contents)
    {
        $path = $this->getMigrationPath();
        $file = pathinfo($this->creator->overrideCreate($name, $path, $contents), PATHINFO_FILENAME);
        $this->line("<info>Created Migration:</info> $file");
    }
    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return $this->laravel->basePath().'/'.$targetPath;
        }
        return $this->laravel->databasePath().'/migrations';
    }

}

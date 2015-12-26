<?php

namespace Kitbs\Blocks\Commands;

use Illuminate\Console\Command;
use Kitbs\Blocks\Traits\LoadsDefinitions;

class BlocksLangCommand extends Command
{
    use LoadsDefinitions;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blocks:lang {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blocks command to generate a language file.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->loadDefinitionFile();

        $lang = $this->definition->getLang();
        
        $lang->output();
        
    }
}

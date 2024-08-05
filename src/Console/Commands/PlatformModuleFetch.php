<?php

namespace CitraGroup\Platform\Console\Commands;

use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Process\Exceptions\ProcessFailedException;
use CitraGroup\Platform\Services\GitModule;
use CitraGroup\Platform\Services\GitModuleHelper;

class PlatformModuleFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:fetch {module?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch git repository to targeted module';

    /**
     * handle function
     *
     * @return void
     */
    public function handle()
    {
        try {
            // try to getting the correct modules if not found..
            $module = $this->argument('module');
            if (!GitModuleHelper::isModuleExist($module)) return $this->info("Modules not found");
            // try to fetch
            $this->info('Trying to fetch.. ' . $module);
            $output = GitModule::fetchModule($module);
            if ($output instanceof ProcessFailedException) throw $output;
        } catch (ErrorException $error) {
            return $this->error($error->getMessage());
        }
    }
}

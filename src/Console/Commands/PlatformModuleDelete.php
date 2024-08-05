<?php

namespace CitraGroup\Platform\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Process\Process;
use CitraGroup\Platform\Services\GitModule;
use CitraGroup\Platform\Services\GitModuleHelper;

class PlatformModuleDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:delete
        {module?}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete selected module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // try to getting needed argument
            $module = $this->argument('module');

            // try to getting the correct modules if not found..
            if (!GitModuleHelper::isModuleExist($module)) return $this->info('Module not found');

            // delete
            if (!$this->confirm(
                "Confirm to delete the targeted module.. \n" .
                    "Module Name        : $module \n" .
                    "Targeted Directory : " . GitModuleHelper::buildModuleDir($module) . "\n"
            )) {
                return $this->info('Delete Cancelled');
            }

            // action
            $success = GitModule::deleteModule($module);
            if ($success)               return $this->info('Delete Success');
            else if (is_null($success)) return $this->info('Delete Canceled');
            else if (!$success)         return $this->info('Delete Failed');
        } catch (ErrorException $error) {
            return $this->error($error->getMessage());
        }
    }
}

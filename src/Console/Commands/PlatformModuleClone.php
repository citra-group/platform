<?php

namespace CitraGroup\Platform\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use CitraGroup\Platform\Services\GitModule;
use CitraGroup\Platform\Services\GitModuleHelper;

class PlatformModuleClone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:clone
        {repository}
        {--by-tag}
        {--by-commit}
        {--directory=}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone module from git-source';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = $this->option('directory') ?: File::basename($this->argument('repository'));
        $module_slug = $this->option('directory') ?: $this->argument('directory');
        $mode = env('APP_ENV', 'local');
        $byCommit = $this->option('by-commit');
        $byTag = $this->option('by-tag');

        try {
            // -> check if module already exist
            if (GitModuleHelper::isModuleExist($module_slug))
                return $this->info('Module already exist!!');

            // --> Proceed to clone the module
            $this->info('Trying to clone..');
            $output = GitModule::cloneModule($this->argument('repository'), $module_slug);
            if ($output instanceof ProcessFailedException) throw $output;

            // -> procced to fetch
            $this->call('module:fetch', ['module' => $module_slug]);

            // -> proceed to do the operation for production mode
            if ($byTag && !$byCommit)
                $this->call('module:checkout', ['module' => $module_slug, '--by-tag' => true, '--nofetch' => 'true']);
            // -> proceed to do the operation for dev mode
            if ($byCommit && !$byTag)
                $this->call('module:checkout', ['module' => $module_slug, '--by-commit' => true, '--nofetch' => 'true']);
            // -> proceed to mode invalid, have option to delete the module
            if ($mode !== 'local' && $mode !== 'production' && $this->confirm('Do you want to delete the cloned module ?'))
                $this->call('module:delete', ['module' => $module_slug]);

            // -> Get into conclusions, if exist then cloned succesfully
            if (GitModuleHelper::isModuleExist($module_slug)) $this->info('Module cloned succesfully!!');
        } catch (ErrorException $error) {
            return $this->error($error->getMessage());
        }

        /*
        $process = new Process([
            'git', 'clone', $this->argument('repository'), $this->option('directory')
        ]);
        $process->setWorkingDirectory(base_path() . DIRECTORY_SEPARATOR . 'modules');
        $process->run();
         */
    }
}

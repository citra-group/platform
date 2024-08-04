<?php

namespace CitraGroup\Platform\Console\Commands;

use ErrorException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Console\Command;
use CitraGroup\Platform\Services\GitModule;
use CitraGroup\Platform\Services\GitModuleHelper;

class PlatformModuleCheckout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:checkout
        {module?}
        {--by-commit}
        {--by-tag}
        {--nofetch=false}
        {ref?}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checkout git repository to modules folder';

    /**
     * handle function
     *
     * @return void
     */
    public function handle()
    {
        try {
            // mode
            $mode    = env('APP_ENV', 'local');

            // try to getting needed argument
            $module     = $this->argument('module');
            $byCommit   = $this->option('by-commit');
            $byTag      = $this->option('by-tag');
            $ref        = $this->argument('ref');
            $nofetch    = $this->option('nofetch') == 'true';

            // try to getting the correct modules if not found..
            if (!GitModuleHelper::isModuleExist($module)) {
                return $this->info('Modules ' . $module . ' not found');
            }

            // Handling fetch
            if (!$nofetch) {
                $this->call('module:fetch', ['module' => $module]);
            }

            // Hadnlign Checkout
            if ($byTag && !$byCommit) {
                $success = $this->handleByTag($module, $ref);
            }

            // For refs by tags or production mode
            if ($byCommit && !$byTag) {
                $success = $this->handleByCommit($module, $ref);
            }

            // return
            if (!isset($success) || is_null($success)) {
                return $this->info('Tags or Commit not defined');
            } elseif ($success) {
                return $this->info('Success checkout');
            } elseif (!$success) {
                return $this->info('Failed checkout');
            }
        } catch (ErrorException $error) {
            return $this->error($error->getMessage());
        }
    }

    /**
     * handle function
     *
     * @return bool | null
     */
    protected function handleByTag($module, $tag): bool | null
    {
        // -> For refs by tags or production mode
        $this->info('Trying to checkout by tag..');

        // -> procced to fetch
        $output = GitModule::fetchModule($module);
        if ($output instanceof ProcessFailedException) {
            throw $output;
        }

        // -> get list of tags
        $output = GitModule::getModuleTags($module);
        if ($output instanceof ProcessFailedException) {
            throw $output;
        }
        if (!is_array($output)) {
            throw $output;
        }
        if (count($output) <= 0) {
            return $this->info("There are no tags found");
        }

        // -> current tag / head
        $log = GitModule::getCurrentLog($module);
        $current_tag = 'Not Found';
        if (count($log->tags) > 0) {
            $current_tag = $log->tags[0];
        }
        if (!is_null($current_tag)) {
            $this->info('Current Tag : ' . $current_tag);
        }

        // -> check if tags found
        while (!in_array($tag, $output)) {
            if (is_null($tag)) {
                $tag = $this->choice('Tag Not Found, Select Tag', $output);
            } else {
                $tag = $this->choice('Tag Not Found, Select Tag', $output);
            }
        }

        // return
        return GitModule::checkoutModule($tag, $module);;
    }

    /**
     * handle function
     *
     * @return bool | null
     */
    protected function handleByCommit($module, $ref): bool | null
    {
        // -> For refs by tags or production mode
        $this->info('Trying to checkout by commit..');

        // -> get list of refs
        $output = GitModule::getModuleCommits($module, 'origin', 'main');
        if ($output instanceof ProcessFailedException) {
            throw $output;
        }
        if (!is_array($output)) {
            throw $output;
        }
        if (count($output) <= 0) {
            return $this->info("There are no commit found");
        }

        // -> current tag / head
        $current_commit = GitModule::getModuleCurrentCommit($module);
        if (!is_null($current_commit)) {
            $this->info('Current Refs : ' . $current_commit);
        }
        // -> latest tag
        $latest_commit = GitModule::getModuleCurrentCommit($module, 'origin', 'main');
        if (!is_null($latest_commit)) {
            $this->info('Latest Refs : ' . $latest_commit);
        }

        // -> check if tags found
        while (!in_array($ref, $output)) {
            if (is_null($ref)) {
                $ref = $this->choice('Ref Not Found, Select Ref', $output);
            } else {
                $ref = $this->choice('Ref Not Found, Select Ref', $output);
            }
        }

        // return
        return GitModule::checkoutModule($ref, $module);;
    }
}

<?php

namespace CitraGroup\Platform\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use CitraGroup\Platform\Services\GitModuleHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use InvalidArgumentException;
use stdClass;

class GitModule
{
    /**
     * Github::open(directory)
     * ->reset()    : git reset --hard && git clean -df && git switch main
     * ->clone(repo, directory)
     * ->fetch()    : git fetch --all --tags --prune --prune-tags
     * ->update()   : git pull origin main => on dev | git checkout $(git describe --tags $(git rev-list --tags --max-count=1)) => on production
     * ->info()     : git log --name-status HEAD^..HEAD => on dev | git tag -n => on production
     */

    /**
     * process function
     * @return string | null
     */
    protected static function process($command, $pwd = null): string | null
    {
        $process = new Process($command);
        if (!is_null($pwd)) {
            $process->setWorkingDirectory($pwd);
        }
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }

    /**
     *
     * checkoutModule function
     *
     * @return bool | null
     */
    public static function checkoutModule($ref, $module_name): bool | null
    {
        $command = ['git', 'checkout', $ref];
        $pwd     = GitModuleHelper::buildModuleDir($module_name);
        $output  = self::process($command, $pwd);
        return $output == '';
    }

    /**
     * cloneModule function
     *
     * @return string | null
     */
    public static function cloneModule($repo_url, $target_folder = ''): string | null
    {
        if($target_folder != ''){
            $command = ['git', 'clone', $repo_url, $target_folder];
        } else {
            $command = ['git', 'clone', $repo_url];
        }
        $pwd     = GitModuleHelper::buildModuleDir('');
        $output  = self::process($command, $pwd);
        return $output;
    }

    /**
     * fetchModule function
     *
     * @return string | null
     */
    public static function fetchModule($module_name): string | null
    {
        // git fetch --prune remove
        $command = ['git', 'fetch', '--all', '--prune', '--prune-tags'];
        $pwd     = GitModuleHelper::buildModuleDir($module_name);
        $output  = self::process($command, $pwd);
        return $output;
    }

    /**
     * deleteModule function
     *
     * @return bool | null
     */
    public static function deleteModule($module_name): bool | null
    {
        // guard clause
        $pwd = GitModuleHelper::buildModuleDir($module_name);

        // if already non exist
        if (!file_exists($pwd)) return true;

        // guard clause to prevent any input error that
        // can remove unwanted directories
        $isModuleNameDots            = $module_name == '' || $module_name == '*' || $module_name == '.' || $module_name == '..';
        $isModuleDirPartOfBaseModule = GitModuleHelper::isDirInBaseModule($pwd);
        if ($isModuleNameDots || !$isModuleDirPartOfBaseModule) {
            throw new InvalidArgumentException(
                "There's some security issue with your targeted directory.. \n" .
                    "Module Name        : $module_name \n" .
                    "Targeted Directory : $pwd \n"
            );
        }

        // confirm for the last time, with the provided information
        // which folder that want to be deleted
        self::removeDirectories($pwd);
        return !file_exists($pwd);
    }

    /**
     * deleteModuleByRepo function
     *
     * @return bool | null
     */
    public static function deleteModuleByRepo($repo_url): bool | null
    {
        return GitModule::deleteModule(GitModuleHelper::buildModuleSlug(($repo_url)));
    }

    /**
     * Logs Operation
     * commit
     * tags
     * refs
     * branches
     * remote
     */

    /**
     *
     * getModuleGitBranchs function
     *
     * @return array | null
     */
    public static function getModuleBranches($module_name, $remote = null, $head = null): array | null
    {
        // git fetch --prune remove
        $command = ['git', 'branch', '-r', "--format='%(refname:short)[SEPARATOR]'"];
        $pwd     = GitModuleHelper::buildModuleDir($module_name);
        $output  = self::process($command, $pwd);
        $output  = explode('[SEPARATOR]',$output);
        return GitModuleHelper::formatModuleBranches($output, $module_name);
    }


    /**
     *
     * getModuleLog function
     *
     * @return array | ProcessFailedException | null
     */
    public static function getModuleLog($module_name, $remote = null, $head = null): array | null
    {
        // git log commit with pretty format
        // git log --pretty=format:'{ commit:%H, refs:%(decorate:prefix=\",suffix=\",separator=|,tag=), unix_time:%ct }'
        $remote_head_exist = !is_null($remote) && !is_null($head);
        if ($remote_head_exist) $command = ['git', 'log', $remote, $head, "--pretty=format:{ \"commit\":\"%H\", \"body\":\"%B\", \"refs\":\"%(decorate:prefix=,suffix=,separator=|,tag=)\", \"unix_time\":%ct }[EXPLODE]"];
        else                    $command = ['git', 'log', "--pretty=format:{ \"commit\":\"%H\", \"body\":\"%B\", \"refs\":\"%(decorate:prefix=,suffix=,separator=|,tag=)\", \"unix_time\":%ct }[EXPLODE]"];

        // proceed to get the output
        $pwd     = GitModuleHelper::buildModuleDir($module_name);
        $output  = self::process($command, $pwd);

        // return logs
        return GitModuleHelper::formatModuleLogsToJson($output, $module_name);
    }

    /**
     *
     * getModuleCommits function
     *
     * @return array | ProcessFailedException | null
     */
    public static function getModuleCommits($module_name, $remote = null, $head = null): array | ProcessFailedException | null
    {
        $logs = self::getModuleLog($module_name, $remote, $head);
        if (!is_array($logs)) {
            return $logs;
        }
        $commits = [];
        foreach ($logs as $log) {
            array_push($commits, $log->commit);
        }
        return $commits;
    }

   /**
     *
     * getModuleLogByTag function
     *
     * @return array | null
     */
    public static function getModuleLogByTag($module_name, $remote = null, $head = null): array | null
    {
        // git log commit with pretty format
        // git log --pretty=format:'{ commit:%H, refs:%(decorate:prefix=\",suffix=\",separator=|,tag=), unix_time:%ct }'
        $remote_head_exist = !is_null($remote) && !is_null($head);
        if ($remote_head_exist) $command = ['git', 'log', '--no-walk', '--tags', $remote, $head, "--pretty=format:{ \"commit\":\"%H\", \"body\":\"%B\", \"refs\":\"%(decorate:prefix=,suffix=,separator=|,tag=)\", \"unix_time\":%ct }[EXPLODE]"];
        else                    $command = ['git', 'log', '--no-walk', '--tags', "--pretty=format:{ \"commit\":\"%H\", \"body\":\"%B\", \"refs\":\"%(decorate:prefix=,suffix=,separator=|,tag=)\", \"unix_time\":%ct }[EXPLODE]"];
        // proceed to get the output
        $pwd     = GitModuleHelper::buildModuleDir($module_name);
        $output  = self::process($command, $pwd);
        // return logs
        return GitModuleHelper::formatModuleLogsToJson($output, $module_name);
    }

    /**
     *
     * getModuleLogByRef function
     *
     * @return stdClass | null
     */
    public static function getModuleLogByRef($module_name, $ref): stdClass | null
    {
        // this one doesn use remotes and branch because ref automatically sync with previous fetch
        $command = ['git', 'log', $ref, "--pretty=format:{ \"commit\":\"%H\", \"body\":\"%B\", \"refs\":\"%(decorate:prefix=,suffix=,separator=|,tag=)\", \"unix_time\":%ct }[EXPLODE]"];
        $pwd     = GitModuleHelper::buildModuleDir('');
        $output  = self::process($command, $pwd);
        // logs
        $logs = GitModuleHelper::formatModuleLogsToJson($output, $module_name);
        if (is_array($logs)) {
            if (count($logs) > 0) return $logs[0];
            else                  return null;
        } else {
            return $output;
        }
    }

    /**
     * getModuleCurrentLog function
     *
     * @param [type] $module_name
     * @param [type] $remote
     * @param [type] $head
     * @return object
     */
    public static function getModuleCurrentLog($module_name, $remote = null, $head = null): object
    {
        $logs = self::getModuleLog($module_name, $remote, $head);
        return count($logs) > 0 ? $logs[0] : null;
    }

    /**
     * getModuleCurrentLogTag function
     *
     * @param [type] $module_name
     * @param [type] $remote
     * @param [type] $head
     * @return object
     */
    public static function getModuleCurrentLogByTag($module_name, $remote = null, $head = null): object
    {
        $logs = self::getModuleLogByTag($module_name, $remote, $head);
        return count($logs) > 0 ? $logs[0] : null;
    }

    /**
     *
     * getModuleTags function
     *
     * @return array  null
     */
    public static function getModuleTags($module_name): array | null
    {
        $command = ['git', 'tag', '--sort=-v:refname'];
        $pwd     = GitModuleHelper::buildModuleDir($module_name);
        $output  = self::process($command, $pwd);
        return GitModuleHelper::formatModuleTags($output);
    }

    /**
     *
     * getModuleLatestTag function
     *
     * @return string | null
     */
    public static function getModuleLatestTag($module_name): string | null
    {
        // automatically sorted by the git command
        $tags = self::getModuleTags($module_name);
        return count($tags) > 0 ? $tags[0] : null;
    }

    /**
     * getModuleRemote function
     *
     * @return string
     */
    public static function getModuleSymbolicRef($module_name): string
    {
        $command = ['git', 'symbolic-ref', 'refs/remotes/origin/HEAD', '--short'];
        $pwd     = GitModuleHelper::buildModuleDir($module_name);
        // expected return origin/[branch-name]
        $output  = self::process($command, $pwd);
        return $output;
    }

    /**
     * getModuleRemote function
     *
     * @return array
     */
    public static function getModuleRemoteAndBranch($module_name): array
    {
        // expected return origin/[branch-name]
        $refs  = trim(self::getModuleSymbolicRef($module_name));
        $split = explode('/', $refs);
        if (count($split) > 0) return $split;
        else                   throw new Exception("expected return origin/[branch-name]");
        // expected [origin,[branch_name]]
    }

    /**
     *
     * getModuleCurrentCommit function
     *
     * @return string | null
     */
    public static function getModuleCurrentCommit($module_name, $remote = null, $head = null): string | null
    {
        $commits = self::getModuleCommits($module_name, $remote, $head);
        return !is_array($commits) ? $commits : $commits[0];
    }

    /**
    * Git File & Folder Operation
     */

    /**
     * readGitConfig function
     *
     * @return array
     */
    public static function readGitConfig($module_name): array
    {
        return self::readGitConfigByPath(self::buildModuleDir($module_name));
    }

    /**
     * readGitConfig function
     *
     * @return array
     */
    public static function readGitConfigByPath($module_path): array
    {
        $git_file = fopen($module_path . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'config', 'r');
        $configs = [];

        // proceed to read git config line_by_line.
        while (($line = fgets($git_file)) !== false) {
            $line = trim($line);

            // check if its parent config
            preg_match('/(?<=\[).+(?=])/', $line, $is_parent_config);

            if (count($is_parent_config) > 0) {
                $is_parent_config[0] = str_replace('"', '', $is_parent_config[0]);
                $parent_config = explode(' ', $is_parent_config[0]);

                array_push($configs, [
                    'name'  => count($parent_config) > 1 ? trim($parent_config[0]) : trim($parent_config[0]),
                    'value' => count($parent_config) > 1 ? trim($parent_config[1]) : null,
                    'properties' => [],
                ]);
            } else {
                $properties = explode('=', $line);
                array_push($configs[count($configs) - 1]['properties'], [
                    'name'  => count($properties) > 1 ? trim($properties[0]) : trim($properties[0]),
                    'value' => count($properties) > 1 ? trim($properties[1]) : null,
                ]);
            }
        }

        fclose($git_file);
        return $configs;
    }

    /**
     * removeDirectories function
     *
     * @return string | ProcessFailedException | null
     */
    public static function removeDirectories(string $dir_path): void
    {
        if (file_exists($dir_path)) {
            $di = new RecursiveDirectoryIterator($dir_path, FilesystemIterator::SKIP_DOTS);
            $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);

            // run recursive for each file
            foreach ($ri as $file) {
                // git config somehow can't be deleted due to permission denied
                $isDir = $file->isDir();
                $path = $file->getRealPath();

                // prevent permission error from deleting
                chmod($path, 0777);

                // delete folder if empty and delete file if not folder
                if ($isDir && GitModuleHelper::isEmptyDirectories($file->getPathname())) rmdir($file);
                if (!$isDir) unlink($file);
            }

            // prevent permission error from deleting
            chmod($dir_path, 0777);
            if (GitModuleHelper::isEmptyDirectories($dir_path)) rmdir($dir_path);
        }
    }

    // ======== OLD METHODS
    // Hapus atau tidak nih ?

    /**
     * fetch function
     *
     * @param [type] $module
     * @return void
     */
    public static function fetch($module = null): void
    {
        $directory = base_path('modules' . DIRECTORY_SEPARATOR . ($module ?: 'system'));

        $process = Process::fromShellCommandline('git fetch --all --tags --prune --prune-tags');
        $process->setWorkingDirectory($directory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * remoteCommitLast function
     *
     * @param [type] $module
     * @return string
     */
    public static function remoteCommitLast($module = null): string
    {
        $directory = base_path('modules' . DIRECTORY_SEPARATOR . ($module ?: 'system'));

        $process = Process::fromShellCommandline('git rev-parse `git branch -r --sort=committerdate | tail -1`');
        $process->setWorkingDirectory($directory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim(preg_replace('/\s+/', ' ', $process->getOutput()));
    }

    /**
     * localCommitLast function
     *
     * @param [type] $module
     * @return string
     */
    public static function localCommitLast($module = null): string
    {
        $directory = base_path('modules' . DIRECTORY_SEPARATOR . ($module ?: 'system'));

        $process = Process::fromShellCommandline('git rev-parse --verify HEAD 2> /dev/null');
        $process->setWorkingDirectory($directory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim(preg_replace('/\s+/', ' ', $process->getOutput()));
    }

    /**
     * remoteTagLast function
     *
     * @param [type] $module
     * @return string
     */
    public static function remoteTagLast($module = null): string
    {
        $directory = base_path('modules' . DIRECTORY_SEPARATOR . ($module ?: 'system'));

        $process = Process::fromShellCommandline('git tag | tail -1');
        $process->setWorkingDirectory($directory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim(preg_replace('/\s+/', ' ', $process->getOutput()));
    }

    /**
     * localTagLast function
     *
     * @param [type] $module
     * @return string
     */
    public static function localTagLast($module = null): string
    {
        $directory = base_path('modules' . DIRECTORY_SEPARATOR . ($module ?: 'system'));

        $process = Process::fromShellCommandline('git describe --exact-match --tags 2> /dev/null');
        $process->setWorkingDirectory($directory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim(preg_replace('/\s+/', ' ', $process->getOutput()));
    }
}

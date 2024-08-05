<?php

namespace CitraGroup\Platform\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use CitraGroup\Platform\Services\GitModule;

class GitModuleHelper
{
    /**
     * baseModuleDir function
     *
     * @return string
     */
    public static function baseModuleDir(): string
    {
        return base_path() . DIRECTORY_SEPARATOR . 'modules';
    }

    /**
     * buildModuleDir function
     *
     * @return string
     */
    public static function buildModuleDir($module_name): string
    {
        return self::baseModuleDir() . DIRECTORY_SEPARATOR . $module_name;
    }

    /**
     * buildModuleSlug function
     *
     * @return string | null
     */
    public static function buildModuleSlug($repo_url): string | null
    {
        // $repo_url = git@github.com:repoUser/repo.git | https://github.com/repoUser/repo.git
        $repo_url = str_replace('.git', '', $repo_url);
        $split    = explode('/', $repo_url);
        if (count($split) <= 0) return null;
        return $split[count($split) - 1];
    }

    /**
     * isModuleExist function
     *
     * @return bool
     */
    public static function isModuleExist($module_name): bool
    {
        // prevent return true because '' should be invalid
        if ($module_name == '' || $module_name == '.' || $module_name == '..') return false;
        return is_dir(self::buildModuleDir($module_name));
    }

    /**
     * isModuleExistByRepo function
     *
     * @return bool
     */
    public static function isModuleExistByRepo($repo_url): bool
    {
        return self::isModuleExist(self::buildModuleSlug($repo_url));
    }

    /**
     * isEmptyDirectories function
     *
     * @return bool
     */
    public static function isEmptyDirectories($dir)
    {
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

   /**
     * isDirInBaseModule function
     *
     * @return bool
     */
    public static function isDirInBaseModule($directory): bool
    {
        $base_dir   = explode(DIRECTORY_SEPARATOR, self::baseModuleDir());
        $target_dir = explode(DIRECTORY_SEPARATOR, $directory);
        // prevent error cause index not found
        if (count($target_dir) < count($base_dir)) {
            return false;
        }
        // start to check each dir path
        foreach ($base_dir as $idx => $dir) {
            if ($base_dir[$idx] != $target_dir[$idx]) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * formatModuleTags function
     *
     * @return array | null
     */
    public static function formatModuleTags($raw_tags): array | null
    {
        preg_match_all('/.+/', $raw_tags, $tags);
        return count($tags) > 0 ? $tags[0] : [];
    }

    /**
     *
     * filterModuleRefsAsTag function
     *
     * @return array
     */
    public static function filterModuleRefsAsTag($refs, $remote, $branch): array
    {
        $result = [];
        foreach ($refs as $ref) {
            // boolean
            $isNotRemote = !str_contains($ref, $remote) && !str_contains($ref, $branch);
            $isNotHead = !str_contains($ref, 'HEAD') && !str_contains($ref, 'head');
            // determine if used remote and branch
            $isUsedRemote = !is_null($remote) && !is_null($branch);
            $isValidTags = $isNotHead && $isNotRemote && $isUsedRemote || $isNotHead && !$isUsedRemote;
            // determine condition
            if ($isValidTags) array_push($result, $ref);
        }
        return $result;
    }

    /**
     *
     * filterModuleRefsAsBranches function
     *
     * @return array
     */
    public static function filterModuleRefsAsBranches($log): array
    {
        $result = [];
        foreach ($log->refs as $ref) {
            // is not a tags
            if ( !in_array($ref, $log->tags) ) {
                // just incase remote/branch
                $split = explode( '/', $ref );
                if(count($split)>1){
                    $branch = trim($split[1]);
                } else {
                    $branch = trim($split[0]);
                }
                // just incase 2 head -> branch
                $split = explode("->", $branch);
                if(count($split)>1){
                    $branch = trim($split[1]);
                } else {
                    $branch = trim($split[0]);
                }
                // and is not head
                $isNotHead = !str_contains($branch, 'HEAD') && !str_contains($branch, 'head');
                // check if already appended
                if( !in_array($branch, $result) && $isNotHead ){
                    array_push($result,$branch);
                }
            }
        }
        return $result;
    }

    /**
     *
     * formatModuleLogsToJson function
     *
     * @return array
     */
    public static function formatModuleLogsToJson($logs_string, $module_name): array
    {
        // get all the logs
        $logs = explode('[EXPLODE]', $logs_string);
        $result = [];

        // filter remotes refs
        $remote = GitModule::getModuleRemote($module_name);
        $branch = GitModule::getModuleBranchLocal($module_name);

        // formatting all the log as a json object
        foreach ($logs as $log) {
            // don't know why but \n cause json_decode
            // return null don't know if it works in unix
            $log = trim($log);
            $log = preg_replace('/\n+/', '\r\n', $log);

            // convert it to json object
            $json   = json_decode($log, true);
            $object = json_decode(json_encode($json), false);

            // if successfully converted into object then
            // append to the array
            if (!is_null($object)) {
                $object->refs = explode("|", $object->refs);
                $object->tags = self::filterModuleRefsAsTag($object->refs, $remote, $branch);
                $object->branches = self::filterModuleRefsAsBranches($object);
                array_push($result, $object);
            }
        }
        return $result;
    }

    /**
     *
     * formatModuleBranches function
     *
     * @return array
     */
    public static function formatModuleBranches($branches, $module_name): array
    {
        $result = [];
        foreach ($branches as $branch) {
            // just incase 2 remote/branch -> remote/branch
            $split = explode("->", $branch);
            $branch = $split[0];
            // just incase remote/branch
            $split = explode( '/', $branch );
            if(count($split)>1){
                $branch = trim($split[1]);
                // and is not head
                $isNotHead = !str_contains($branch, 'HEAD') && !str_contains($branch, 'head');
                // check if already appended
                if( $isNotHead ) array_push($result,$branch);
            }
        }
        return $result;
    }
}

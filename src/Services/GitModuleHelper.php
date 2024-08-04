<?php

namespace CitraGroup\Platform\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
    public static function buildModuleDir($module_slug): string
    {
        return self::baseModuleDir() . DIRECTORY_SEPARATOR . $module_slug;
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
    public static function isModuleExist($module_slug): bool
    {
        // prevent return true because '' should be invalid
        if ($module_slug == '' || $module_slug == '.' || $module_slug == '..') return false;
        return is_dir(self::buildModuleDir($module_slug));
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
    public function formatModuleTags($raw_tags): array | null
    {
        preg_match_all('/.+/', $raw_tags, $tags);
        return count($tags) > 0 ? $tags[0] : [];
    }

}

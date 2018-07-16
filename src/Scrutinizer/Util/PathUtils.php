<?php

namespace Scrutinizer\Util;

abstract class PathUtils
{
    public static function isFiltered($path, array $filter)
    {
        $paths = $filter['paths'] ?? [];
        $pathMatch = self::getLongestMatch($path, $paths);
        if ($paths && $pathMatch === null) {
            return true;
        }

        $excludedMatch = self::getLongestMatch($path, $filter['excluded_paths'] ?? []);
        if ($excludedMatch) {
            return $pathMatch === null || strlen($excludedMatch) > strlen($pathMatch);
        }

        return false;
    }

    public static function matches($path, array $patterns)
    {
        return self::getLongestMatch($path, $patterns) !== null;
    }
    
    private static function getLongestMatch(string $path, array $patterns): ?string
    {
        $currentMatch = null;
        foreach ($patterns as $pattern) {
            if (($currentMatch === null || strlen($pattern) > strlen($currentMatch))
                    && fnmatch($pattern, $path)) {
                $currentMatch = $pattern;
            }
        }

        return $currentMatch;
    }

    final private function __construct() { }
}
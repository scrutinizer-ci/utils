<?php

namespace Scrutinizer\Util;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class YamlUtils
{
    /**
     * Safely parses the input string.
     *
     * In contrast to the original Yaml class, we do not try to resolve the input to a filename first. Consequentially,
     * there is no need to sanitize the input prior to calling this method.
     *
     * @param string $input
     *
     * @return mixed
     */
    public static function safeParse($input)
    {
        Yaml::setPhpParsing(false);

        return (new Parser())->parse($input);
    }
}
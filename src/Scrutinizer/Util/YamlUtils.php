<?php

namespace Scrutinizer\Util;

use Symfony\Component\Yaml\Exception\ParseException;
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
        if (method_exists('Symfony\Component\Yaml\Yaml', 'setPhpParsing')) {
            Yaml::setPhpParsing(false);
        }

        self::validateDuplicatedKey($input);

        return (new Parser())->parse($input, true, false);
    }

    /**
     * Validate if there is duplicated keys in the input yml string
     *
     * @param string $input
     * @return bool
     */
    private static function validateDuplicatedKey($input) {

        $lines = explode("\n", $input);
        $data = array();
        $nbSpacesOfLastLine = 0;
        
        foreach ($lines as $linenumber => $line) {
            $trimmedLine = ltrim($line, ' ');
            if ($trimmedLine === '' || $trimmedLine[0] === '#') {
                continue;
            }

            $nbSpacesOfCurrentLine = strlen($line) - strlen($trimmedLine);

            if ($nbSpacesOfCurrentLine < $nbSpacesOfLastLine) {
                foreach ($data as $nbSpaces => $keys) {
                    if ($nbSpaces > $nbSpacesOfCurrentLine) {
                        unset($data[$nbSpaces]);
                    }
                }
            }

            if ($trimmedLine[0] === '-' || false === $pos = strpos($trimmedLine, ':')) {
                continue;
            }

            $key = substr($trimmedLine, 0, $pos);

            if (isset($data[$nbSpacesOfCurrentLine][$key])) {
                throw new ParseException(sprintf('Duplicate key "%s" detected on line %s whilst parsing YAML.', $key, $linenumber));
            }

            $data[$nbSpacesOfCurrentLine][$key] = array();
            $nbSpacesOfLastLine = $nbSpacesOfCurrentLine;
        }
        return;
    }
}
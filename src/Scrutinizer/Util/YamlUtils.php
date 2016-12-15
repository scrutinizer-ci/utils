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

        if (!self::validateDuplicatedKey($input)){
            throw new ParseException("Duplicate key detected while parsing YAML :{".$input."}");
        }

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
        $indentationofLastline = 0;
        
        foreach ($lines as $linenumber => $line) {
            $indentationofCurrentLine = strlen($line) - strlen(ltrim($line, ' '));
            $value=trim($line);
            if (false !== strpos($value, ':')) {
                $key = substr($value, 0, strpos($value, ":"));
            }  else {
                $key = $value;
            }

            if ($indentationofCurrentLine > $indentationofLastline) {
                $data[$indentationofCurrentLine][$key] = array();
            } elseif ($indentationofCurrentLine === $indentationofLastline) {
                if (false === strpos($key, '-') && isset($data[$indentationofCurrentLine][$key])) {
                    return false;
                } else {
                    $data[$indentationofCurrentLine][$key] = array();
                }
            } else {
                foreach ($data as $indentation => $keys) {
                    if ($indentation > $indentationofCurrentLine) {
                        unset($data[$indentation]);
                    }
                }
                if (false === strpos($key, '-') && isset($data[$indentationofCurrentLine][$key])) {
                    return false;
                } else {
                    $data[$indentationofCurrentLine][$key] = array();
                }
            }
            $indentationofLastline = $indentationofCurrentLine;
        }
        return true;
    }
}
<?php

namespace Scrutinizer\Tests\Util;

use Scrutinizer\Util\YamlUtils;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testSafeParseDoesNotResolveFilename()
    {
        $this->assertEquals(__FILE__, YamlUtils::safeParse(__FILE__));
    }


    public function testDuplicatedKeyInGeneral()
    {
        $input = <<<YAML
build:
    test:
        override:
            - test
build:
    environment: true
YAML;

        $this->setExpectedException(ParseException::class);
        $actualArray = YamlUtils::safeParse($input);

    }

    public function testDuplicatedKeyInChildNode()
    {
        $input = <<<YAML
build:
    test:
        override:
            - test
    test:
        before:
            - test
YAML;

        $this->setExpectedException(ParseException::class);
        $actualArray = YamlUtils::safeParse($input);
    }

    public function testDuplicatedContentSequencesShouldNotThrowException()
    {
        $input = <<<YAML
build:
    test:
        override:
            - test
            - test
        
YAML;

        $actualArray = YamlUtils::safeParse($input);
        $expectedArray = array(
            'build' => array(
                'test' => array(
                    'override' => array(
                        'test', 'test'
                    )
                )
            )
        );

        $this->assertEquals($expectedArray, $actualArray);
    }

    public function testSequencesWithChildrenArray()
    {
        $input = <<<YAML
build:
    test:
        override:
            - 
                command: 
                    analysis: true
            - 
                command: 
                    analysis: true
YAML;

        $actualArray = YamlUtils::safeParse($input);
        $expectedArray = array(
            'build' => array(
                'test' => array(
                    'override' => array(
                        array(
                            'command' => array(
                                'analysis' => true
                            )
                        ),
                        array(
                            'command' => array(
                                'analysis' => true
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals($expectedArray, $actualArray);
    }
}
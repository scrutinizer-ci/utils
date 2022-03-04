<?php

namespace Scrutinizer\Tests\Util;

use PHPUnit\Framework\TestCase;
use Scrutinizer\Util\YamlUtils;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlUtilsTest extends TestCase
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

        $this->expectException(ParseException::class);
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

        $this->expectException(ParseException::class);
        $actualArray = YamlUtils::safeParse($input);
    }

    public function testDuplicatedKeyIgnoresComments()
    {
        $input = <<<YAML

abc:
    # https:
    # https:
    foo: bar

YAML;

        $this->assertEquals(['abc' => ['foo' => 'bar']], YamlUtils::safeParse($input));
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

    public function testDockerConfigYml()
    {
        $input = <<<YAML
build:
    environment:
        docker:
            logins:
            - { username: "my-user", password: "my-password" } # DockerHub
            - "scrutinizer/*"
YAML;

        $actualArray = YamlUtils::safeParse($input);
        $expectedArray = array(
            'build' => array(
                'environment' => array(
                    'docker' => array(
                        'logins' => array(
                            array(
                            'username' => 'my-user',
                            'password' => 'my-password'
                            ),
                            'scrutinizer/*'
                        )
                    )
                )
            )
        );

        $this->assertEquals($expectedArray, $actualArray);
    }

    public function testDockerCijionfigYml()
    {
        $input = <<<YAML
build:
    environment:
        docker:
            logins:
            - { username: "my-user", password: "my-password" } # DockerHub
            - "scrutinizer/*"
YAML;


        try {
            $actualArray = YamlUtils::safeParse($input);
            $this->assertNotEmpty($actualArray);
        } catch (ParseException $ex) {
            throw new \RuntimeException("Unexpected ParseException thrown: ", $ex);
        }
    }

    public function testWithBuildConfigSample()
    {
        $input = <<<YAML
build:
    tests:
        before:
            - 'this-is-a-simple-command'
            -
                command: 'this-is-a-complex-command'
                environment: { ABC: 'foo' }
                not_if: 'test -e foo/bar'
                only_if: 'test -e bar/baz'
                idle_timeout: 600
                background: true
                on_node: 1
YAML;

        try {
            $actualArray = YamlUtils::safeParse($input);
            $this->assertNotEmpty($actualArray);
        }catch (ParseException $ex) {
            throw new \RuntimeException("Unexpected ParseException thrown: ", $ex);
        }
    }

    public function testBuildConfigSampleCaseTwo()
    {
        $input = <<<YAML
build:
    dependencies:
        # Runs before inferred commands
        before:
            - 'gem install abc'
            - 'pecl install abc'
            - 'pip install Abc'

        # Overwrites inferred commands
        override:
            - 'some-command'

        # Runs after inferred commands
        after:
            - 'some-command'


    # Run after dependencies
    project_setup:
        before:
            - mysql -e "CREATE DATABASE abc"

        override: []
        after: []
YAML;

        try {
            $actualArray = YamlUtils::safeParse($input);
            $this->assertNotEmpty($actualArray);
        }catch (ParseException $ex) {
            throw new \RuntimeException("Unexpected ParseException thrown: ", $ex);
        }
    }

    public function testApacheBuildConfig()
    {
        $input = <<<YAML
build:
    environment:
        apache2:
            modules: ['rewrite']
            sites:
                symfony_app:
                    web_root: 'web/'
                    host: 'local.dev'
                    rules:
                        - 'RewriteCond %{HTTP_REFERER} !^$'
                        - 'RewriteCond %{HTTP_REFERER} !^http://(www.)?example.com/ [NC]'
                        - 'RewriteRule .(gif|jpg|png)$ - [F]'
YAML;

        try {
            $actualArray = YamlUtils::safeParse($input);
            $this->assertNotEmpty($actualArray);
        }catch (ParseException $ex) {
            throw new \RuntimeException("Unexpected ParseException thrown: ", $ex);
        }

    }
}

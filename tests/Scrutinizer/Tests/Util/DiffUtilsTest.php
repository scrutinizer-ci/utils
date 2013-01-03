<?php

/*
 * Copyright 2013 Johannes M. Schmitt <schmittjoh@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Scrutinizer\Tests\Util;

use Scrutinizer\Util\DiffUtils;

class DiffUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getParserAndGeneratorTests
     */
    public function testGenerate($diff, $before, $after)
    {
        $this->assertEquals($diff, DiffUtils::generate($before, $after));
    }

    /**
     * @dataProvider getParserAndGeneratorTests
     */
    public function testApply($diff, $before, $after)
    {
        $this->assertEquals($after, DiffUtils::apply($before, $diff));
        $this->assertEquals($before, DiffUtils::reverseApply($after, $diff));
    }

    public function getParserAndGeneratorTests()
    {
        $tests = array();
        $tests[] = array("@@ -0,0 +1 @@\n+foo\n\\ No newline at end of file\n", '', 'foo');
        $tests[] = array('', 'foo', 'foo');
        $tests[] = array("@@ -1 +1 @@\n-foo\n\\ No newline at end of file\n+asdf\n\\ No newline at end of file\n", "foo", "asdf");

        return $tests;
    }

    public function testParseEmptyDiff()
    {
        $this->assertEquals(array(), DiffUtils::parseDiffs(''));
    }

    public function testDiffBinaryFiles()
    {
        $diffs = DiffUtils::parseDiffs(file_get_contents(__DIR__.'/Fixture/binary_files.diff'));

        $this->assertCount(1, $diffs);
        $this->assertEquals('src/Acme/DemoBundle/Resources/public/images/welcome-demo.gif', $diffs[0]['a_path']);
        $this->assertEquals('src/Acme/DemoBundle/Resources/public/images/welcome-demo.gif', $diffs[0]['b_path']);
        $this->assertEquals('0623de5', $diffs[0]['a_sha']);
        $this->assertEquals('0000000', $diffs[0]['b_sha']);
        $this->assertEquals('100644', $diffs[0]['a_mode']);
        $this->assertNull($diffs[0]['b_mode']);
        $this->assertFalse($diffs[0]['is_new']);
        $this->assertTrue($diffs[0]['is_deleted']);
        $this->assertFalse($diffs[0]['is_renamed']);
        $this->assertNull($diffs[0]['diff']);
        $this->assertNull($diffs[0]['sim_index']);
    }

    public function testDiff()
    {
        $diffs = DiffUtils::parseDiffs(file_get_contents(__DIR__.'/Fixture/one_modified_one_removed.diff'));

        $this->assertCount(3, $diffs);

        $this->assertEquals('src/CheckAccessControlPass.php', $diffs[0]['a_path']);
        $this->assertEquals('src/CheckAccessControlPass.php', $diffs[0]['b_path']);
        $this->assertEquals('a5c01ca', $diffs[0]['a_sha']);
        $this->assertEquals('f781f8f', $diffs[0]['b_sha']);
        $this->assertNull($diffs[0]['a_mode']);
        $this->assertEquals('100644', $diffs[0]['b_mode']);
        $this->assertFalse($diffs[0]['is_new']);
        $this->assertFalse($diffs[0]['is_deleted']);
        $this->assertFalse($diffs[0]['is_renamed']);
        $this->assertNull($diffs[0]['sim_index']);
        $this->assertStringStartsWith('@@', $diffs[0]['diff']);

        $this->assertEquals('src/PhpParser/Scope/Scope.php', $diffs[1]['a_path']);
        $this->assertEquals('src/PhpParser/Scope/Scope.php', $diffs[1]['b_path']);
        $this->assertEquals('0110ef0', $diffs[1]['a_sha']);
        $this->assertEquals('facb0c0', $diffs[1]['b_sha']);
        $this->assertNull($diffs[1]['a_mode']);
        $this->assertEquals('100644', $diffs[1]['b_mode']);
        $this->assertFalse($diffs[1]['is_new']);
        $this->assertFalse($diffs[1]['is_deleted']);
        $this->assertFalse($diffs[1]['is_renamed']);
        $this->assertStringStartsWith('@@', $diffs[0]['diff']);
        $this->assertNull($diffs[1]['sim_index']);

        $this->assertEquals('tests/Fixture/AccessControl/call_from_non_object_context.test', $diffs[2]['a_path']);
        $this->assertEquals('tests/Fixture/AccessControl/call_from_non_object_context.test', $diffs[2]['b_path']);
        $this->assertEquals('5c7ab73', $diffs[2]['a_sha']);
        $this->assertEquals('0000000', $diffs[2]['b_sha']);
        $this->assertEquals('100644', $diffs[2]['a_mode']);
        $this->assertNull($diffs[2]['b_mode']);
        $this->assertFalse($diffs[2]['is_new']);
        $this->assertTrue($diffs[2]['is_deleted']);
        $this->assertFalse($diffs[2]['is_renamed']);
        $this->assertStringStartsWith('@@', $diffs[2]['diff']);
        $this->assertNull($diffs[2]['sim_index']);
    }
}
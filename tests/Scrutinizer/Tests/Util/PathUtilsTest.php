<?php

namespace Scrutinizer\Tests\Util;

use Scrutinizer\Util\PathUtils;

class PathUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getMatchingTests
     */
    public function testMatches($path, array $patterns, $expectedOutcome)
    {
        $this->assertSame($expectedOutcome, PathUtils::matches($path, $patterns));
    }

    public function getMatchingTests()
    {
        return array(
            array('foo/bar', array('foo/*'), true),
            array('foo/bar', array('foo'), false)
        );
    }

    /**
     * @dataProvider getFilteringTests
     */
    public function testIsFiltered($path, array $includedPaths, array $excludedPaths, $expectedOutcome)
    {
        $this->assertSame($expectedOutcome, PathUtils::isFiltered($path, array('paths' => $includedPaths, 'excluded_paths' => $excludedPaths)));
    }

    public function getFilteringTests()
    {
        return array(
            array('foo', array(), array(), false),
            array('foo', array('foo'), array('foo/bar'), false),
            array('foo/bar', array('foo/*'), array('foo/bar'), true),
        );
    }
}
<?php

namespace Scrutinizer\Tests\Util;

use Scrutinizer\Util\YamlUtils;

class YamlUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testSafeParseDoesNotResolveFilename()
    {
        $this->assertEquals(__FILE__, YamlUtils::safeParse(__FILE__));
    }
}
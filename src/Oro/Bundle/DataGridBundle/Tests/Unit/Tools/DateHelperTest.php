<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Tools;

use Oro\Bundle\DataGridBundle\Tools\DateHelper;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class DateHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|LocaleSettings */
    private $localeSettings;

    /** @var DateHelper */
    private $dateHelper;

    protected function setUp()
    {
        $this->localeSettings = $this->createMock(LocaleSettings::class);

        $this->dateHelper = new DateHelper($this->localeSettings);
    }

    public function testGetTimeZoneOffset()
    {
        $this->localeSettings->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('Europe/Kiev');

        self::assertEquals('+03:00', $this->dateHelper->getTimeZoneOffset());
        // test that the offset is cached
        self::assertEquals('+03:00', $this->dateHelper->getTimeZoneOffset());
    }

    public function testGetTimeZoneOffsetForUTC()
    {
        $this->localeSettings->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('UTC');

        self::assertEquals('+00:00', $this->dateHelper->getTimeZoneOffset());
    }

    public function testGetConvertTimezoneExpression()
    {
        $this->localeSettings->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('Europe/Kiev');

        self::assertEquals(
            'CONVERT_TZ(e.createdAt, \'+00:00\', \'+03:00\')',
            $this->dateHelper->getConvertTimezoneExpression('e.createdAt')
        );
    }

    public function testGetConvertTimezoneExpressionForUTC()
    {
        $this->localeSettings->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('UTC');

        self::assertEquals(
            'e.createdAt',
            $this->dateHelper->getConvertTimezoneExpression('e.createdAt')
        );
    }
}

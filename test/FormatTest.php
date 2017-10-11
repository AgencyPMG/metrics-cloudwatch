<?php declare(strict_types=1);

/**
 * This file is part of pmg/metrics
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\Metrics\CloudWatch;

use PMG\Metrics\Gauge;
use PMG\Metrics\MetricName;
use PMG\Metrics\CloudWatch\Exception\UnsupportedMetric;

class FormatTest extends TestCase
{
    public function testNameDoesNotIncludeDimensionsWhenNoneArePresent()
    {
        $result = Format::name(new MetricName('example'));

        $this->assertSame(['MetricName' => 'example'], $result);
    }

    public function testNameRendersDimensionsWhenPresent()
    {
        $result = Format::name(new MetricName('example', ['one' => '1']));

        $this->assertSame([
            'MetricName' => 'example',
            'Dimensions' => [
                ['Name' => 'one', 'Value' => '1'],
            ],
        ], $result);
    }

    public function testGaugeErrorsWhenGivenAnUnknownUnit()
    {
        $this->expectException(UnsupportedMetric::class);
        Format::gauge(new MetricName('t'), new Gauge(1, 'UnKnownUnit'));
    }

    public function testGaugeFormatsForCloudWatchAsExpected()
    {
        $result = Format::gauge(new MetricName('t'), Gauge::count(1, new \DateTime()));

        $this->assertEquals(1, $result['Value']);
        $this->assertEquals('Count', $result['Unit']);
        $this->assertArrayHasKey('Timestamp', $result);
    }
}

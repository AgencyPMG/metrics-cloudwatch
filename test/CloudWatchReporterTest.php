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

use Aws\CommandInterface;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Exception\AwsException;
use PMG\Metrics\Gauge;
use PMG\Metrics\Metric;
use PMG\Metrics\MetricName;
use PMG\Metrics\MetricSet;
use PMG\Metrics\CloudWatch\Exception\CloudWatchError;

class CloudWatchReporterTest extends TestCase
{
    const NS = 'PMG/Metrics';

    private $cloudwatch, $reporter, $metricName;

    public function testReportWrapsErrorsFromTheCloudWatchClient()
    {
        $this->expectException(CloudWatchError::class);
        $set = new MetricSet($this->fakeMetrics(Gauge::count(10)));
        $this->cloudwatch->expects($this->once())
            ->method('putMetricData')
            ->willThrowException(new AwsException('oops', $this->createMock(CommandInterface::class)));

        $this->reporter->reportOn($set);
    }

    public function testReporterDoesNothingWhenThereAreNoMetricsToSend()
    {
        $set = new MetricSet([]);
        $this->cloudwatch->expects($this->never())
            ->method('putMetricData');

        $this->reporter->reportOn($set);
    }

    public function testReporterSendsGaugeDataToCloudWatch()
    {
        $set = new MetricSet($this->fakeMetrics(Gauge::count(10)));
        $this->cloudwatch->expects($this->once())
            ->method('putMetricData')
            ->with($this->callback(function (array $in) {
                $this->assertEquals(self::NS, $in['Namespace']);
                $this->assertCount(1, $in['MetricData']);
                return true;
            }));

        $this->reporter->reportOn($set);
    }

    protected function setUp() : void
    {
        $this->cloudwatch = $this->getMockBuilder(CloudWatchClient::class)
            ->setMethods(['putMetricData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->reporter = new CloudWatchReporter(self::NS, $this->cloudwatch);
        $this->metricName = new MetricName('test');
    }

    private function fakeMetrics(Metric $m) : \Generator
    {
        yield $this->metricName => $m;
    }
}

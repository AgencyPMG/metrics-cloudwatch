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

use Aws\Exception\AwsException;
use Aws\CloudWatch\CloudWatchClient;
use PMG\Metrics\Gauge;
use PMG\Metrics\MetricSet;
use PMG\Metrics\MetricName;
use PMG\Metrics\Reporter;
use PMG\Metrics\CloudWatch\Exception\CloudWatchError;

/**
 * A reporter that sends metrics to AWS CloudWatch
 *
 * @since 0.1
 */
final class CloudWatchReporter implements Reporter
{
    /**
     * @var string
     */
    private $metricNamespace;

    /**
     * @var CloudWatchClient
     */
    private $cloudwatch;

    public function __construct(string $metricNamespace, CloudWatchClient $cloudwatch)
    {
        $this->metricNamespace = $metricNamespace;
        $this->cloudwatch = $cloudwatch;
    }

    public function reportOn(MetricSet $set) : void
    {
        $metricData = $this->buildMetricData($set);
        if (!$metricData) {
            return;
        }

        try {
            $this->cloudwatch->putMetricData([
                'Namespace' => $this->metricNamespace,
                'MetricData' => $metricData,
            ]);
        } catch (AwsException $e) {
            throw CloudWatchError::from($e);
        }
    }

    private function buildMetricData(MetricSet $set) : array
    {
        $md = [];
        foreach ($set->getGauges() as $name => $gauge) {
            $md[] = Format::gauge($name, $gauge);
        }

        return $md;
    }
}

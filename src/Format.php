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
use PMG\Metrics\Metric;
use PMG\Metrics\MetricName;
use PMG\Metrics\CloudWatch\Exception\UnsupportedMetric;

/**
 * Some pure, static helpers for turning pmg/metrics objects into things
 * suitable for the cloud watch client.
 *
 * @since 0.1
 * @internal
 */
final class Format
{
    private static $gaugeUnitsToAws = [
        Gauge::UNIT_COUNT => 'Count',
    ];

    public static function name(MetricName $name) : array
    {
        $dims = [];
        foreach ($name->getDimensions() as $key => $val) {
            $dims[] = [
                'Name' => $key,
                'Value' => $val,
            ];
        }

        return array_filter([
            'MetricName' => $name->getName(),
            'Dimensions' => $dims,
        ]);
    }

    public static function gauge(MetricName $name, Gauge $gauge) : array
    {
        $unit = $gauge->getUnit();
        if (!isset(self::$gaugeUnitsToAws[$unit])) {
            throw new UnsupportedMetric(sprintf(
                'Cannot convert %s gauge unit to a cloudwatch unit, valid units: %s',
                $unit,
                implode(', ', array_keys(self::$gaugeUnitsToAws))
            ));
        }

        return self::maybeAddTimestamp(self::name($name) + [
            'Value' => $gauge->getValue(),
            'Unit' => self::$gaugeUnitsToAws[$unit],
        ], $gauge);
    }

    private static function maybeAddTimestamp(array $out, Metric $metric) : array
    {
        if ($ts = $metric->getTimestamp()) {
            $out['Timestamp'] = $ts;
        }

        return $out;
    }
}

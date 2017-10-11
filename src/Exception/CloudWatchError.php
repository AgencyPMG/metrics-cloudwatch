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

namespace PMG\Metrics\CloudWatch\Exception;

use Aws\Exception\AwsException;
use PMG\Metrics\MetricsException;

final class CloudWatchError extends \RuntimeException implements MetricsException
{
    public static function from(AwsException $e) : self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}

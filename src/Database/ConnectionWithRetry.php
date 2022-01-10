<?php

declare(strict_types=1);

namespace Keboola\JobQueueSharedBundle\Database;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\ParameterType;
use Retry\RetryProxy;

class ConnectionWithRetry extends DBALConnection
{
    private ?RetryProxy $retryProxy;

    public function setRetryProxy(?RetryProxy $retryProxy): void
    {
        $this->retryProxy = $retryProxy;
    }

    public function connect()
    {
        return $this->call(fn () => parent::connect());
    }

    public function query(...$args)
    {
        return $this->call(fn () => parent::query(...$args));
    }

    public function exec($sql)
    {
        return $this->call(fn () => parent::exec($sql));
    }

    public function executeQuery($query, array $params = array(), $types = array(), QueryCacheProfile $qcp = null)
    {
        return $this->call(fn () => parent::executeQuery($query, $params, $types, $qcp));
    }

    public function executeStatement($sql, array $params = [], array $types = [])
    {
        return $this->call(fn () => parent::executeStatement($sql, $params, $types));
    }

    public function quote($value, $type = ParameterType::STRING)
    {
        return $this->call(fn () => parent::quote($value, $type));
    }

    public function beginTransaction()
    {
        return $this->call(fn () => parent::beginTransaction());
    }

    public function commit()
    {
        return $this->call(fn () => parent::commit());
    }

    public function rollBack()
    {
        return $this->call(fn () => parent::rollBack());
    }

    private function call(callable $fn)
    {
        if ($this->retryProxy !== null) {
            return $this->retryProxy->call($fn);
        }

        return $fn();
    }
}

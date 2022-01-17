<?php

declare(strict_types=1);

namespace Keboola\JobQueueSharedBundle\Database;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Retry\RetryProxy;

class ConnectionWithRetry extends DBALConnection
{
    private ?RetryProxy $retryProxy;

    public function setRetryProxy(?RetryProxy $retryProxy): void
    {
        $this->retryProxy = $retryProxy;
    }

    public function connect(): bool
    {
        return $this->call(fn () => parent::connect());
    }

    public function query(string $sql): Result
    {
        return $this->call(fn () => parent::query($sql));
    }

    public function exec(string $sql): int
    {
        return $this->call(fn () => parent::exec($sql));
    }

    /**
     * @inheritdoc
     */
    public function executeQuery(
        string $sql,
        array $params = [],
        $types = [],
        ?QueryCacheProfile $qcp = null
    ): Result {
        return $this->call(fn () => parent::executeQuery($sql, $params, $types, $qcp));
    }

    /**
     * @inheritdoc
     */
    public function executeStatement($sql, array $params = [], array $types = []): int
    {
        return $this->call(fn () => parent::executeStatement($sql, $params, $types));
    }

    /**
     * @inheritdoc
     */
    public function quote($value, $type = ParameterType::STRING)
    {
        return $this->call(fn () => parent::quote($value, $type));
    }

    public function beginTransaction(): bool
    {
        return $this->call(fn () => parent::beginTransaction());
    }

    public function commit(): bool
    {
        return $this->call(fn () => parent::commit());
    }

    public function rollBack(): bool
    {
        return $this->call(fn () => parent::rollBack());
    }

    /**
     * @param callable(): T $fn
     * @return T
     *
     * @template T
     */
    private function call(callable $fn)
    {
        if ($this->retryProxy !== null) {
            $result = $this->retryProxy->call($fn);

            /** @var T $result */
            return $result;
        }

        return $fn();
    }
}

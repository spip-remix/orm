<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connector;

use SpipRemix\Component\Orm\ConnectionInterface;
use SpipRemix\Component\Orm\ConnectorInterface;
use SpipRemix\Component\Orm\SqlQueryBuilderInterface;

abstract class AbstractConnector implements ConnectorInterface
{
    private ?SqlQueryBuilderInterface $queryBuilder;

    /** @var resource|\Pdo|null */
    protected mixed $handle;

    /** @var resource|\Pdo|null */
    protected mixed $alter_handle;

    private array $mapping = [
        // 'meta' => 'Meta',
    ];

    public function __construct(
        protected string $name,
        protected string $driver,
        private ConnectionInterface $Connection,
        protected string $table_prefix = 'spip',
        protected string $base = '',
        ?string $filename = null,
        ?string $socket = null,
        ?string $hostname = null,
        ?int $port = null,
        protected ?string $username = null,
        #[\SensitiveParameter]
        protected ?string $password = null,
        protected ?string $alter_username = null,
        #[\SensitiveParameter]
        protected ?string $alter_password = null,
        protected array $options = [],
    ) {
    }

    public function setQueryBuilder(string|SqlQueryBuilderInterface $queryBuilder): void
    {
        if (\is_string($queryBuilder)) {
            $queryBuilder = new $queryBuilder($this->getTablePrefix());
        }

        $this->queryBuilder = $queryBuilder;
    }

    abstract public static function getServer(): string;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getTablePrefix(): string
    {
        return $this->table_prefix;
    }

    public function alter_connect(): static
    {
        if (\is_null($this->alter_username)) {
            return $this->connect();
        }

        try {
            $connector = clone $this;
            if (!isset($connector->alter_handle)) {
                $connector->handle = new \PDO(
                    $connector->Connection->getPdoString(),
                    $connector->alter_username,
                    $connector->alter_password,
                    $connector->options
                );
            }

            return $connector;
        } catch (\Throwable $th) {
            echo $th->getCode() . \PHP_EOL . $th->getMessage() . \PHP_EOL;
            exit(1);
        }
    }

    public function connect(): static
    {
        try {
            $connector = clone $this;
            if (!isset($connector->handle)) {
                $connector->handle = new \PDO(
                    $connector->Connection->getPdoString(),
                    $connector->username,
                    $connector->password,
                    $connector->options
                );
            }

            return $connector;
        } catch (\Throwable $th) {
            echo $th->getCode() . \PHP_EOL . $th->getMessage() . \PHP_EOL;
            exit(1);
        }
    }

    public function query(string $query, ?string $class = null): mixed
    {
        if (!isset($this->handle)) {
            throw new \Exception('faut se connecter d\'abord !');
        }

        $target = null;
        if (!(\is_null($class) || $class === 'MetaStd')) {
            $target = $class;
            $class = \stdClass::class;
        }

        $retour = [];
        try {
            $statement = $this->handle->prepare($query);
            $ok = $statement->execute();
            if (!$ok) {
                throw new \Exception('erreur dans la requête !');
            }
            $retour = \is_null($class) ?
                $statement->fetchAll(\PDO::FETCH_ASSOC) :
                $statement->fetchAll(\PDO::FETCH_CLASS, $class);

            $retour = \is_null($target) ?
                $retour :
                \array_map(function ($row) use ($target) {
                    return new $target($row);
                }, $retour);
        } catch (\Throwable $th) {
            echo $th->getCode() . \PHP_EOL . $th->getMessage() . \PHP_EOL;
            exit(1);
        }

        return $retour;
    }

    public function select(
        string|array $from,
        string|array $select = ['*'],
        string|array $where = [],
        string|array $groupBy = [],
        string|array $having = [],
        string|array $orderBy = [],
        int|array $limit = 0
    ): mixed {
        $class = \null;
        if (\in_array($from, \array_keys($this->mapping))) {
            $class = $this->mapping[$from];
        }

        $query = $this->queryBuilder->select(
            $from,
            $select,
            $where,
            $groupBy,
            $having,
            $orderBy,
            $limit,
        );

        return $this->query($query, $class);
    }

    public function insert(string $table, array $columns, array $values): mixed
    {
        $query = $this->queryBuilder->insert(
            $table,
            $columns,
            $values,
        );

        return $this->query($query);
    }

    public function update(string $table, string|array $values = [], string|array $where = []): mixed
    {
        $query = $this->queryBuilder->update(
            $table,
            $values,
            $where,
        );
        dump($query);

        return $this->query($query);
    }

    public function delete(string $table, string|array $where = []): mixed
    {
        $query = $this->queryBuilder->delete(
            $table,
            $where,
        );

        return $this->query($query);
    }
}

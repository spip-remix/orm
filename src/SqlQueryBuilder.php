<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm;

/**
 * Undocumented class.
 *
 * @author JamesRezo <james@rezo.net>
 */
class SqlQueryBuilder implements SqlQueryBuilderInterface
{
    public function __construct(
        private string $prefix = 'spip',
    ) {
    }

    public function select(
        string|array $from,
        string|array $select = ['*'],
        string|array $where = [],
        string|array $groupBy = [],
        string|array $having = [],
        string|array $orderBy = [],
        int|array $limit = 0,
    ): string {
        /**
         * Gestion des tables.
         *
         * @todo Jointures/Alias $from
         */
        if ($from === '' || $from === []) {
            throw new \RuntimeException('table(s) non fournie');
        }
        if (is_string($from)) {
            $from = [$from];
        }
        $from = array_map(fn (string $table) => $this->setPrefix($table), $from);
        $from = self::makeClause(' FROM ', ', ', $from);

        /**
         * Gestion des colonnes à récupérer (Projection).
         */
        $select = self::makeClause('SELECT ', ', ', $select);

        /**
         * Gestion des lignes à récupérer (Selection).
         *
         * @todo limit,offset
         */
        $where = self::makeClause(' WHERE ', ' AND ', $where);
        if (\is_int($limit)) {
            $limit = [$limit];
        }
        $limit = array_map(fn (int $limit) => \strval($limit), $limit);
        $limit = self::makeClause(' LIMIT ', '', $limit);

        $groupBy = self::makeClause(' GROUP BY ', ', ', $groupBy);
        $having = self::makeClause(' HAVING ', ' AND ', $having);
        $orderBy = self::makeClause(' ORDER BY ', ', ', $orderBy);

        $sql = str_replace(
            ['{select}', '{from}', '{where}', '{groupBy}', '{having}', '{orderBy}', '{limit}'],
            [$select, $from, $where, $groupBy, $having, $orderBy, $limit],
            '{select}{from}{where}{groupBy}{having}{orderBy}{limit};'
        );

        return $sql;
    }

    public function insert(
        string $table,
        array $columns,
        array $values,
    ): string {
        $table = $this->setPrefix($table);

        if ($table === '' || $columns === [] || $values === []) {
            throw new \RuntimeException('table(s) non fournie');
        }

        /**
         * Gestion des tables.
         */
        $insert = self::makeClause('INSERT INTO ', '', $table);

        $columns = ' (' . self::makeClause('', ', ', $columns) . ')';

        $_values = [];
        foreach ($values as $name => $value) {
            $_values[] = "('" . $name . "', '" . $value . "')";
        }
        $values = ' VALUES ' . self::makeClause('', ', ', $_values);

        $sql = str_replace(
            ['{insert}', '{columns}', '{values}'],
            [$insert, $columns, $values],
            '{insert}{columns}{values};'
        );

        return $sql;
    }

    public function update(
        string $table,
        string|array $values = [],
        string|array $where = [],
    ): string {
        if ($table === '' || $values === []) {
            throw new \RuntimeException('table(s) non fournie');
        }

        /**
         * Gestion des tables.
         */
        $table = $this->setPrefix($table);
        $update = self::makeClause('UPDATE ', '', $table);

        if (\is_string($values)) {
            $values = [$values];
        }
        $_values = [];
        foreach ($values as $name => $value) {
            $_values[] = $name . "='" . $value . "'";
        }
        $values = self::makeClause(' SET ', ', ', $_values);

        if (\is_string($where)) {
            $where = [$where];
        }
        $_where = [];
        foreach ($where as $name => $value) {
            $_where[] = $name . "='" . $value . "'";
        }
        $where = self::makeClause(' WHERE ', ' AND ', $_where);

        $sql = str_replace(
            ['{update}', '{values}', '{where}'],
            [$update, $values, $where],
            '{update}{values}{where};'
        );

        return $sql;
    }

    public function delete(
        string $table,
        string|array $where = [],
    ): string {
        if ($table === '') {
            throw new \RuntimeException('table(s) non fournie');
        }

        /**
         * Gestion des tables.
         */
        $table = $this->setPrefix($table);
        $delete = self::makeClause('DELETE FROM ', '', $table);

        /**
         * Si on ne fournit pas de clause WHERE
         * Faire un TRUNCATE
         */
        if (empty($where)) {
            return 'TRUNCATE ' . $table . ';';
        }

        if (\is_string($where)) {
            $where = [$where];
        }
        $_where = [];
        foreach ($where as $name => $value) {
            $_where[] = $name . "='" . $value . "'";
        }
        $where = self::makeClause(' WHERE ', ' AND ', $_where);


        $sql = str_replace(
            ['{delete}', '{where}'],
            [$delete, $where],
            '{delete}{where};'
        );

        return $sql;
    }

    /**
     * Undocumented function.
     *
     * @param string $clauseName
     * @param string $clauseSeparator
     * @param string|string[] $clause
     *
     * @return string
     */
    private function makeClause(
        string $clauseName,
        string $clauseSeparator,
        string|array $clause
    ): string {
        if (\is_string($clause)) {
            $clause = [$clause];
        }

        $clause = \implode($clauseSeparator, \array_map('trim', $clause));
        $clause = !empty($clause) ? $clauseName . $clause : '';

        return $clause;
    }

    /**
     * Undocumented function.
     */
    private function setPrefix(string $table): string
    {
        $table = ($this->prefix && !\preg_match('/^spip_/', $table)) ? 'spip_' . $table : $table;

        return $this->prefix ? (string) \preg_replace('/^spip_/', $this->prefix . '_', $table) : $table;
    }
}

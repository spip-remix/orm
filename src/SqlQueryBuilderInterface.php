<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm;

/**
 * Undocumented interface.
 *
 * @author JamesRezo <james@rezo.net>
 */
interface SqlQueryBuilderInterface
{
    /**
     * Undocumented function.
     *
     * @example https://github.com/spip-remix/database/blob/0.1/docs/POO/001_select.md description
     *
     * @param string|string[] $from la table
     * @param string|string[] $select les colonnes de la table
     * @param string|string[] $where les conditions
     * @param string|string[] $groupBy
     * @param string|string[] $orderBy
     * @param string|string[] $having
     * @param int|int[] $limit offset et position
     *
     * @return mixed le résultat de la requête SQL
     */
    public function select(
        string|array $from,
        string|array $select = ['*'],
        string|array $where = [],
        string|array $groupBy = [],
        string|array $having = [],
        string|array $orderBy = [],
        int|array $limit = 0,
    ): mixed;

    /**
     * Undocumented function
     *
     * @param string $table
     * @param string[] $columns
     * @param string[] $values
     *
     * @return string
     */
    public function insert(
        string $table,
        array $columns,
        array $values,
    ): mixed;

    /**
     * Undocumented function
     *
     * @param string $table
     * @param string|string[] $values
     * @param string|string[] $where
     *
     * @return string
     */
    public function update(
        string $table,
        string|array $values = [],
        string|array $where = [],
    ): mixed;

    /**
     * Undocumented function
     *
     * @param string $table
     * @param string|string[] $where
     *
     * @return string
     */
    public function delete(
        string $table,
        string|array $where = [],
    ): mixed;
}

<?php

namespace Modules\Utils;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator
{
    private const PAGE_SIZE = 5;
    private \ArrayIterator $result;
    private int $numResult;
    private int $currentPage;

    /**
     * Paginator constructor.
     */
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly int $pageSize = self::PAGE_SIZE,
    ) {
    }

    /**
     * @return $this
     *
     * @throws \Exception
     */
    final public function pagination(int $page = 1): self
    {
        $this->currentPage = (int) max(1, $page);
        $firstResult = ($this->currentPage - 1) * $this->pageSize;

        $query = $this->queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pageSize)
            ->getQuery();

        $paginator = new DoctrinePaginator($query, true);

        $this->result = $paginator->getIterator();
        $this->numResult = $paginator->count();

        return $this;
    }

    final public function getResult(): \ArrayIterator
    {
        return $this->result;
    }

    final public function getNumResult(): int
    {
        return $this->numResult;
    }

    final public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    final public function getLastPage(): int
    {
        return (int) ceil($this->numResult / $this->pageSize);
    }

    final public function getPageSize(): int
    {
        return $this->pageSize;
    }

    final public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    final public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    final public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    final public function hasToPaginate(): bool
    {
        return $this->numResult > $this->pageSize;
    }

    final public function getNextPage(): int
    {
        return min($this->getLastPage(), $this->currentPage + 1);
    }
}

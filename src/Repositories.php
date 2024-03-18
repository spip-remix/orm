<?php

namespace SpipRemix\Component\Orm;

final class Repositories
{
    /** @var array<string,RepositoryInterface> */
    private array $repositories = [];

    public function add(RepositoryInterface $repository): self
    {
        $name = (string) $repository;
        if (!\array_key_exists($name, $this->repositories)) {
            $this->repositories[$name] = $repository;
        }

        return $this;
    }

    public function get(string $name): ?RepositoryInterface
    {
        return $this->repositories[$name] ?? null;
    }
}

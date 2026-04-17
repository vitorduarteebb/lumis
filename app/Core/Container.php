<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;

/**
 * Container mínimo para evolução (API, serviços compartilhados).
 */
final class Container
{
    /** @var array<string, Closure(self): mixed> */
    private array $bindings = [];

    /** @var array<string, Closure(self): mixed> */
    private array $singletonFactories = [];

    /** @var array<string, object> */
    private array $singletons = [];

    public function bind(string $id, Closure $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function singleton(string $id, Closure $factory): void
    {
        $this->singletonFactories[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        if (isset($this->singletonFactories[$id])) {
            $obj = ($this->singletonFactories[$id])($this);
            if (!is_object($obj)) {
                throw new RuntimeException('Singleton deve resolver para object: ' . $id);
            }
            $this->singletons[$id] = $obj;
            return $obj;
        }

        if (isset($this->bindings[$id])) {
            return ($this->bindings[$id])($this);
        }

        throw new RuntimeException('Serviço não registrado: ' . $id);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->singletonFactories[$id]) || isset($this->singletons[$id]);
    }
}

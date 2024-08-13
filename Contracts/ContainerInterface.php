<?php

namespace MA\PHPQUICK\Contracts;

use Closure;
use Psr\Container\ContainerInterface as PsrContainer;

/**
 * Interface tambahan untuk container dengan fitur khusus.
 */
interface ContainerInterface extends PsrContainer
{
    /**
     * Mendaftarkan layanan dengan closure atau factory.
     *
     * @param string $id
     * @param Closure $resolver
     */
    public function bind(string $id, Closure $resolver): void;

    /**
     * Mendaftarkan beberapa layanan sekaligus.
     *
     * @param array<string, Closure> $bindings
     */
    public function bindMany(array $bindings): void;

    /**
     * Mendaftarkan layanan sebagai singleton.
     *
     * @param string $id
     * @param Closure $resolver
     */
    public function singleton(string $id, Closure $resolver): void;

    /**
     * Mendaftarkan instance yang sudah ada.
     *
     * @param string $id
     * @param mixed $instance
     */
    public function instance(string $id, mixed $instance): void;

    /**
     * Memodifikasi layanan yang sudah ada dalam container.
     *
     * @param string $id
     * @param Closure $callback
     */
    public function extend(string $id, Closure $callback): void;

    /**
     * Memanggil Closure dengan dependencies yang di-resolve oleh container.
     *
     * @param Closure|string $callback
     * @param array<string, mixed> $parameters
     * @return mixed
     */
    public function call($callback, array $parameters = []): mixed;
}

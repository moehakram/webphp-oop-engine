<?php
declare(strict_types = 1);

namespace MA\PHPQUICK\Contracts;

use MA\PHPQUICK\Collection;

/**
 * Interface ValidationInterface
 * 
 * This interface defines the contract for validation classes.
 */
interface ValidationInterface
{
    /**
     * Validates the data against the validation rules.
     *
     * @return Collection A collection of validated data.
     * @throws \MA\PHPQUICK\Exceptions\ValidationException If validation fails.
     */
    public function validate(): Collection;

    /**
     * Checks if the given key exists in the data.
     *
     * @param string $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Retrieves the value associated with the given key.
     *
     * @param string $key The key to retrieve the value for.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value of the key, or the default value if not found.
     */
    public function get(string $key, $default = null): mixed;

    /**
     * Sets a value for a specific key in the data.
     *
     * @param string $key The key to set the value for.
     * @param mixed $value The value to set.
     */
    public function set(string $key, $value): void;

}

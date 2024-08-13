<?php

namespace MA\PHPQUICK\Contracts;

interface Authenticable
{
    /**
     * Dapatkan identifier unik dari entitas yang dapat diotentikasi.
     *
     * @return mixed
     */
    public function getAuthIdentifier();

    /**
     * Dapatkan password dari entitas yang dapat diotentikasi.
     *
     * @return string
     */
    public function getAuthPassword(): string;

    /**
     * Dapatkan nama kolom yang digunakan untuk otentikasi.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string;

    /**
     * Dapatkan nilai 'remember token' dari entitas yang dapat diotentikasi.
     *
     * @return string|null
     */
    public function getRememberToken(): ?string;

    /**
     * Set nilai 'remember token' dari entitas yang dapat diotentikasi.
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken(string $value): void;

    /**
     * Dapatkan nama kolom 'remember token' dari entitas yang dapat diotentikasi.
     *
     * @return string
     */
    public function getRememberTokenName(): string;
}

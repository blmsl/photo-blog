<?php

namespace Core\Managers\User\Contracts;

use Core\Models\User;

/**
 * Interface UserManager.
 *
 * @package Core\Managers\User\Contracts
 */
interface UserManager
{
    /**
     * Get the user by ID.
     *
     * @param int $id
     * @return User
     */
    public function getById(int $id): User;

    /**
     * Get the user by name.
     *
     * @param string $name
     * @return User
     */
    public function getByName(string $name): User;

    /**
     * Get the user by email.
     *
     * @param string $email
     * @return User
     */
    public function getByEmail(string $email): User;

    /**
     * Get the user by credentials.
     *
     * @param string $email
     * @param string $password
     * @return User
     */
    public function getByCredentials(string $email, string $password): User;

    /**
     * Generate the api token.
     *
     * @param User $user
     * @return void
     */
    public function generateApiToken(User $user): void;

    /**
     * Generate the password hash.
     *
     * @param User $user
     * @param string $password
     * @return mixed
     */
    public function generatePasswordHash(User $user, string $password): void;

    /**
     * Create the customer user.
     *
     * @param array $attributes
     * @return User
     */
    public function createCustomer(array $attributes = []): User;

    /**
     * Create the administrator user.
     *
     * @param array $attributes
     * @return User
     */
    public function createAdministrator(array $attributes = []): User;

    /**
     * Save the user.
     *
     * @param User $user
     * @param array $attributes
     * @return void
     */
    public function save(User $user, array $attributes = []): void;

    /**
     * Delete the user.
     *
     * @param User $user
     * @return void
     */
    public function delete(User $user): void;
}

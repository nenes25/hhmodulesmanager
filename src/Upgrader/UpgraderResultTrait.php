<?php

namespace Hhennes\ModulesManager\Upgrader;

/**
 * Trait which allows to manage results upgrade process
 */
trait UpgraderResultTrait
{
    protected array $errors = [];
    protected array $warning = [];
    protected array $success = [];

    /**
     * {@inheritDoc}
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Reset the results of the upgrade
     *
     * @return void
     */
    public function resetResults(): void
    {
        $this->success = [];
        $this->errors = [];
        $this->warning = [];
    }
}

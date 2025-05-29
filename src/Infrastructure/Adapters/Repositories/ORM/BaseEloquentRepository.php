<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM;

abstract class BaseEloquentRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    private function handle()
    {
        return app($this->model);
    }

    public function __construct()
    {
        $this->model = $this->handle();
    }
}

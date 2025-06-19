<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM;

use Illuminate\Database\Eloquent\Model;

abstract class BaseEloquentRepository
{
    /**
     * @var Model
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

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Jiannei\Response\Laravel\Contracts\Format;
use Jiannei\Response\Laravel\Support\Traits\JsonResponseTrait;
use Prettus\Repository\Eloquent\BaseRepository;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, JsonResponseTrait;

    protected $formatter;

    protected BaseRepository $repository;

    public function __construct(Format $format)
    {
        $this->formatter = $format;
        if (method_exists($this, "getRepositoryClass")) {
            $this->repository = app($this->getRepositoryClass());
        }
    }
}

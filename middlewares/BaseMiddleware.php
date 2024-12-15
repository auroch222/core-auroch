<?php

namespace auroch\phpmvc\middlewares;

abstract class BaseMiddleware
{
    abstract public function execute();
}
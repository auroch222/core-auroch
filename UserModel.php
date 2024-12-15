<?php

namespace auroch\phpmvc;

use auroch\phpmvc\db\DbModel;

abstract class UserModel extends DbModel
{
    abstract public function getDisplayName(): string;
}
<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;
use ClickCart\Helpers;

abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Helpers\db();
    }
}

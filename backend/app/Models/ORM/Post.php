<?php

namespace Models\ORM;

use Database\DataAccess\ORM;

class Post extends ORM
{
    protected static string $primaryKey = 'post_id';

    protected static function getTableName(): string
    {
        return 'post';
    }

    public function __toString()
    {
        return '';
    }
}

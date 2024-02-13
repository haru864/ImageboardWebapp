<?php

namespace Database\Migrations;

use Database;

class AddColumnToPost implements Database\SchemaMigration
{
    public function up(): array
    {
        // マイグレーションロジックをここに追加してください
        return [
            "ALTER TABLE post
            ADD COLUMN image_path VARCHAR(255) NULL,
            ADD COLUMN thumbnail_path VARCHAR(255) NULL"
        ];
    }

    public function down(): array
    {
        // ロールバックロジックを追加してください
        return [
            "ALTER TABLE post
            DROP COLUMN image_path,
            DROP COLUMN thumbnail_path"
        ];
    }
}
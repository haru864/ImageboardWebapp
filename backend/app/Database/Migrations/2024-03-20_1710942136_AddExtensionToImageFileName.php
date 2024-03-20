<?php

namespace Database\Migrations;

use Database;

class AddExtensionToImageFileName implements Database\SchemaMigration
{
    public function up(): array
    {
        // マイグレーションロジックをここに追加してください
        return [
            "ALTER TABLE post MODIFY COLUMN content varchar(300)",
            "ALTER TABLE post MODIFY COLUMN image_file_name varchar(80)",
            "ALTER TABLE post MODIFY COLUMN image_file_extension varchar(15)"
        ];
    }

    public function down(): array
    {
        // ロールバックロジックを追加してください
        return [
            "ALTER TABLE post MODIFY COLUMN content TEXT",
            "ALTER TABLE post MODIFY COLUMN image_file_name varchar(255)",
            "ALTER TABLE post MODIFY COLUMN image_file_extension varchar(10)"
        ];
    }
}

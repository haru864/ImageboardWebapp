<?php

namespace Database\Migrations;

use Database;

class ModifyColumnsInPost implements Database\SchemaMigration
{
    public function up(): array
    {
        // マイグレーションロジックをここに追加してください
        return [
            "ALTER TABLE post ADD COLUMN image_file_extension VARCHAR(10) NULL",
            "ALTER TABLE post RENAME COLUMN uploaded_image TO image_file_name",
            "ALTER TABLE post DROP thumbnail"
        ];
    }

    public function down(): array
    {
        // ロールバックロジックを追加してください
        return [
            "ALTER TABLE post DROP image_file_extension",
            "ALTER TABLE post RENAME COLUMN image_file_name TO uploaded_image",
            "ALTER TABLE post ADD COLUMN thumbnail VARCHAR(255) NULL"
        ];
    }
}

<?php

namespace Database\Migrations;

use Database;

class RenameColumnInPost implements Database\SchemaMigration
{
    public function up(): array
    {
        // マイグレーションロジックをここに追加してください
        return [
            "ALTER TABLE post RENAME COLUMN image_path TO uploaded_image",
            "ALTER TABLE post RENAME COLUMN thumbnail_path TO thumbnail"
        ];
    }

    public function down(): array
    {
        // ロールバックロジックを追加してください
        return [
            "ALTER TABLE post RENAME COLUMN uploaded_image TO image_path",
            "ALTER TABLE post RENAME COLUMN thumbnail TO thumbnail_path"
        ];
    }
}
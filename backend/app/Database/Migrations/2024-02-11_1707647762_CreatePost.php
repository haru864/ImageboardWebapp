<?php

namespace Database\Migrations;

use Database;

class CreatePost implements Database\SchemaMigration
{
    public function up(): array
    {
        // マイグレーションロジックをここに追加してください
        return [
            "CREATE TABLE post (
                post_id INT AUTO_INCREMENT PRIMARY KEY,
                reply_to_id INT NULL,
                subject VARCHAR(50) NULL,
                content TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
            FOREIGN KEY (reply_to_id) REFERENCES post(post_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
            )"
        ];
    }

    public function down(): array
    {
        // ロールバックロジックを追加してください
        return [
            "DROP TABLE post"
        ];
    }
}

<?php

/**
 * 数据库迁移：为 animals 表的 review_status 增加 'adopted' 状态
 *
 * SQLite 不支持 ALTER COLUMN 修改 enum/CHECK 约束，
 * 因此通过重建表的方式将 review_status 的允许值从
 * ('pending','approved','rejected') 扩展为 ('pending','approved','rejected','adopted')。
 * 'adopted' 状态表示动物已被领养。
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 执行迁移 —— 重建 animals 表以扩展 review_status 约束
     */
    public function up(): void
    {
        // SQLite 不支持 ALTER COLUMN 修改 enum，需要重建表
        // 通过原始 SQL 删除 CHECK 约束并重建
        DB::statement("PRAGMA foreign_keys = OFF");

        DB::statement("CREATE TABLE animals_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR NOT NULL,
            species VARCHAR NOT NULL,
            age INTEGER,
            habits TEXT,
            visibility VARCHAR NOT NULL DEFAULT 'public' CHECK(visibility IN ('public','private')),
            created_by INTEGER NOT NULL,
            review_status VARCHAR NOT NULL DEFAULT 'pending' CHECK(review_status IN ('pending','approved','rejected','adopted')),
            reviewed_by INTEGER,
            reviewed_at DATETIME,
            deleted_at DATETIME,
            created_at DATETIME,
            updated_at DATETIME,
            description TEXT,
            photo_path VARCHAR,
            FOREIGN KEY(created_by) REFERENCES users(id),
            FOREIGN KEY(reviewed_by) REFERENCES users(id)
        )");

        DB::statement("INSERT INTO animals_new SELECT id, name, species, age, habits, visibility, created_by, review_status, reviewed_by, reviewed_at, deleted_at, created_at, updated_at, description, photo_path FROM animals");

        DB::statement("DROP TABLE animals");
        DB::statement("ALTER TABLE animals_new RENAME TO animals");

        DB::statement("PRAGMA foreign_keys = ON");
    }

    public function down(): void
    {
        // Reverse: remove 'adopted' from enum
        DB::statement("PRAGMA foreign_keys = OFF");

        DB::statement("UPDATE animals SET review_status = 'approved' WHERE review_status = 'adopted'");

        DB::statement("CREATE TABLE animals_old (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR NOT NULL,
            species VARCHAR NOT NULL,
            age INTEGER,
            habits TEXT,
            visibility VARCHAR NOT NULL DEFAULT 'public' CHECK(visibility IN ('public','private')),
            created_by INTEGER NOT NULL,
            review_status VARCHAR NOT NULL DEFAULT 'pending' CHECK(review_status IN ('pending','approved','rejected')),
            reviewed_by INTEGER,
            reviewed_at DATETIME,
            deleted_at DATETIME,
            created_at DATETIME,
            updated_at DATETIME,
            description TEXT,
            photo_path VARCHAR,
            FOREIGN KEY(created_by) REFERENCES users(id),
            FOREIGN KEY(reviewed_by) REFERENCES users(id)
        )");

        DB::statement("INSERT INTO animals_old SELECT * FROM animals");
        DB::statement("DROP TABLE animals");
        DB::statement("ALTER TABLE animals_old RENAME TO animals");

        DB::statement("PRAGMA foreign_keys = ON");
    }
};

<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateInitialTables extends AbstractMigration
{
    public function up(): void
    {
        // Включаем расширение для генерации UUID (pgcrypto)
        $this->execute("CREATE EXTENSION IF NOT EXISTS \"pgcrypto\";");

        // Таблица пользователей
        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('avatar', 'string', ['limit' => 1024, 'null' => true])
            ->addColumn('status_message', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('last_online_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['phone'], ['unique' => true])
            ->create();
        $this->execute("ALTER TABLE users ALTER COLUMN id SET DEFAULT gen_random_uuid();");

        // Таблица чатов
        $this->table('chat', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
        $this->execute("ALTER TABLE chat ALTER COLUMN id SET DEFAULT gen_random_uuid();");

        // Таблица типов чатов (mapping)
        $this->table('chat_type', ['id' => false, 'primary_key' => ['chat_id']])
            ->addColumn('chat_id', 'uuid')
            ->addColumn('type', 'string', ['limit' => 20, 'null' => false])
            ->addForeignKey('chat_id', 'chat', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();

        // Детали группового чата
        $this->table('group_chat_details', ['id' => false, 'primary_key' => ['chat_id']])
            ->addColumn('chat_id', 'uuid')
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_by', 'uuid', ['null' => false])
            ->addForeignKey('chat_id', 'chat', 'id', ['delete'=> 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete'=> 'NO_ACTION'])
            ->create();

        // Детали личного чата
        $this->table('private_chat_details', ['id' => false, 'primary_key' => ['chat_id']])
            ->addColumn('chat_id', 'uuid')
            ->addColumn('user1_id', 'uuid', ['null' => false])
            ->addColumn('user2_id', 'uuid', ['null' => false])
            ->addForeignKey('chat_id', 'chat', 'id', ['delete'=> 'CASCADE'])
            ->addForeignKey('user1_id', 'users', 'id', ['delete'=> 'NO_ACTION'])
            ->addForeignKey('user2_id', 'users', 'id', ['delete'=> 'NO_ACTION'])
            ->create();

        // Таблица участников чата
        $this->table('chat_member', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('chat_id', 'uuid', ['null' => false])
            ->addColumn('user_id', 'uuid', ['null' => false])
            ->addColumn('is_admin', 'boolean', ['default' => false])
            ->addColumn('joined_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('chat_id', 'chat', 'id', ['delete'=> 'CASCADE'])
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'NO_ACTION'])
            ->create();
        $this->execute("ALTER TABLE chat_member ALTER COLUMN id SET DEFAULT gen_random_uuid();");

        // Таблица сообщений
        $this->table('message', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('chat_id', 'uuid', ['null' => false])
            ->addColumn('sender_id', 'uuid', ['null' => false])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('is_read', 'boolean', ['default' => false])
            ->addColumn('reply_to_id', 'uuid', ['null' => true])
            ->addForeignKey('chat_id', 'chat', 'id', ['delete'=> 'NO_ACTION'])
            ->addForeignKey('sender_id', 'users', 'id', ['delete'=> 'NO_ACTION'])
            ->addForeignKey('reply_to_id', 'message', 'id', ['delete'=> 'SET_NULL'])
            ->create();
        $this->execute("ALTER TABLE message ALTER COLUMN id SET DEFAULT gen_random_uuid();");

        // Таблица вложений
        $this->table('attachment', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('message_id', 'uuid', ['null' => false])
            ->addColumn('file_url', 'string', ['limit' => 1024, 'null' => false])
            ->addColumn('type', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('size', 'integer', ['null' => true])
            ->addForeignKey('message_id', 'message', 'id', ['delete'=> 'CASCADE'])
            ->create();
        $this->execute("ALTER TABLE attachment ALTER COLUMN id SET DEFAULT gen_random_uuid();");
    }

    public function down(): void
    {
        // Удаляем таблицы в обратном порядке, чтобы не было ошибок FK
        $this->table('attachment')->drop()->save();
        $this->table('message')->drop()->save();
        $this->table('chat_member')->drop()->save();
        $this->table('private_chat_details')->drop()->save();
        $this->table('group_chat_details')->drop()->save();
        $this->table('chat_type')->drop()->save();
        $this->table('chat')->drop()->save();
        $this->table('users')->drop()->save();
    }
}

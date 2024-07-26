<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmDefault8c68aa3a546d50e600c47b283ea86f57 extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('messages')
        ->addColumn('id', 'primary', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('text', 'text', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('chat_id', 'text', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('created_at', 'datetime', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('from_user_id', 'text', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('from_username', 'text', ['nullable' => true, 'defaultValue' => null])
        ->setPrimaryKeys(['id'])
        ->create();
    }

    public function down(): void
    {
        $this->table('messages')->drop();
    }
}

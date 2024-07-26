<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmDefault52803cab853575753e994992a71d5bed extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('messages')
        ->addColumn('is_finish_message', 'boolean', ['nullable' => false, 'defaultValue' => false])
        ->update();
    }

    public function down(): void
    {
        $this->table('messages')
        ->dropColumn('is_finish_message')
        ->update();
    }
}

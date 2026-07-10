<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('modules')->insert([
            [
                'id' => 30,
                'name' => 'Indicator Submissions',
                'description' => 'Indicator Submissions module',
                'category' => 'Submission',
                'administrator' => 'N',
            ],
        ]);

        DB::table('permissions')->insert([
            [
                'id' => 113,
                'name' => 'add',
                'description' => 'Permission to create indicator submissions',
                'module_id' => 30,
            ],
            [
                'id' => 114,
                'name' => 'edit',
                'description' => 'Permission to edit indicator submissions',
                'module_id' => 30,
            ],
            [
                'id' => 115,
                'name' => 'delete',
                'description' => 'Permission to delete indicator submissions',
                'module_id' => 30,
            ],
            [
                'id' => 116,
                'name' => 'view',
                'description' => 'Permission to view indicator submissions',
                'module_id' => 30,
            ],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('id', [113, 114, 115, 116])
            ->delete();

        DB::table('modules')
            ->where('id', 30)
            ->delete();
    }
};
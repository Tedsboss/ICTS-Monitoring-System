<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('modules')->updateOrInsert(
            ['id' => 33],
            [
                'name' => 'UPLIFT Submissions',
                'description' => 'UPLIFT submissions module',
                'category' => 'Submission',
                'administrator' => 'N',
            ]
        );

        foreach ([
            125 => ['add', 'Permission to create UPLIFT submissions'],
            126 => ['edit', 'Permission to edit and submit UPLIFT submissions'],
            127 => ['delete', 'Permission to delete UPLIFT submissions'],
            128 => ['view', 'Permission to view UPLIFT submissions'],
        ] as $id => $permission) {
            DB::table('permissions')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $permission[0],
                    'description' => $permission[1],
                    'module_id' => 33,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('id', [125, 126, 127, 128])->delete();
        DB::table('modules')->where('id', 33)->delete();
    }
};

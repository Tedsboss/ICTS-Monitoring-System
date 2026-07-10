<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PermissionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // DB::table('permissions')->insert([
    //   [
    //     'id' => 33,
    //     'name' => 'Generate Satement of Allotment, Expenditures and Balances by Staff',
    //     'description' => 'Generate report(4)',
    //     'module_id' => 6
    //   ],
    //   [
    //     'id' => 34,
    //     'name' => 'Generate Statement of Cumulative Allotments, Obligations Incurred, AA Estimated and Balance (Detailed)',
    //     'description' => 'Generate report(5)',
    //     'module_id' => 6
    //   ],
    //   [
    //     'id' => 35,
    //     'name' => 'Generate Statement of Cumulative Allotments, Obligations Incurred, AA Estimated and Balance (Summary)',
    //     'description' => 'Generate report(6)',
    //     'module_id' => 6
    //   ],
    //   [
    //     'id' => 36,
    //     'name' => 'Generate Summary of Expenditures',
    //     'description' => 'Generate report(7)',
    //     'module_id' => 6
    //   ],
    // ]);

    DB::table('reports')->insert([
      [
        'id' => 4,
        'name' => 'Satement of Allotment, Expenditures and Balances by Staff',
        'permission_id' => 33,
        'excel_flag' => 'Y',
        'pdf_flag' => 'N',
        'year' => 'N',
        'appro_type' => 'Y',
        'date_range' => 'Y',
        'month_range' => 'N',
        'responsibility_center' => 'Y',
      ],
      [
        'id' => 5,
        'name' => 'Statement of Cumulative Allotments, Obligations Incurred, AA Estimated and Balance (Detailed)',
        'permission_id' => 34,
        'excel_flag' => 'Y',
        'pdf_flag' => 'N',
        'year' => 'Y',
        'appro_type' => 'N',
        'date_range' => 'N',
        'month_range' => 'Y',
        'responsibility_center' => 'N',
      ],
      [
        'id' => 6,
        'name' => 'Statement of Cumulative Allotments, Obligations Incurred, AA Estimated and Balance (Summary)',
        'permission_id' => 35,
        'excel_flag' => 'Y',
        'pdf_flag' => 'N',
        'year' => 'Y',
        'appro_type' => 'N',
        'date_range' => 'N',
        'month_range' => 'Y',
        'responsibility_center' => 'N',
      ],
      [
        'id' => 7,
        'name' => 'Summary of Expenditures',
        'permission_id' => 35,
        'excel_flag' => 'Y',
        'pdf_flag' => 'N',
        'year' => 'Y',
        'appro_type' => 'Y',
        'date_range' => 'N',
        'month_range' => 'N',
        'responsibility_center' => 'N',
      ],
    ]);
  }
}

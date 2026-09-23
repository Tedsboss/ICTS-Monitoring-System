<?php

namespace Database\Seeders;

use App\Models\WorkPlanClassification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkPlanClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $fiscalYear = 2026;

        DB::transaction(function () use ($fiscalYear) {
            WorkPlanClassification::where('fiscal_year', $fiscalYear)->delete();

            $sortOrder = 10;

            $gas = $this->createClassification(
                $fiscalYear,
                null,
                'GENERAL ADMINISTRATION AND SUPPORT',
                1,
                $sortOrder
            );

            $sortOrder += 10;

            $this->createClassification(
                $fiscalYear,
                $gas->id,
                'General management and supervision',
                2,
                $sortOrder
            );

            $sortOrder += 10;

            $this->createClassification(
                $fiscalYear,
                $gas->id,
                'Legislative liaison services',
                2,
                $sortOrder
            );

            $sortOrder += 10;

            $this->createClassification(
                $fiscalYear,
                $gas->id,
                'Human resource development',
                2,
                $sortOrder
            );

            $sortOrder += 10;

            $this->createClassification(
                $fiscalYear,
                $gas->id,
                'Administration of personnel benefits',
                2,
                $sortOrder
            );

            $sortOrder += 10;

            $sto = $this->createClassification(
                $fiscalYear,
                null,
                'SUPPORT TO OPERATIONS',
                1,
                $sortOrder
            );

            $sortOrder += 10;

            $stoItems = [
                'Internal planning and management services',
                'Public relations, multimedia development, and knowledge management',
                'Internal information and communications technology services',
                'Legal services',
                'Locally-funded project',
                'Implementation of the Management Information System',
            ];

            foreach ($stoItems as $name) {
                $this->createClassification(
                    $fiscalYear,
                    $sto->id,
                    $name,
                    2,
                    $sortOrder
                );

                $sortOrder += 10;
            }

            $operations = $this->createClassification(
                $fiscalYear,
                null,
                'OPERATIONS',
                1,
                $sortOrder
            );

            $sortOrder += 10;

            $socioeconomic = $this->createClassification(
                $fiscalYear,
                $operations->id,
                'Socioeconomic Policy and Planning Program',
                2,
                $sortOrder
            );

            $sortOrder += 10;

            $socioeconomicItems = [
                'Coordination of formulation and updating of national, inter-regional, regional and sectoral socioeconomic, physical and development policies and plans',
                'Provision of technical and secretariat support services to the Economy and Development Council and its Committees and other Inter-agency Committees',
                'Provision of support services to Regional Development Councils',
                'Provision of advisory service and assistance to the President, the Cabinet, the Congress, the inter-agency bodies, and other government entities and instrumentalities on socioeconomic and development matters',
                'Provision of Technical and Secretariat Support Services to the LEDAC and its sub-committee and technical working group',
                'Locally-funded projects',
                'Establishment of Innovation Fund pursuant to Section 21 of Republic Act No. 11293 including Provision of Secretariat Services to the National Innovation Council',
            ];

            foreach ($socioeconomicItems as $name) {
                $this->createClassification(
                    $fiscalYear,
                    $socioeconomic->id,
                    $name,
                    3,
                    $sortOrder
                );

                $sortOrder += 10;
            }

            $investment = $this->createClassification(
                $fiscalYear,
                $operations->id,
                'National Investment Programming Program',
                2,
                $sortOrder
            );

            $sortOrder += 10;

            $investmentItems = [
                'Provision of technical and secretariat support services to the Investment Coordination Committee and the Infrastructure Committee',
                'Coordination of the formulation and updating of Public Investment Program',
                'Appraisal of proposed projects for official development assistance, local financing, and for public-private partnership implementation',
                'Coordination of programming of official development assistance in the form of grants and concessional loans',
            ];

            foreach ($investmentItems as $name) {
                $this->createClassification(
                    $fiscalYear,
                    $investment->id,
                    $name,
                    3,
                    $sortOrder
                );

                $sortOrder += 10;
            }

            $monitoring = $this->createClassification(
                $fiscalYear,
                $operations->id,
                'National Development Monitoring and Evaluation Program',
                2,
                $sortOrder
            );

            $sortOrder += 10;

            $monitoringItems = [
                'Monitoring and evaluation of the implementation of plans, programs, policies and projects',
                'Evaluation services pursuant to laws, rules and regulations, and other issuances',
            ];

            foreach ($monitoringItems as $name) {
                $this->createClassification(
                    $fiscalYear,
                    $monitoring->id,
                    $name,
                    3,
                    $sortOrder
                );

                $sortOrder += 10;
            }
        });
    }

    private function createClassification(
        int $fiscalYear,
        ?int $parentId,
        string $name,
        int $level,
        int $sortOrder
    ): WorkPlanClassification {
        return WorkPlanClassification::create([
            'fiscal_year' => $fiscalYear,
            'parent_id' => $parentId,
            'code' => null,
            'name' => $name,
            'level' => $level,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }
}
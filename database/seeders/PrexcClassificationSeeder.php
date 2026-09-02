<?php

namespace Database\Seeders;

use App\Models\PrexcClassification;
use Illuminate\Database\Seeder;

class PrexcClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            // I. General Administration and Support
            [
                'classification_group' => 'I. General Administration and Support',
                'program_name' => null,
                'classification_name' => 'General Management and Supervision',
                'prexc_code' => '100000100001000',
                'sort_order' => 10,
            ],
            [
                'classification_group' => 'I. General Administration and Support',
                'program_name' => null,
                'classification_name' => 'Legislative liaison services',
                'prexc_code' => '100000100002000',
                'sort_order' => 20,
            ],
            [
                'classification_group' => 'I. General Administration and Support',
                'program_name' => null,
                'classification_name' => 'Human Resource Development',
                'prexc_code' => '100000100003000',
                'sort_order' => 30,
            ],
            [
                'classification_group' => 'I. General Administration and Support',
                'program_name' => null,
                'classification_name' => 'Administration of Personnel Benefits',
                'prexc_code' => '100000100004000',
                'sort_order' => 40,
            ],

            // II. Support to Operations
            [
                'classification_group' => 'II. Support to Operations',
                'program_name' => null,
                'classification_name' => 'Internal planning and management services',
                'prexc_code' => '200000100001000',
                'sort_order' => 100,
            ],
            [
                'classification_group' => 'II. Support to Operations',
                'program_name' => null,
                'classification_name' => 'Public relations, multimedia development and knowledge management services',
                'prexc_code' => '200000100002000',
                'sort_order' => 110,
            ],
            [
                'classification_group' => 'II. Support to Operations',
                'program_name' => null,
                'classification_name' => 'Internal information and communication technology (ICT) services',
                'prexc_code' => '200000100003000',
                'sort_order' => 120,
            ],
            [
                'classification_group' => 'II. Support to Operations',
                'program_name' => null,
                'classification_name' => 'Legal Services',
                'prexc_code' => '200000100004000',
                'sort_order' => 130,
            ],
            [
                'classification_group' => 'II. Support to Operations',
                'program_name' => 'Locally Funded Project(s)',
                'classification_name' => 'Implementation of the Management Information System',
                'prexc_code' => '200000200001000',
                'sort_order' => 140,
            ],

            // III. Operations
            // Program 1
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 1: Socioeconomic Policy and Planning Program',
                'classification_name' => 'Coordination of the Formulation and Updating of National, Inter-regional, Regional, and Sectoral Socioeconomic, Physical and Development Policies, and Plans',
                'prexc_code' => '310100100001000',
                'sort_order' => 200,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 1: Socioeconomic Policy and Planning Program',
                'classification_name' => 'Provision of Technical and Secretariat Support Services to the NEDA Board and its Committees and other Inter-agency Committees',
                'prexc_code' => '310100100002000',
                'sort_order' => 210,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 1: Socioeconomic Policy and Planning Program',
                'classification_name' => 'Provision of Support Services to Regional Development Councils',
                'prexc_code' => '310100100003000',
                'sort_order' => 220,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 1: Socioeconomic Policy and Planning Program',
                'classification_name' => 'Provision of Advisory Services and Assistance to the President, Cabinet, Congress, Inter-agency Bodies, and other government entities and instrumentalities on Socio-economic and Development Matters',
                'prexc_code' => '310100100004000',
                'sort_order' => 230,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 1: Socioeconomic Policy and Planning Program',
                'classification_name' => 'Provision of Technical and Secretariat Support Services to the LEDAC and its sub-committee and technical working group',
                'prexc_code' => '310100100005000',
                'sort_order' => 240,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Locally Funded Project(s)',
                'classification_name' => 'Establishment of Innovation Fund pursuant to Section 21 of Republic Act No. 11293 including Provision of Secretariat Services to the National Innovation Council',
                'prexc_code' => '310100200005000',
                'sort_order' => 250,
            ],

            // III. Operations
            // Program 2
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 2: National Investment Programming Program',
                'classification_name' => 'Provision of Technical and Secretariat Support Services to the Investment Coordination Committee, and the Infrastructure Committee',
                'prexc_code' => '310200100001000',
                'sort_order' => 300,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 2: National Investment Programming Program',
                'classification_name' => 'Coordination to the Formulation and Updating of Public Investment Programs',
                'prexc_code' => '310200100002000',
                'sort_order' => 310,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 2: National Investment Programming Program',
                'classification_name' => 'Appraisal of Proposed Projects for Official Development Assistance, Local Financing, and for Public-Private Partnership Implementation',
                'prexc_code' => '310200100003000',
                'sort_order' => 320,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 2: National Investment Programming Program',
                'classification_name' => 'Coordination of the Programming of Official Development Assistance in the Form of Grants and Concessional Loans',
                'prexc_code' => '310200100004000',
                'sort_order' => 330,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Locally Funded Project(s)',
                'classification_name' => 'Value Engineering/Value Analysis (VE/VA) Project',
                'prexc_code' => '310200200001000',
                'sort_order' => 340,
            ],

            // III. Operations
            // Program 3
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 3: National Development Monitoring and Evaluation',
                'classification_name' => 'Monitoring and Evaluation of the Implementation of Plans, Programs, Policies and Projects',
                'prexc_code' => '310300100001000',
                'sort_order' => 400,
            ],
            [
                'classification_group' => 'III. Operations',
                'program_name' => 'Program 3: National Development Monitoring and Evaluation',
                'classification_name' => 'Evaluation Services Pursuant to Laws, Rules and Regulations, and other Issuances',
                'prexc_code' => '310300100002000',
                'sort_order' => 410,
            ],
        ];

        foreach ($items as $item) {
            PrexcClassification::updateOrCreate(
                [
                    'prexc_code' => $item['prexc_code'],
                ],
                [
                    'classification_group' => $item['classification_group'],
                    'program_name' => $item['program_name'],
                    'classification_name' => $item['classification_name'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}

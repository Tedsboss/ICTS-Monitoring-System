<?php

namespace Database\Seeders;

use App\Models\Expenditure;
use App\Models\Program;
use App\Models\ProgramHeader;
use App\Models\ProgramSubHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramClassificationMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $programs = ProgramHeader::updateOrCreate(
                ['header' => 'Programs'],
                ['header' => 'Programs']
            );

            $locallyFunded = ProgramHeader::updateOrCreate(
                ['header' => 'Locally-Funded Projects'],
                ['header' => 'Locally-Funded Projects']
            );

            $this->seedProgramSubHeader($programs, 'General Administration and Support', 'I', [
                ['General Management and Supervision', '100000100001000'],
                ['Legislative liaison services', '100000100002000'],
                ['Human Resource Development', '100000100003000'],
                ['Administration of Personnel Benefits', '100000100004000'],
            ]);

            $this->seedProgramSubHeader($programs, 'Support to Operations', 'II', [
                ['Internal planning and management services', '200000100001000'],
                ['Public relations, multimedia development and knowledge management services', '200000100002000'],
                ['Internal information and communication technology (ICT) services', '200000100003000'],
                ['Legal Services', '200000100004000'],
            ]);

            $operations = ProgramSubHeader::updateOrCreate(
                [
                    'header_id' => $programs->id,
                    'sub_header' => 'Operations',
                ],
                [
                    'sub_code' => 'III',
                ]
            );

            $this->seedProgram($operations, 'Program 1: Socioeconomic Policy and Planning Program', [
                [
                    'Coordination of the Formulation and Updating of National, Inter-regional, Regional, and Sectoral Socioeconomic, Physical and Development Policies, and Plans',
                    '310100100001000',
                ],
                [
                    'Provision of Technical and Secretariat Support Services to the Economy and Development Council and its Committees and other Inter-agency Committees',
                    '310100100002000',
                ],
                [
                    'Provision of Support Services to Regional Development Councils',
                    '310100100003000',
                ],
                [
                    'Provision of Advisory Services and Assistance to the President, Cabinet, Congress, Inter-agency Bodies, and other government entities and instrumentalities on Socio-economic and Development Matters',
                    '310100100004000',
                ],
                [
                    'Provision of Technical and Secretariat Support Services to the LEDAC and its sub-committee and technical working group',
                    '310100100005000',
                ],
            ]);

            $this->seedProgram($operations, 'Program 2: National Investment Programming Program', [
                [
                    'Provision of Technical and Secretariat Support Services to the Investment Coordination Committee, and the Infrastructure Committee',
                    '310200100001000',
                ],
                [
                    'Coordination to the Formulation and Updating of Public Investment Programs',
                    '310200100002000',
                ],
                [
                    'Appraisal of Proposed Projects for Official Development Assistance, Local Financing, and for Public-Private Partnership Implementation',
                    '310200100003000',
                ],
                [
                    'Coordination of the Programming of Official Development Assistance in the Form of Grants and Concessional Loans',
                    '310200100004000',
                ],
            ]);

            $this->seedProgram($operations, 'Program 3: National Development Monitoring and Evaluation', [
                [
                    'Monitoring and Evaluation of the Implementation of Plans, Programs, Policies and Projects',
                    '310300100001000',
                ],
                [
                    'Evaluation Services Pursuant to Laws, Rules and Regulations, and other Issuances',
                    '310300100002000',
                ],
            ]);

            $locallyFundedSubHeader = ProgramSubHeader::updateOrCreate(
                [
                    'header_id' => $locallyFunded->id,
                    'sub_header' => 'Locally-Funded Projects',
                ],
                [
                    'sub_code' => 'LFP',
                ]
            );

            $this->seedProgram($locallyFundedSubHeader, 'Locally-Funded Projects', [
                [
                    'Implementation of the Management Information System',
                    '200000200001000',
                ],
                [
                    'Establishment of Innovation Fund pursuant to Section 21 of Republic Act No. 11293 including Provision of Secretariat Services to the National Innovation Council',
                    '310100200005000',
                ],
            ]);
        });
    }

    private function seedProgramSubHeader(
        ProgramHeader $header,
        string $name,
        string $code,
        array $classifications
    ): void {
        $subHeader = ProgramSubHeader::updateOrCreate(
            [
                'header_id' => $header->id,
                'sub_header' => $name,
            ],
            [
                'sub_code' => $code,
            ]
        );

        $this->seedProgram($subHeader, $name, $classifications);
    }

    private function seedProgram(
        ProgramSubHeader $subHeader,
        string $programName,
        array $classifications
    ): void {
        $program = Program::updateOrCreate(
            [
                'sub_header_id' => $subHeader->id,
                'program' => $programName,
            ],
            []
        );

        foreach ($classifications as [$classification, $prexc]) {
            Expenditure::updateOrCreate(
                [
                    'program_id' => $program->id,
                    'expenditure' => $classification,
                ],
                [
                    'prexc' => $prexc,
                    'is_ops' => $subHeader->sub_code === 'III',
                ]
            );
        }
    }
}
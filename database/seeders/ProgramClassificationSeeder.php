<?php

namespace Database\Seeders;

use App\Models\Expenditure;
use App\Models\Program;
use App\Models\ProgramHeader;
use App\Models\ProgramSubHeader;
use Illuminate\Database\Seeder;

class ProgramClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $programsHeader = ProgramHeader::firstOrCreate([
            'header' => 'Programs',
        ]);

        $locallyFundedHeader = ProgramHeader::firstOrCreate([
            'header' => 'Locally-Funded Projects',
        ]);

        $gas = ProgramSubHeader::firstOrCreate(
            [
                'header_id' => $programsHeader->id,
                'sub_header' => 'General Administration and Support',
            ],
            [
                'sub_code' => 'GAS',
            ]
        );

        $sto = ProgramSubHeader::firstOrCreate(
            [
                'header_id' => $programsHeader->id,
                'sub_header' => 'Support to Operations',
            ],
            [
                'sub_code' => 'STO',
            ]
        );

        $ops = ProgramSubHeader::firstOrCreate(
            [
                'header_id' => $programsHeader->id,
                'sub_header' => 'Operations',
            ],
            [
                'sub_code' => 'OPS',
            ]
        );

        $program1 = Program::firstOrCreate([
            'sub_header_id' => $ops->id,
            'program' => 'Program 1: Socioeconomic Policy and Planning Program',
        ]);

        $program2 = Program::firstOrCreate([
            'sub_header_id' => $ops->id,
            'program' => 'Program 2: National Investment Programming Program',
        ]);

        $program3 = Program::firstOrCreate([
            'sub_header_id' => $ops->id,
            'program' => 'Program 3: National Development Monitoring and Evaluation',
        ]);

        $this->createExpenditures($program1, [
            [
                'expenditure' => 'Coordination of the Formulation and Updating of National, Inter-regional, Regional, and Sectoral Socioeconomic, Physical and Development Policies, and Plans',
                'prexc' => '310100100001000',
            ],
            [
                'expenditure' => 'Provision of Technical and Secretariat Support Services to the Economy and Development Council and its Committees and other Inter-agency Committees',
                'prexc' => '310100100002000',
            ],
            [
                'expenditure' => 'Provision of Support Services to Regional Development Councils',
                'prexc' => '310100100003000',
            ],
            [
                'expenditure' => 'Provision of Advisory Services and Assistance to the President, Cabinet, Congress, Inter-agency Bodies, and other government entities and instrumentalities on Socio-economic and Development Matters',
                'prexc' => '310100100004000',
            ],
            [
                'expenditure' => 'Provision of Technical and Secretariat Support Services to the LEDAC and its sub-committee and technical working group',
                'prexc' => '310100100005000',
            ],
        ]);

        $this->createExpenditures($program2, [
            [
                'expenditure' => 'Provision of Technical and Secretariat Support Services to the Investment Coordination Committee, and the Infrastructure Committee',
                'prexc' => '310200100001000',
            ],
            [
                'expenditure' => 'Coordination to the Formulation and Updating of Public Investment Programs',
                'prexc' => '310200100002000',
            ],
            [
                'expenditure' => 'Appraisal of Proposed Projects for Official Development Assistance, Local Financing, and for Public-Private Partnership Implementation',
                'prexc' => '310200100003000',
            ],
            [
                'expenditure' => 'Coordination of the Programming of Official Development Assistance in the Form of Grants and Concessional Loans',
                'prexc' => '310200100004000',
            ],
        ]);

        $this->createExpenditures($program3, [
            [
                'expenditure' => 'Monitoring and Evaluation of the Implementation of Plans, Programs, Policies and Projects',
                'prexc' => '310300100001000',
            ],
            [
                'expenditure' => 'Evaluation Services Pursuant to Laws, Rules and Regulations, and other Issuances',
                'prexc' => '310300100002000',
            ],
        ]);

        $locallyFundedProgram = Program::firstOrCreate([
            'sub_header_id' => $this->getLocallyFundedSubHeader($locallyFundedHeader)->id,
            'program' => 'Locally-Funded Projects',
        ]);

        $this->createExpenditures($locallyFundedProgram, [
            [
                'expenditure' => 'Implementation of the Management Information System',
                'prexc' => '200000200001000',
            ],
            [
                'expenditure' => 'Establishment of Innovation Fund pursuant to Section 21 of Republic Act No. 11293 including Provision of Secretariat Services to the National Innovation Council',
                'prexc' => '310100200005000',
            ],
        ]);
    }

    private function createExpenditures(Program $program, array $expenditures): void
    {
        foreach ($expenditures as $data) {
            Expenditure::firstOrCreate(
                [
                    'program_id' => $program->id,
                    'expenditure' => $data['expenditure'],
                ],
                [
                    'prexc' => $data['prexc'],
                    'is_ops' => $program->subHeader->sub_code === 'OPS',
                ]
            );
        }
    }

    private function getLocallyFundedSubHeader(ProgramHeader $header): ProgramSubHeader
    {
        return ProgramSubHeader::firstOrCreate(
            [
                'header_id' => $header->id,
                'sub_header' => 'Locally-Funded Projects',
            ],
            [
                'sub_code' => 'LFP',
            ]
        );
    }
}
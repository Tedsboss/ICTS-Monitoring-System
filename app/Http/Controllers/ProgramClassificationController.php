<?php

namespace App\Http\Controllers;

use App\Models\Expenditure;
use App\Models\Program;
use App\Models\ProgramHeader;
use App\Models\ProgramSubHeader;
use Illuminate\Http\JsonResponse;

class ProgramClassificationController extends Controller
{
    public function headers(): JsonResponse
    {
        $headers = ProgramHeader::query()
            ->orderBy('header')
            ->get(['id', 'header']);

        return response()->json($headers);
    }

    public function subHeaders(ProgramHeader $header): JsonResponse
    {
        $subHeaders = ProgramSubHeader::query()
            ->where('header_id', $header->id)
            ->orderBy('sub_header')
            ->get([
                'id',
                'header_id',
                'sub_header',
                'sub_code',
            ]);

        return response()->json($subHeaders);
    }

    public function programs(ProgramSubHeader $subHeader): JsonResponse
    {
        $programs = Program::query()
            ->where('sub_header_id', $subHeader->id)
            ->orderBy('program')
            ->get([
                'id',
                'sub_header_id',
                'program',
            ]);

        return response()->json($programs);
    }

    public function expenditures(Program $program): JsonResponse
    {
        $expenditures = Expenditure::query()
            ->where('program_id', $program->id)
            ->orderBy('expenditure')
            ->get([
                'id',
                'program_id',
                'expenditure',
                'prexc',
                'is_ops',
            ]);

        return response()->json($expenditures);
    }
}
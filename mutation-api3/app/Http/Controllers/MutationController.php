<?php

namespace App\Http\Controllers;

use App\Services\MutationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class MutationController extends Controller
{
    protected MutationService $mutationService;

    public function __construct(MutationService $mutationService)
    {
        $this->mutationService = $mutationService;
    }

    /**
     * Endpoint principal para detectar mutaciones en ADN
     * POST /mutation
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function detectMutation(Request $request): JsonResponse
    {
        try {
            // Validar que el request tenga el campo 'dna'
            $request->validate([
                'dna' => 'required|array',
                'dna.*' => 'required|string'
            ]);

            $dna = $request->input('dna');
            
            // Detectar mutación usando el servicio
            $isMutant = $this->mutationService->hasMutation($dna);

            if ($isMutant) {
                return response()->json([
                    'message' => 'Mutation detected',
                    'is_mutant' => true
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No mutation detected',
                    'is_mutant' => false
                ], 403);
            }

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Invalid DNA format',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Endpoint para obtener análisis detallado del ADN
     * POST /mutation/analyze
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function analyzeDna(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'dna' => 'required|array',
                'dna.*' => 'required|string'
            ]);

            $dna = $request->input('dna');
            $analysis = $this->mutationService->analyzeDna($dna);

            return response()->json([
                'dna' => $dna,
                'is_mutant' => $analysis['isMutant'],
                'sequences_found' => $analysis['sequences'],
                'matrix_size' => count($dna) . 'x' . count($dna)
            ], 200);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Invalid DNA format',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Endpoint de prueba para verificar el funcionamiento básico
     * GET /mutation/test
     * 
     * @return JsonResponse
     */
    public function test(): JsonResponse
    {
        // Ejemplos de prueba
        $testCases = [
            'mutant_case' => [
                "ATGCGA",
                "CAGTGC", 
                "TTATGT",
                "AGAAGG",
                "CCCCTA",
                "TCACTG"
            ],
            'human_case' => [
                "ATGCGA",
                "CAGTGC",
                "TTATTT",
                "AGACGG",
                "GCGTCA",
                "TCACTG"
            ]
        ];

        $results = [];
        foreach ($testCases as $caseName => $dna) {
            try {
                $isMutant = $this->mutationService->hasMutation($dna);
                $analysis = $this->mutationService->analyzeDna($dna);
                
                $results[$caseName] = [
                    'dna' => $dna,
                    'is_mutant' => $isMutant,
                    'sequences_found' => $analysis['sequences']
                ];
            } catch (\Exception $e) {
                $results[$caseName] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Test results',
            'results' => $results
        ], 200);
    }
}
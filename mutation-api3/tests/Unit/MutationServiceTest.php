<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\MutationService;
use InvalidArgumentException;

class MutationServiceTest extends TestCase
{
    protected MutationService $mutationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mutationService = new MutationService();
    }

    /**
     * Test caso con mutación (más de 1 secuencia)
     */
    public function test_detects_mutation_with_multiple_sequences()
    {
        $dna = [
            "ATGCGA",
            "CAGTGC", 
            "TTATGT",
            "AGAAGG",
            "CCCCTA",
            "TCACTG"
        ];

        $result = $this->mutationService->hasMutation($dna);
        $this->assertTrue($result);

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertTrue($analysis['isMutant']);
        $this->assertGreaterThan(1, $analysis['sequences']);
    }

    /**
     * Test caso sin mutación (0 o 1 secuencia)
     */
    public function test_detects_no_mutation_with_single_or_no_sequences()
    {
        $dna = [
            "ATGCGA",
            "CAGTGC",
            "TTATTT",
            "AGACGG",
            "GCGTCA",
            "TCACTG"
        ];

        $result = $this->mutationService->hasMutation($dna);
        $this->assertFalse($result);

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertFalse($analysis['isMutant']);
        $this->assertLessThanOrEqual(1, $analysis['sequences']);
    }

    /**
     * Test secuencia horizontal
     */
    public function test_detects_horizontal_sequence()
    {
        $dna = [
            "AAAATG",
            "CAGTGC",
            "TTATGT",
            "AGACGG",
            "GCGTCA",
            "TCACTG"
        ];

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertEquals(1, $analysis['sequences']);
    }

    /**
     * Test secuencia vertical - matriz 4x4 simple
     */
    public function test_detects_vertical_sequence()
    {
        $dna = [
            "ATGC",
            "AGTG",
            "ACTG",
            "AGTG"
        ];

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertEquals(1, $analysis['sequences']); // Solo una secuencia vertical de A's en columna 0
    }

    /**
     * Test secuencia diagonal descendente - matriz 4x4 simple
     */
    public function test_detects_diagonal_descending_sequence()
    {
        $dna = [
            "ATGC",
            "CAGC",
            "TCAG",
            "GTCA"
        ];

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertEquals(1, $analysis['sequences']); // Solo una secuencia diagonal de A's
    }

    /**
     * Test secuencia diagonal ascendente - matriz 4x4 simple
     */
    public function test_detects_diagonal_ascending_sequence()
    {
        $dna = [
            "GTCA",
            "CGAT",
            "TACG",
            "ATGC"
        ];

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertEquals(1, $analysis['sequences']); // Solo una secuencia diagonal ascendente de A's
    }

    /**
     * Test matriz no cuadrada
     */
    public function test_throws_exception_for_non_square_matrix()
    {
        $dna = [
            "ATGCGA",
            "CAGTGC",
            "TTATGT",
            "AGAAGG",
            "CCCCTA"  // falta una fila
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DNA must be a square matrix');
        
        $this->mutationService->hasMutation($dna);
    }

    /**
     * Test caracteres inválidos
     */
    public function test_throws_exception_for_invalid_characters()
    {
        $dna = [
            "ATGCGA",
            "CAGTGC",
            "TTATGT",
            "AGAAGG",
            "CCXCTA",  // X es inválido
            "TCACTG"
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid character');
        
        $this->mutationService->hasMutation($dna);
    }

    /**
     * Test matriz vacía
     */
    public function test_throws_exception_for_empty_array()
    {
        $dna = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DNA array cannot be empty');
        
        $this->mutationService->hasMutation($dna);
    }

    /**
     * Test caso con múltiples secuencias para verificar early exit
     */
    public function test_early_exit_with_multiple_sequences()
    {
        $dna = [
            "AAAATG",  // Secuencia horizontal
            "CAGTGC",
            "CCCCGT",  // Otra secuencia horizontal
            "AGACGG",
            "GCGTCA",
            "TCACTG"
        ];

        $result = $this->mutationService->hasMutation($dna);
        $this->assertTrue($result);

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertEquals(2, $analysis['sequences']);
    }

    /**
     * Test matriz 4x4 mínima
     */
    public function test_minimum_4x4_matrix()
    {
        $dna = [
            "AAAA",
            "TGCC",
            "CCCC",
            "GTAG"
        ];

        $result = $this->mutationService->hasMutation($dna);
        $this->assertTrue($result);

        $analysis = $this->mutationService->analyzeDna($dna);
        $this->assertEquals(2, $analysis['sequences']);
    }
}
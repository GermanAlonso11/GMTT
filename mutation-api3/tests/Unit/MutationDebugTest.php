<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\MutationService;

class MutationDebugTest extends TestCase
{
    protected MutationService $mutationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mutationService = new MutationService();
    }

    /**
     * Test debug para ver exactamente qué secuencias detecta
     */
    public function test_debug_vertical_sequence()
    {
        $dna = [
            "ATGC",
            "ATGC", 
            "ATGC",
            "AGCC"
        ];

        echo "\nMatriz de prueba vertical:\n";
        foreach ($dna as $row) {
            echo $row . "\n";
        }

        $analysis = $this->mutationService->analyzeDna($dna);
        echo "Secuencias detectadas: " . $analysis['sequences'] . "\n";
        
        // Vamos a mostrar manualmente qué debería detectar
        // Columna 0: A, A, A, A -> 4 A's verticales ✓
        // Columna 1: T, T, T, G -> 3 T's no cuenta
        // Fila 0: A, T, G, C -> no hay 4 iguales
        // etc.
        
        $this->assertTrue($analysis['sequences'] >= 1);
    }

    public function test_debug_simple_horizontal()
    {  
        $dna = [
            "AAAA",
            "TGCT",
            "CGTA", 
            "GTAC"
        ];

        echo "\nMatriz de prueba horizontal:\n";
        foreach ($dna as $row) {
            echo $row . "\n";
        }

        $analysis = $this->mutationService->analyzeDna($dna);
        echo "Secuencias detectadas: " . $analysis['sequences'] . "\n";
        
        $this->assertEquals(1, $analysis['sequences']);
    }

    public function test_debug_only_vertical_As()
    {
        $dna = [
            "ATGC",
            "AGTC", 
            "ACTC",
            "AGTC"
        ];

        echo "\nMatriz solo vertical A's:\n";  
        foreach ($dna as $row) {
            echo $row . "\n";
        }

        $analysis = $this->mutationService->debugAnalyzeDna($dna);
        echo "Secuencias detectadas: " . $analysis['sequences'] . "\n";
        echo "Detalles:\n";
        foreach ($analysis['details'] as $detail) {
            echo "- Inicio: [{$detail['start'][0]},{$detail['start'][1]}], Dirección: {$detail['direction']}, Carácter: {$detail['character']}\n";
        }
        
        // Solo debería haber 1 secuencia: A vertical en columna 0
        $this->assertEquals(1, $analysis['sequences']);
    }
}
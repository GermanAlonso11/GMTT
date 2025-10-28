<?php

namespace App\Services;

use InvalidArgumentException;

class MutationService
{
    /**
     * Detecta si hay más de una secuencia de 4 letras iguales en el ADN
     * 
     * @param array<string> $dna Array de strings representando filas del ADN
     * @return bool true si hay mutación (>1 secuencia), false si no
     * @throws InvalidArgumentException si la matriz no es válida
     */
    public function hasMutation(array $dna): bool
    {
        // Validaciones iniciales
        $n = count($dna);
        if ($n === 0) {
            throw new InvalidArgumentException('DNA array cannot be empty');
        }

        // Validar que sea matriz cuadrada y solo contenga A,T,C,G
        $matrix = $this->validateAndBuildMatrix($dna, $n);

        // Buscar secuencias en las 4 direcciones
        $matchesFound = 0;

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $ch = $matrix[$r][$c];

                // Horizontal (→)
                if ($this->checkDirection($matrix, $r, $c, 0, 1, $ch, $n)) {
                    $matchesFound++;
                    if ($matchesFound > 1) return true;
                }

                // Vertical (↓)
                if ($this->checkDirection($matrix, $r, $c, 1, 0, $ch, $n)) {
                    $matchesFound++;
                    if ($matchesFound > 1) return true;
                }

                // Diagonal descendente (↘)
                if ($this->checkDirection($matrix, $r, $c, 1, 1, $ch, $n)) {
                    $matchesFound++;
                    if ($matchesFound > 1) return true;
                }

                // Diagonal ascendente (↗)
                if ($this->checkDirection($matrix, $r, $c, -1, 1, $ch, $n)) {
                    $matchesFound++;
                    if ($matchesFound > 1) return true;
                }
            }
        }

        return false;
    }

    /**
     * Valida y construye la matriz de caracteres desde el array de strings
     * 
     * @param array<string> $dna
     * @param int $n
     * @return array<array<string>>
     * @throws InvalidArgumentException
     */
    private function validateAndBuildMatrix(array $dna, int $n): array
    {
        $matrix = [];
        $validChars = ['A', 'T', 'C', 'G'];

        foreach ($dna as $i => $row) {
            if (strlen($row) !== $n) {
                throw new InvalidArgumentException("DNA must be a square matrix. Row $i has length " . strlen($row) . ", expected $n");
            }

            $matrixRow = [];
            for ($j = 0; $j < $n; $j++) {
                $char = strtoupper($row[$j]);
                if (!in_array($char, $validChars, true)) {
                    throw new InvalidArgumentException("Invalid character '$char' at position [$i][$j]. Only A, T, C, G are allowed");
                }
                $matrixRow[] = $char;
            }
            $matrix[] = $matrixRow;
        }

        return $matrix;
    }

    /**
     * Verifica si hay una secuencia de 4 caracteres iguales desde una posición en una dirección específica
     * Aplica regla de "inicio válido" para evitar contar la misma secuencia múltiples veces
     * 
     * @param array<array<string>> $matrix
     * @param int $r fila inicial
     * @param int $c columna inicial
     * @param int $dr incremento de fila
     * @param int $dc incremento de columna
     * @param string $ch carácter a buscar
     * @param int $n tamaño de la matriz
     * @return bool
     */
    private function checkDirection(array $matrix, int $r, int $c, int $dr, int $dc, string $ch, int $n): bool
    {
        // Verificar que hay espacio para 4 caracteres en esta dirección
        $endR = $r + ($dr * 3);
        $endC = $c + ($dc * 3);
        
        if ($endR < 0 || $endR >= $n || $endC < 0 || $endC >= $n) {
            return false;
        }

        // Regla de "inicio válido": solo contar si esta es la primera posición de la secuencia
        // Verificar que la posición anterior en la dirección opuesta no tiene el mismo carácter
        $prevR = $r - $dr;
        $prevC = $c - $dc;
        
        if ($prevR >= 0 && $prevR < $n && $prevC >= 0 && $prevC < $n) {
            if ($matrix[$prevR][$prevC] === $ch) {
                return false; // No es el inicio de la secuencia
            }
        }

        // Verificar que los 4 caracteres consecutivos son iguales
        for ($i = 0; $i < 4; $i++) {
            $currentR = $r + ($dr * $i);
            $currentC = $c + ($dc * $i);
            
            if ($matrix[$currentR][$currentC] !== $ch) {
                return false;
            }
        }

        return true;
    }

    /**
     * Método de utilidad para obtener estadísticas de ADN (para endpoints adicionales)
     * 
     * @param array<string> $dna
     * @return array{isMutant: bool, sequences: int}
     */
    public function analyzeDna(array $dna): array
    {
        try {
            $n = count($dna);
            $matrix = $this->validateAndBuildMatrix($dna, $n);
            $sequences = 0;

            for ($r = 0; $r < $n; $r++) {
                for ($c = 0; $c < $n; $c++) {
                    $ch = $matrix[$r][$c];

                    if ($this->checkDirection($matrix, $r, $c, 0, 1, $ch, $n)) $sequences++;
                    if ($this->checkDirection($matrix, $r, $c, 1, 0, $ch, $n)) $sequences++;
                    if ($this->checkDirection($matrix, $r, $c, 1, 1, $ch, $n)) $sequences++;
                    if ($this->checkDirection($matrix, $r, $c, -1, 1, $ch, $n)) $sequences++;
                }
            }

            return [
                'isMutant' => $sequences > 1,
                'sequences' => $sequences
            ];
        } catch (InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Método de debug para mostrar exactamente dónde se detectan las secuencias
     * 
     * @param array<string> $dna
     * @return array{isMutant: bool, sequences: int, details: array}
     */
    public function debugAnalyzeDna(array $dna): array
    {
        try {
            $n = count($dna);
            $matrix = $this->validateAndBuildMatrix($dna, $n);
            $sequences = 0;
            $details = [];

            for ($r = 0; $r < $n; $r++) {
                for ($c = 0; $c < $n; $c++) {
                    $ch = $matrix[$r][$c];

                    if ($this->checkDirection($matrix, $r, $c, 0, 1, $ch, $n)) {
                        $sequences++;
                        $details[] = [
                            'start' => [$r, $c],
                            'direction' => 'horizontal',
                            'character' => $ch,
                            'positions' => [[$r, $c], [$r, $c+1], [$r, $c+2], [$r, $c+3]]
                        ];
                    }
                    if ($this->checkDirection($matrix, $r, $c, 1, 0, $ch, $n)) {
                        $sequences++;
                        $details[] = [
                            'start' => [$r, $c],
                            'direction' => 'vertical',
                            'character' => $ch,
                            'positions' => [[$r, $c], [$r+1, $c], [$r+2, $c], [$r+3, $c]]
                        ];
                    }
                    if ($this->checkDirection($matrix, $r, $c, 1, 1, $ch, $n)) {
                        $sequences++;
                        $details[] = [
                            'start' => [$r, $c],
                            'direction' => 'diagonal_down',
                            'character' => $ch,
                            'positions' => [[$r, $c], [$r+1, $c+1], [$r+2, $c+2], [$r+3, $c+3]]
                        ];
                    }
                    if ($this->checkDirection($matrix, $r, $c, -1, 1, $ch, $n)) {
                        $sequences++;
                        $details[] = [
                            'start' => [$r, $c],
                            'direction' => 'diagonal_up',
                            'character' => $ch,
                            'positions' => [[$r, $c], [$r-1, $c+1], [$r-2, $c+2], [$r-3, $c+3]]
                        ];
                    }
                }
            }

            return [
                'isMutant' => $sequences > 1,
                'sequences' => $sequences,
                'details' => $details
            ];
        } catch (InvalidArgumentException $e) {
            throw $e;
        }
    }
}
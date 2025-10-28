# API de Detección de Mutaciones - GMTT

## Descripción

Esta API implementa un algoritmo eficiente para detectar mutaciones en secuencias de ADN. Una mutación se detecta cuando hay **más de una secuencia de 4 letras iguales** en cualquier dirección (horizontal, vertical o diagonal).

## Implementación

### Características principales:

1. **Validaciones estrictas**: 
   - Matriz cuadrada NxN
   - Solo caracteres A, T, C, G (mayúsculas)
   - Retorna HTTP 400 si no cumple

2. **Algoritmo optimizado**:
   - Complejidad O(N²) con constantes pequeñas
   - Early exit cuando se detecta más de 1 secuencia
   - Evita contar duplicados con regla de "inicio válido"

3. **Búsqueda en 4 direcciones**:
   - Horizontal (→)
   - Vertical (↓) 
   - Diagonal descendente (↘)
   - Diagonal ascendente (↗)

## Endpoints

### POST /mutation
Detecta si hay mutación en una secuencia de ADN.

**Request Body:**
```json
{
  "dna": [
    "ATGCGA",
    "CAGTGC", 
    "TTATGT",
    "AGAAGG",
    "CCCCTA",
    "TCACTG"
  ]
}
```

**Response:**
- **200**: Mutación detectada
- **403**: No hay mutación
- **400**: Formato de ADN inválido

### POST /mutation/analyze
Proporciona análisis detallado de la secuencia de ADN.

**Response:**
```json
{
  "dna": ["ATGCGA", "CAGTGC", ...],
  "is_mutant": true,
  "sequences_found": 2,
  "matrix_size": "6x6"
}
```

### GET /mutation/test
Endpoint de prueba con casos predefinidos.

## Casos de Prueba

### Mutante (>1 secuencia):
```
ATGCGA
CAGTGC 
TTATGT
AGAAGG
CCCCTA  ← 4 C's horizontales
TCACTG
```

### Humano (≤1 secuencia):
```
ATGCGA
CAGTGC
TTATTT
AGACGG
GCGTCA
TCACTG
```

## Ejemplos de uso

### Con curl:

```bash
# Detectar mutación
curl -X POST http://127.0.0.1:8000/mutation \
  -H "Content-Type: application/json" \
  -d '{
    "dna": [
      "ATGCGA",
      "CAGTGC", 
      "TTATGT",
      "AGAAGG",
      "CCCCTA",
      "TCACTG"
    ]
  }'

# Análisis detallado
curl -X POST http://127.0.0.1:8000/mutation/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "dna": [
      "AAAA",
      "TGCT",
      "CGTA", 
      "GTAC"
    ]
  }'

# Test predefinido
curl http://127.0.0.1:8000/mutation/test
```

## Arquitectura

```
app/
├── Http/Controllers/
│   └── MutationController.php    # Endpoints REST
├── Services/
│   └── MutationService.php       # Lógica de detección
tests/
├── Unit/
│   └── MutationServiceTest.php   # Tests unitarios
```

## Tests

Ejecutar todos los tests:
```bash
php artisan test tests/Unit/MutationServiceTest.php
```

## Servidor

Iniciar servidor de desarrollo:
```bash
php artisan serve
```

El servidor estará disponible en: http://127.0.0.1:8000
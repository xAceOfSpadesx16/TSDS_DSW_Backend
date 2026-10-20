<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Cobertura de la configuración de CORS.
 *
 * Casos verificados:
 *   1) El preflight de `DELETE /api/history/{id}` con un origen permitido
 *      devuelve los headers `Access-Control-Allow-*` correspondientes.
 *   2) Con un origen no permitido, no se devuelven.
 *   3) `DELETE` está entre los métodos permitidos (si no, el preflight del
 *      borrado de historial nunca pasaría).
 *   4) `allowed_origins` sale de `CORS_ALLOWED_ORIGINS`, y el default son los
 *      dos orígenes del dev server.
 */
class CorsTest extends TestCase
{
    /** Origen por defecto permitido: el dev server de Vite. */
    private const ORIGIN_PERMITIDO = 'http://localhost:5173';

    /** Origen inventado, fuera de la lista. */
    private const ORIGIN_NO_PERMITIDO = 'http://sitio-no-permitido.example.com';

    public function test_preflight_delete_de_un_origen_permitido_devuelve_los_headers_cors(): void
    {
        $response = $this->withHeaders([
            'Origin' => self::ORIGIN_PERMITIDO,
            'Access-Control-Request-Method' => 'DELETE',
            'Access-Control-Request-Headers' => 'authorization,content-type',
        ])->options('/api/history/01a10e7e-d990-7072-9f16-b20348cefbce');

        $response->assertSuccessful();
        $response->assertHeader('Access-Control-Allow-Origin', self::ORIGIN_PERMITIDO);
        $response->assertHeader('Access-Control-Allow-Methods');

        // El método pedido tiene que estar entre los permitidos.
        $this->assertStringContainsString(
            'DELETE',
            (string) $response->headers->get('Access-Control-Allow-Methods')
        );

        // El header Authorization es imprescindible: el JWT viaja ahí.
        $allowedHeaders = strtolower((string) $response->headers->get('Access-Control-Allow-Headers'));
        $this->assertStringContainsString('authorization', $allowedHeaders);
    }

    public function test_preflight_de_un_origen_no_permitido_no_devuelve_headers_cors(): void
    {
        $response = $this->withHeaders([
            'Origin' => self::ORIGIN_NO_PERMITIDO,
            'Access-Control-Request-Method' => 'DELETE',
        ])->options('/api/history/01a10e7e-d990-7072-9f16-b20348cefbce');

        // El preflight responde igual, pero sin conceder acceso al origen.
        $this->assertNull($response->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_los_metodos_permitidos_incluyen_get_post_delete_y_options(): void
    {
        $allowed = config('cors.allowed_methods');

        $this->assertContains('GET', $allowed);
        $this->assertContains('POST', $allowed);
        $this->assertContains('DELETE', $allowed);
        $this->assertContains('OPTIONS', $allowed);
    }

    public function test_allowed_origins_toma_el_valor_de_la_variable_de_entorno(): void
    {
        $origins = $this->corsConfigWithEnv([
            'CORS_ALLOWED_ORIGINS' => 'https://app.example.com, https://alt.example.com',
        ]);

        $this->assertSame(
            ['https://app.example.com', 'https://alt.example.com'],
            $origins,
            'La lista debe venir de CORS_ALLOWED_ORIGINS, con los espacios recortados.'
        );
    }

    public function test_allowed_origins_descarta_entradas_vacias(): void
    {
        $origins = $this->corsConfigWithEnv([
            'CORS_ALLOWED_ORIGINS' => 'https://app.example.com,,  ,https://alt.example.com',
        ]);

        $this->assertSame(
            ['https://app.example.com', 'https://alt.example.com'],
            $origins,
            'Los elementos vacíos del CSV no deben generar orígenes en blanco.'
        );
    }

    public function test_sin_variable_de_entorno_caen_los_origenes_de_desarrollo(): void
    {
        $origins = $this->corsConfigWithEnv([]);

        $this->assertSame(
            ['http://localhost:5173', 'http://127.0.0.1:5173'],
            $origins
        );
    }

    /**
     * Resuelve `config/cors.php` de nuevo con las variables de entorno dadas.
     *
     * Un test no puede llamar a `putenv` y esperar que la config ya resuelta
     * cambie: `config/cors.php` lee `env()` al cargarse, y ya fue cargada al
     * bootear la app. Re-evaluar el archivo es la forma de probar el parseo
     * sin depender de un `.env` distinto por test.
     *
     * @param  array<string, string>  $env
     * @return list<string>
     */
    private function corsConfigWithEnv(array $env): array
    {
        $previousEnv = $_ENV;
        $previousServer = $_SERVER;

        foreach ($env as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        try {
            /** @var array{allowed_origins: list<string>} $config */
            $config = require config_path('cors.php');

            return $config['allowed_origins'];
        } finally {
            foreach (array_keys($env) as $key) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);
            }
            $_ENV = $previousEnv;
            $_SERVER = $previousServer;
        }
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Solo se aplica CORS a las rutas de la API.
    // Sanctum no se usa (la auth es por JWT en `Authorization: Bearer ...`),
    // por lo que no necesitamos exponer /sanctum/csrf-cookie.
    'paths' => ['api/*'],

    // Métodos permitidos para esta API. DELETE cubre el borrado de
    // historial (DELETE /api/history/{id} y DELETE /api/histories).
    'allowed_methods' => ['GET', 'POST', 'DELETE', 'OPTIONS'],

    // Orígenes habilitados.
    // - Por defecto: los dos del dev server de Vite (SPA React).
    // - En producción o con más entornos, se sobreescribe la variable
    //   `CORS_ALLOWED_ORIGINS` con una lista separada por comas.
    //
    //   CORS_ALLOWED_ORIGINS=https://app.example.com,https://www.example.com
    //
    // Se parsea en runtime (no con `env()` directo en un array) para poder
    // reescribir el origen del front sin editar este archivo.
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env(
            'CORS_ALLOWED_ORIGINS',
            'http://localhost:5173,http://127.0.0.1:5173'
        ))
    ))),

    'allowed_origins_patterns' => [],

    // Headers que el cliente puede enviar.
    // - Authorization: para el JWT (`Bearer <token>`).
    // - Content-Type:  para application/json en POST.
    // - Accept:        para forzar respuestas JSON.
    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept'],

    'exposed_headers' => [],

    // Cache del preflight durante 1 día para no martillar el endpoint
    // OPTIONS en cada request. Reduce latencia en uso normal.
    'max_age' => 86400,

    // Sin credenciales cross-origin: el JWT viaja en `Authorization`,
    // no en cookies, así que no hace falta `supports_credentials=true`.
    'supports_credentials' => false,

];

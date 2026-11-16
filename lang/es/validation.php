<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación
    |--------------------------------------------------------------------------
    |
    | Traducciones de las reglas que usan los Form Requests de autenticación
    | e historial. El mensaje de cada regla compuesta vive en su clave.
    |
    */

    'required' => 'El campo :attribute es obligatorio.',
    'present' => 'El campo :attribute debe estar presente.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'unique' => 'El valor del campo :attribute ya está en uso.',
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'array' => 'El campo :attribute debe ser una lista.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'in' => 'El campo :attribute debe ser uno de: :values.',

    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
    ],

    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file' => 'El archivo :attribute no debe pesar más de :max kilobytes.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
    ],

    'size' => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file' => 'El archivo :attribute debe pesar :size kilobytes.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
        'array' => 'El campo :attribute debe contener :size elementos.',
    ],

    'gt' => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'file' => 'El archivo :attribute debe pesar más de :value kilobytes.',
        'string' => 'El campo :attribute debe tener más de :value caracteres.',
        'array' => 'El campo :attribute debe tener más de :value elementos.',
    ],

    'gte' => [
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'file' => 'El archivo :attribute debe pesar :value kilobytes o más.',
        'string' => 'El campo :attribute debe tener :value caracteres o más.',
        'array' => 'El campo :attribute debe tener :value elementos o más.',
    ],

    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file' => 'El archivo :attribute debe pesar entre :min y :max kilobytes.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nombres de los campos
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'payload.sides' => 'caras del dado',
        'payload.modifier' => 'modificador',
        'payload.count' => 'cantidad',
        'payload.formula' => 'fórmula',
        'payload.min' => 'mínimo',
        'payload.max' => 'máximo',
        'payload.unique' => 'único',
        'payload.options' => 'opciones',
        'payload.options.*' => 'cada opción',
        'payload.options.*.label' => 'etiqueta de la opción',
        'payload.options.*.color' => 'color de la opción',
        'payload.participants' => 'participantes',
        'payload.participants.*' => 'cada participante',
        'payload.mode' => 'modo',
        'payload.value' => 'valor',
        'payload.exclusions' => 'exclusiones',
        'payload.exclusions.*' => 'cada exclusión',
        'payload.exclusions.*.*' => 'cada extremo de la exclusión',
        'payload.entries' => 'entradas',
        'payload.entries.*' => 'cada entrada',
        'payload.entries.*.name' => 'nombre de la entrada',
        'payload.entries.*.weight' => 'peso de la entrada',
        'payload.autoRemove' => 'autoeliminar',
        'result.rolls' => 'tiradas',
        'result.rolls.*' => 'cada tirada',
        'result.total' => 'total',
        'result.modifier' => 'modificador del resultado',
        'result.numbers' => 'números',
        'result.numbers.*' => 'cada número',
        'result.winner' => 'ganador',
        'result.winner.label' => 'etiqueta del ganador',
        'result.winner.color' => 'color del ganador',
        'result.winner.name' => 'nombre del ganador',
        'result.winner.weight' => 'peso del ganador',
        'result.probability' => 'probabilidad',
        'result.teams' => 'equipos',
        'result.teams.*' => 'cada equipo',
        'result.teams.*.*' => 'cada miembro del equipo',
        'result.colors' => 'colores',
        'result.colors.*' => 'cada color',
    ],

];

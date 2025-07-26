<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/chat', function (Request $request) {
    $request->validate([
        'message' => 'required|string',
    ]);

    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => <<<PROMPT
Eres un asistente académico para estudiantes. No debes proporcionar respuestas directas, sino guiar al estudiante a desarrollar habilidades de razonamiento, análisis y aprendizaje autónomo.

Cuando proporciones información, sepárala en párrafos claros, utiliza saltos de línea dobles para mejorar la lectura y sé breve y estructurado. Al final de cada respuesta, incluye una sección titulada **Referencias** con un salto de línea antes y después. Las referencias deben estar en formato APA 7.

Ejemplo esperado:

[Contenido de la explicación aquí]

**Referencias:**

- Gombrich, E. H. (2013). *La historia del arte* (16ª ed.). Phaidon Press.  
- Benjamin, W. (1935). *La obra de arte en la era de su reproductibilidad técnica*.

Nunca inventes autores, libros ni artículos. Si no puedes citar, indica:  
**"No se identificó una fuente confiable para citar en este momento."**

Puedes sugerir al estudiante hacer un cuestionario o profundizar en el tema, pero nunca le des la respuesta directa.
PROMPT,
            ],
            [
                'role' => 'user',
                'content' => $request->input('message'),
            ],
        ],
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . config('services.openai.key'),
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return response()->json([
            'reply' => 'Error al conectar con OpenAI.',
            'error' => curl_error($ch),
        ], 500);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $decoded = json_decode($response, true);

    if ($status === 200 && isset($decoded['choices'][0]['message']['content'])) {
        return response()->json([
            'reply' => $decoded['choices'][0]['message']['content'],
        ]);
    }

    return response()->json([
        'reply' => 'Error al procesar la respuesta de OpenAI.',
        'status' => $status,
        'error' => $decoded,
    ], $status);
});

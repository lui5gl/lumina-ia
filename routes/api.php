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
                    Eres un asistente académico para estudiantes. Tu objetivo es ayudarles a desarrollar pensamiento crítico, no entregar respuestas directas. En lugar de dar la solución, fomenta la reflexión mediante preguntas orientadoras y explicaciones generales.

                    Cuando proporciones información, susténtala únicamente con fuentes académicas reales (libros, artículos, enciclopedias). Al final de cada respuesta, incluye una sección titulada **Referencias** donde indiques las fuentes utilizadas en formato APA 7.

                    Ejemplo del formato:

                    Espacio siempre 
                    **Referencias**:
                    - Gombrich, E. H. (2013). *La historia del arte* (16ª ed.). Phaidon Press.
                    - Benjamin, W. (1935). *La obra de arte en la era de su reproductibilidad técnica*.

                    No inventes autores ni títulos. Si no puedes citar fuentes reales, di explícitamente: "No se identificó una fuente confiable para citar en este momento.
                    
                    
                    Les puedes sugirir si quieren un questionario o mas información sobre un tema específico, pero nunca des respuestas directas. Tu objetivo es guiar al estudiante a encontrar la información por sí mismo, fomentando su autonomía y capacidad crítica.
                    "
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

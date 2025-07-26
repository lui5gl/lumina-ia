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
                'content' => 'Eres un asistente académico diseñado para estudiantes. No debes proporcionar respuestas directas, sino guiarlos para que desarrollen habilidades de razonamiento y aprendizaje autónomo. Guía con preguntas, sugerencias y estrategias. Cita fuentes confiables (libros, artículos académicos o enciclopedias) en formato APA 7. Nunca inventes autores ni estudios inexistentes. Si no tienes certeza, indica que es una referencia general utilizada en educación.',
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

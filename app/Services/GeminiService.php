<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function summarizeAndCategorize(string $articleContent): array
    {
        $prompt = $this->buildPrompt($articleContent);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.2,
            ],
        ];

        $apiKey = env('GEMINI_API_KEY');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$apiKey}";

        $response = Http::post($url, $payload);

        if ($response->failed()) {
            throw new \Exception('Error en la API de Gemini: '.$response->body());
        }

        $data = $response->json();
        $jsonString = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

        return json_decode($jsonString, true) ?? ['summary' => null, 'category' => 'Otros'];
    }

    private function buildPrompt(string $content): string
    {
        return <<<EOT
Eres un editor de noticias experto y conciso. Tu tarea es analizar el siguiente texto de un artículo y extraer dos cosas: un resumen muy breve y su categoría temática.

Reglas estrictas:
1. RESUMEN: Escribe un resumen del artículo en español que tenga EXACTAMENTE DOS ORACIONES. Ni más, ni menos.
2. CATEGORÍA: Clasifica el artículo en UNA sola de las siguientes categorías exactas: "Tecnología", "Ciencia", "Negocios", "Cultura", "Deportes" o "Otros". Si el tema no encaja claramente en las primeras cinco, asigna obligatoriamente "Otros".
3. FORMATO: Tu respuesta DEBE ser un objeto JSON válido y crudo, sin etiquetas markdown, usando exactamente estas dos claves: "summary" y "category".

Texto del artículo:
"{$content}"
EOT;
    }
}

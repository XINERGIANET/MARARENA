<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class QzController extends Controller
{
    public function certificate()
    {
        try {
            $certificate = $this->readConfiguredFile('qz.certificate_path', 'certificado');

            return response($certificate, 200, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Cache-Control' => 'no-store',
            ]);
        } catch (\Throwable $e) {
            Log::error('QZ certificate error: ' . $e->getMessage());
            return response('No se pudo obtener el certificado de QZ.', 500);
        }
    }

    public function sign(Request $request)
    {
        try {
            $request->validate([
                'request' => 'required|string',
            ]);

            $privateKeyPem = $this->readConfiguredFile('qz.private_key_path', 'clave privada');
            $privateKey = openssl_pkey_get_private($privateKeyPem);

            if (!$privateKey) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se pudo cargar la clave privada de QZ.',
                ], 500);
            }

            $signature = null;
            $algo = $this->opensslAlgorithmFromConfig();
            $ok = openssl_sign($request->input('request'), $signature, $privateKey, $algo);
            openssl_free_key($privateKey);

            if (!$ok || $signature === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se pudo firmar la solicitud de QZ.',
                ], 500);
            }

            return response()->json([
                'status' => true,
                'signature' => base64_encode($signature),
            ]);
        } catch (\Throwable $e) {
            Log::error('QZ sign error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'No se pudo firmar la solicitud de QZ.',
            ], 500);
        }
    }

    private function readConfiguredFile(string $configKey, string $label): string
    {
        $configured = (string) config($configKey, '');

        if ($configured === '') {
            throw new \RuntimeException("Archivo de {$label} QZ no configurado en {$configKey}.");
        }

        // Soporta caso accidental donde se pegue el contenido PEM/base64 en el .env.
        if (strpos($configured, 'BEGIN ') !== false || strlen($configured) > 500) {
            return trim($configured);
        }

        $path = $this->resolvePath($configured);

        if (!File::exists($path)) {
            throw new \RuntimeException("Archivo de {$label} QZ no encontrado en {$path}.");
        }

        $content = (string) File::get($path);

        if (trim($content) === '') {
            throw new \RuntimeException("Archivo de {$label} QZ vacio.");
        }

        return $content;
    }

    private function resolvePath(string $path): string
    {
        if (
            preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) ||
            substr($path, 0, 1) === '/' ||
            substr($path, 0, 2) === '\\\\'
        ) {
            return $path;
        }

        return base_path($path);
    }

    private function opensslAlgorithmFromConfig(): int
    {
        $algo = strtoupper((string) config('qz.signature_algorithm', 'SHA1'));
        if ($algo === 'SHA512') {
            return OPENSSL_ALGO_SHA512;
        }
        if ($algo === 'SHA384') {
            return OPENSSL_ALGO_SHA384;
        }
        if ($algo === 'SHA256') {
            return OPENSSL_ALGO_SHA256;
        }
        return OPENSSL_ALGO_SHA1;
    }
}

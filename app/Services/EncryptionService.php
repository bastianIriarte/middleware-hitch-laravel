<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class EncryptionService
{
    private $key;
    private $iv;

    public function __construct()
    {
        // La clave debe tener 32 bytes para AES-256-CBC
        $this->key = 'hb=$pGWi)V3me3b8#F1_J5qyExv9+zx5'; // 32 bytes
        $this->iv = 'K1tFDd0hFDk&(&qk'; // 16 bytes
    }

    /**
     * Cifra el texto usando AES-256-CBC y lo devuelve en base64.
     */
    public function encrypt($plainText)
    {

        $cipher = 'AES-256-CBC';
        // Usar OPENSSL_RAW_DATA para obtener los datos en bruto
        $encrypted = openssl_encrypt($plainText, $cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);

        return base64_encode($encrypted);
    }



    /**
     * Descifra el texto cifrado en base64.
     */
    public function decrypt($cipherText)
    {
        try {
            // Verifica si el texto es válido antes de intentar descifrarlo
            if (empty($cipherText) || !$this->isBase64($cipherText)) {
                return $cipherText;
            }

            $cipher = 'AES-256-CBC';
            // Usamos OPENSSL_RAW_DATA para trabajar con datos en crudo
            return openssl_decrypt(base64_decode($cipherText), $cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
        } catch (Exception $e) {
            Log::error("Error al descifrar: " . $e->getMessage());
            return $cipherText;
        }
    }

    /**
     * Verifica si una cadena es válida en base64.
     */
    private function isBase64($string)
    {
        if (empty($string) || strlen($string) % 4 !== 0) {
            return false;
        }

        return base64_encode(base64_decode($string, true)) === $string;
    }
}

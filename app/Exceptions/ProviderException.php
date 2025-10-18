<?php

namespace App\Exceptions;

use Exception;

class ProviderException extends Exception
{
    public function __construct(string $provider, string $message, int $code = 500)
    {
        parent::__construct("Erro ao consultar provider {$provider}: {$message}", $code);
    }

    public function render()
    {
        return response()->json([
            'message' => 'Erro ao buscar municípios. Tente novamente mais tarde.',
        ], $this->getCode());
    }
}

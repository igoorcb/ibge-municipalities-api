<?php

namespace App\Exceptions;

use Exception;

class InvalidUfException extends Exception
{
    public function __construct(string $uf)
    {
        parent::__construct("UF inválida: {$uf}. Por favor, informe uma sigla de estado válida.", 422);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}

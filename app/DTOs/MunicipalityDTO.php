<?php

namespace App\DTOs;

readonly class MunicipalityDTO
{
    public function __construct(
        public string $name,
        public string $ibgeCode,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'ibge_code' => $this->ibgeCode,
        ];
    }
}

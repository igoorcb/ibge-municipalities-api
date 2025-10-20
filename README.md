# IBGE Municipalities API

API RESTful para consulta de municípios brasileiros por UF, utilizando provedores externos (BrasilAPI e IBGE) com cache, paginação e arquitetura escalável.

## Índice

- [Tecnologias](#-tecnologias)
- [Arquitetura](#-arquitetura)
- [Instalação](#-instalação)
- [Uso da API](#-uso-da-api)
- [Testes](#-testes)
- [Decisões Arquiteturais](#-decisões-arquiteturais)
- [Escalabilidade](#-escalabilidade)
- [Comandos Úteis](#-comandos-úteis)

## Tecnologias

- **Laravel 11** - Framework PHP
- **PHP 8.2** - Linguagem
- **Redis** - Cache
- **Nginx** - Web server
- **Docker & Docker Compose** - Containerização
- **Pest** - Framework de testes
- **Laravel Pint** - Code style (PSR-12)
- **GitHub Actions** - CI/CD

## Arquitetura

### Design Patterns Implementados

#### 1. Strategy Pattern

Permite trocar o provedor de dados via configuração (.env):

```
App/Providers/Municipality/
├── MunicipalityProviderInterface.php    # Contrato
├── BrasilApiProvider.php                # Implementação BrasilAPI
├── IbgeProvider.php                     # Implementação IBGE
└── MunicipalityProviderFactory.php      # Factory para criar provider
```

#### 2. Service Pattern

Lógica de negócio isolada do controller:

```
App/Services/
└── MunicipalityService.php              # Orquestra cache, validação e provider
```

#### 3. DTO Pattern

Transferência de dados padronizada entre camadas:

```
App/DTOs/
└── MunicipalityDTO.php                  # Readonly class (PHP 8.2)
```

#### 4. API Resource Pattern

Respostas JSON consistentes:

```
App/Http/Resources/
├── MunicipalityResource.php             # Recurso individual
└── MunicipalityCollection.php           # Coleção paginada
```

### Estrutura de Diretórios

```
app/
├── DTOs/                    # Data Transfer Objects
├── Exceptions/              # Exceções customizadas
├── Http/
│   ├── Controllers/         # Controllers (apenas roteamento)
│   ├── Requests/            # Form Requests (validação)
│   └── Resources/           # API Resources (formatação JSON)
├── Providers/
│   └── Municipality/        # Providers de dados externos
└── Services/                # Lógica de negócio

tests/
├── Feature/                 # Testes de integração
└── Unit/                    # Testes unitários
    ├── Providers/
    └── Services/
```

## Instalação

### Pré-requisitos

- Docker
- Docker Compose

### Passo a passo

1. Clone o repositório:

```bash
git clone <repository-url>
cd ibge-municipalities-api
```

2. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

3. Configure as variáveis de ambiente no `.env`:

```env
MUNICIPALITY_PROVIDER=brasilapi  # ou 'ibge'
MUNICIPALITY_CACHE_TTL=3600      # Tempo de cache em segundos
CACHE_STORE=redis
REDIS_HOST=redis
```

4. Suba os containers:

```bash
docker-compose up -d
```

5. Instale as dependências:

```bash
docker-compose exec app composer install
```

6. Gere a chave da aplicação:

```bash
docker-compose exec app php artisan key:generate
```

7. Rode as migrations:

```bash
docker-compose exec app php artisan migrate
```

8. Acesse a API:

```
http://localhost:8000/api/municipalities/{uf}
```

## Uso da API

### Endpoint Principal

**GET** `/api/municipalities/{uf}`

Retorna os municípios de uma UF específica com paginação.

#### Parâmetros de Rota

| Parâmetro | Tipo   | Obrigatório | Descrição                      |
| ---------- | ------ | ------------ | -------------------------------- |
| `uf`     | string | Sim          | Sigla do estado (ex: RS, SP, RJ) |

#### Parâmetros de Query

| Parâmetro   | Tipo    | Obrigatório | Padrão | Descrição               |
| ------------ | ------- | ------------ | ------- | ------------------------- |
| `per_page` | integer | Não         | 15      | Itens por página (1-100) |
| `page`     | integer | Não         | 1       | Número da página        |

#### Exemplo de Requisição

```bash
curl -X GET "http://localhost:8000/api/municipalities/RS?per_page=20&page=1"
```

#### Exemplo de Resposta (200 OK)

```json
{
  "data": [
    {
      "name": "Porto Alegre",
      "ibge_code": "4314902"
    },
    {
      "name": "Caxias do Sul",
      "ibge_code": "4305108"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 497,
    "last_page": 25
  },
  "links": {
    "first": "http://localhost:8000/api/municipalities/RS?page=1",
    "last": "http://localhost:8000/api/municipalities/RS?page=25",
    "prev": null,
    "next": "http://localhost:8000/api/municipalities/RS?page=2"
  }
}
```

#### Exemplo de Erro (422 Unprocessable Entity)

```json
{
  "message": "UF inválida: XX. Por favor, informe uma sigla de estado válida."
}
```

#### Exemplo de Erro de Validação (422)

```json
{
  "message": "O campo per_page deve ser um número inteiro.",
  "errors": {
    "per_page": [
      "O campo per_page deve ser um número inteiro."
    ]
  }
}
```

### UFs Válidas

AC, AL, AP, AM, BA, CE, DF, ES, GO, MA, MT, MS, MG, PA, PB, PR, PE, PI, RJ, RN, RS, RO, RR, SC, SP, SE, TO

## Testes

### Rodar todos os testes

```bash
docker-compose exec app php artisan test
```

### Rodar testes em paralelo

```bash
docker-compose exec app php artisan test --parallel
```

### Rodar testes com cobertura

```bash
docker-compose exec app php artisan test --coverage
```

### Suíte de Testes

- **17 testes** cobrindo:
  - Providers (BrasilAPI e IBGE)
  - Service Layer (cache, validação)
  - Endpoints (paginação, validação, erros)
  - Integração completa

## Decisões Arquiteturais

### 1. Strategy Pattern para Providers

**Por quê?** Permite trocar facilmente entre BrasilAPI e IBGE sem alterar código, apenas configurando `.env`.

**Benefício:** Flexibilidade, testabilidade e extensibilidade (fácil adicionar novos providers).

### 2. Cache com Redis

**Por quê?** Dados de municípios são estáveis e raramente mudam.

**Estratégia:**

- Cache key: `municipalities:{provider}:{uf}`
- TTL configurável (padrão: 1 hora)
- Reduz latência e chamadas externas

### 3. Paginação In-Memory

**Por quê?** APIs externas retornam dados completos, não paginados.

**Implementação:** `LengthAwarePaginator` do Laravel para consistência nas respostas.

### 4. Validação de UF no Service

**Por quê?** Evita chamadas desnecessárias a APIs externas com dados inválidos.

**Benefício:** Resposta mais rápida para erros, melhor UX.

### 5. Exception Handling Customizado

**Por quê?** Mensagens em PT-BR e respostas JSON padronizadas.

**Implementação:**

- `InvalidUfException` - Retorna 422
- `ProviderException` - Retorna 500 (mensagem genérica para usuário, log detalhado)

### 6. Retry Logic

**Por quê?** APIs externas podem falhar temporariamente.

**Implementação:** 3 tentativas com backoff de 100ms usando `Http::retry()` do Laravel.

### 7. Timeout de 10 segundos

**Por quê?** Evitar requests travados indefinidamente.

## 📈 Escalabilidade

### Preparado para Crescimento

#### 1. Adicionar Novos Endpoints

```php
// routes/api.php
Route::get('/states', [StateController::class, 'index']);
Route::get('/cities/{cityId}', [CityController::class, 'show']);
```

#### 2. Adicionar Novos Providers

```php
// app/Providers/Municipality/ViaCepProvider.php
class ViaCepProvider implements MunicipalityProviderInterface
{
    public function getMunicipalities(string $uf): array
    {
        // Implementação
    }
}

// config/services.php - adicionar ao match
'viacep' => new ViaCepProvider(),
```

#### 3. Rate Limiting

```php
// app/Http/Kernel.php ou routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/municipalities/{uf}', [MunicipalityController::class, 'index']);
});
```

#### 4. API Versioning

```
/api/v1/municipalities/{uf}
/api/v2/municipalities/{uf}
```

#### 5. Cache Distribuído

Atual: Redis local
Futuro: Redis Cluster ou ElastiCache (AWS)

#### 6. Queue para Sincronização

```php
// Para pré-carregar cache de todos os estados
dispatch(new LoadMunicipalitiesJob('RS'));
```

#### 7. Database para Histórico

Adicionar model `Municipality` para armazenar histórico e permitir buscas complexasDocker

### Testes

```bash
# Rodar todos os testes
docker-compose exec app php artisan test

# Rodar teste específico
docker-compose exec app php artisan test --filter=BrasilApiProviderTest

# Ver cobertura
docker-compose exec app php artisan test --coverage-html=coverage
```

### Code Quality

```bash
# Verificar code style
docker-compose exec app ./vendor/bin/pint --test

# Corrigir code style automaticamente
docker-compose exec app ./vendor/bin/pint
```

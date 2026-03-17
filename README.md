# API de Gestao Hoteleira

Implementacao do desafio tecnico da Focomultimidia usando Laravel 10, Service Layer e testes com PHPUnit.

## Objetivo

Construir uma API capaz de:

- importar os arquivos XML em `database/`
- gerenciar quartos via CRUD
- criar e consultar reservas
- bloquear reservas com conflito de periodo para o mesmo quarto

## Arquitetura

O projeto foi organizado para manter regras de negocio fora dos controllers.

- `app/Http/Controllers/Api`: camada HTTP, validacao de entrada e resposta
- `app/Http/Requests`: validacoes das rotas
- `app/Services/XmlImportService`: importacao transacional dos XMLs
- `app/Services/AvailabilityService`: regra de disponibilidade
- `app/Services/ReservationService`: criacao de reservas e orquestracao do dominio
- `app/Models`: entidades e relacionamentos
- `database/migrations`: schema relacional para hoteis, quartos, tarifas e reservas

## Modelagem

Entidades principais:

- `Hotel`
- `Room`
- `Rate`
- `Reservation`
- `ReservationGuest`
- `ReservationPrice`

Os IDs vindos dos XMLs foram preservados como chave primaria para manter rastreabilidade entre arquivo de origem e persistencia.

## Regra de disponibilidade

Uma reserva entra em conflito quando existe outra reserva do mesmo quarto com intersecao de periodo:

```text
existing.arrival_date < new.departure_date
AND
existing.departure_date > new.arrival_date
```

Isso trata corretamente o intervalo como `[check-in, check-out)`, permitindo reservas adjacentes sem conflito.

## Endpoints

### Importacao

`POST /api/imports/xml`

Importa todos os XMLs por padrao. Tambem aceita importacao parcial:

```json
{
  "datasets": ["hotels", "rooms", "rates", "reservations"]
}
```

### Quartos

- `GET /api/rooms`
- `POST /api/rooms`
- `GET /api/rooms/{room}`
- `PUT /api/rooms/{room}`
- `PATCH /api/rooms/{room}`
- `DELETE /api/rooms/{room}`

Exemplo de criacao:

```json
{
  "id": 137598900,
  "hotel_id": 1375988,
  "name": "Premium Suite",
  "inventory_count": 3
}
```

### Reservas

- `GET /api/reservations`
- `POST /api/reservations`
- `GET /api/reservations/{reservation}`

Exemplo de criacao:

```json
{
  "hotel_id": 1375988,
  "room_id": 137598802,
  "customer": {
    "first_name": "Luis",
    "last_name": "Gustavo"
  },
  "booked_at_date": "2026-03-16",
  "booked_at_time": "10:00:00",
  "arrival_date": "2026-04-20",
  "departure_date": "2026-04-22",
  "currency_code": "BRL",
  "meal_plan": "Breakfast included.",
  "total_price": 300.00,
  "guests": [
    {
      "type": "adult",
      "count": 2
    }
  ],
  "prices": [
    {
      "rate_id": 5333849,
      "reference_date": "2026-04-20",
      "amount": 150.00
    },
    {
      "rate_id": 5333849,
      "reference_date": "2026-04-21",
      "amount": 150.00
    }
  ]
}
```

Em caso de conflito de periodo, a API responde `422 Unprocessable Entity`.

## Como executar

### Requisitos

- PHP 8.1+
- Composer
- extensao `pdo_sqlite` habilitada no PHP

### Passos

1. Instale as dependencias:

```bash
composer install
```

2. Configure o ambiente:

```bash
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
```

3. Execute as migrations:

```bash
php artisan migrate
```

4. Suba a API:

```bash
php artisan serve
```

5. Importe os XMLs:

```bash
curl -X POST http://127.0.0.1:8000/api/imports/xml \
  -H "Content-Type: application/json" \
  -d '{}'
```

## Testes

Cobertura implementada:

- teste unitario da regra de sobreposicao
- teste de integracao da importacao XML
- teste de integracao para criar reserva
- teste de integracao para bloquear reserva com conflito

Execucao:

```bash
php artisan test
```

## Observacoes

- O campo `inventory_count` foi importado e persistido, mas a regra do desafio foi implementada literalmente: qualquer reserva conflitante para o mesmo quarto bloqueia uma nova reserva no periodo.
- O ambiente desta maquina nao possui `pdo_sqlite` habilitado no PHP neste momento. O projeto esta configurado para SQLite, mas migrations e testes de integracao dependem dessa extensao estar instalada.

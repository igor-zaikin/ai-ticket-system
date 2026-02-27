# Helpdesk AI Ticket API

A Laravel 11 API prototype where users submit support tickets and an AI agent enriches them with:

- category
- sentiment
- urgency
- suggested reply

AI enrichment is processed asynchronously via a queue job.

## Setup

```
cp .env.example .env
```

Fill in your OpenAI key in .env: 

```
OPENAI_API_KEY=your_openai_api_key
```

Setup env:

```
docker compose up -d --build
```

```
docker compose exec app composer install
```

```
docker compose exec app php artisan key:generate
```

```
docker compose exec app php artisan migrate
```

```
docker compose exec app php artisan optimize:clear
```

##Run Tests

```
docker compose exec app php artisan test --filter=TicketApiTest
```

##API Base URL

```
http://localhost:8080
```

##Endpoints

###Create Ticket

```
POST /api/tickets
```

Example body:

```
{
  "title": "Payment failed",
  "description": "My card was charged twice and I still do not see the invoice."
}
```

###Get Ticket

```
GET /api/tickets/{id}
```

##Prompt Strategy

The AI uses a strict system prompt that defines it as a helpful customer support agent and limits the output to a fixed task: classify the ticket, detect sentiment, assign urgency and draft a reply.

To keep the response predictable, the request uses a JSON schema with required fields and allowed values. This reduces free-form output, prevents markdown/code fences and makes the result easy to validate before saving it.

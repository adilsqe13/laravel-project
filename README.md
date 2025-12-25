# BeyondChats – Full Stack Assignment - Phase 1

This repository contains the complete solution for the BeyondChats assignment, implemented as a **single monolithic project** covering **Phase 1, Phase 2, and Phase 3**.
The project demonstrates an end-to-end flow where blog articles are **scraped**, **stored**, **processed**, and **displayed** using Laravel, Node.js, and React.


## Laravel Backend

### What this phase does
- Automatically detects the **last page** of BeyondChats blogs
- Scrapes the **5 oldest articles**
- Stores them in a MySQL database
- Exposes **CRUD APIs** with pagination


## ⚙️ Environment Setup

The application uses a hosted MySQL database provided by Railway.

```env
DB_CONNECTION=mysql
DB_HOST=shuttle.proxy.rlwy.net
DB_PORT=38376
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=********
```

## Database Design

### Articles Table

| Column Name | Type              | Description     |
| ----------- | ----------------- | --------------- |
| id          | bigint            | Primary key     |
| title       | string            | Article title   |
| content     | text              | Article content |
| author      | string (nullable) | Author name     |
| url         | string            | Source URL      |
| created_at  | timestamp         | Auto-managed    |
| updated_at  | timestamp         | Auto-managed    |


## Key Highlights

* Hosted database (production-like setup)
* Clean REST API design
* Laravel best practices
* Migration-driven schema
* JSON-based communication


### How to run Phase 1 locally

```bash
cd beyondchats-phase1
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan scrape:beyondchats
php artisan serve
```

### Fetch Laravel Api
- Locally     : http://127.0.0.1:8000/api/articles/
- Hosted Url  : https://beyondchats.up.railway.app/api/articles

### Phase-1 hosted at "Railway"


## Author

**Md Adil Alam**

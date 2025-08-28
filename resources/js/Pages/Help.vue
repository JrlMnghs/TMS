<template>
  <div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Translation Management Service - Help</h1>

    <section class="mb-8">
      <h2 class="text-xl font-semibold mb-2">Overview</h2>
      <p class="text-gray-700">
        This project provides a high-performance API for managing translations with versioned endpoints
        under <code>/api/v1</code>. It includes authentication, search, CRUD, export (with streaming), and
        comprehensive tests.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-xl font-semibold mb-2">Getting Started</h2>
      <ol class="list-decimal list-inside space-y-1 text-gray-700">
        <li>Clone the repository and start Docker: <code>docker compose up -d</code></li>
        <li>Install dependencies: <code>docker exec -it {container} composer install</code></li>
        <li>Generate key: <code>docker exec -it {container} php artisan key:generate</code></li>
        <li>Migrate DB: <code>docker exec -it {container} php artisan migrate:fresh</code></li>
        <li>Seed base data: <code>docker exec -it {container} php artisan db:seed</code></li>
      </ol>
    </section>

    <section class="mb-8">
      <h2 class="text-xl font-semibold mb-2">Large Dataset (Optional)</h2>
      <p class="text-gray-700 mb-2">Seed 200k translations for performance testing:</p>
      <pre class="bg-gray-100 rounded p-3 overflow-x-auto"><code>docker exec -it {container} php artisan migrate:fresh --seed --seeder=Database\\Seeders\\MemoryEfficientSeeder</code></pre>
    </section>

    <section class="mb-8">
      <h2 class="text-xl font-semibold mb-2">Authentication</h2>
      <p class="text-gray-700 mb-2">Default user after seeding:</p>
      <ul class="list-disc list-inside text-gray-700">
        <li>Email: <code>test@example.com</code></li>
        <li>Password: <code>********</code></li>
      </ul>
    </section>

    <section class="mb-8">
      <h2 class="text-xl font-semibold mb-2">API Usage</h2>
      <ul class="list-disc list-inside space-y-1 text-gray-700">
        <li>Base URL: <code>http://localhost:8080</code></li>
        <li>Version prefix: <code>/api/v1</code></li>
        <li>Login: <code>POST /api/v1/login</code></li>
        <li>Translations CRUD: <code>/api/v1/translations</code></li>
        <li>Export: <code>GET /api/v1/export/{locale}</code>, <code>/api/v1/export/{locale}/stream</code></li>
      </ul>
      <p class="text-gray-700 mt-2">See <code>openapi.yaml</code> and Postman collection for details.</p>
    </section>

    <section class="mb-8">
      <h2 class="text-xl font-semibold mb-2">Rate Limiting</h2>
      <p class="text-gray-700 mb-2">All API endpoints are rate-limited for security:</p>
      <ul class="list-disc list-inside space-y-1 text-gray-700">
        <li><strong>Login:</strong> 5 attempts per minute</li>
        <li><strong>Export:</strong> 30 requests per hour</li>
        <li><strong>Translations CRUD:</strong> 100 requests per hour</li>
        <li><strong>Users:</strong> 50 requests per hour</li>
        <li><strong>Default:</strong> 60 requests per hour</li>
      </ul>
      <p class="text-gray-700 mt-2">Rate limit headers are included in responses: <code>X-RateLimit-Limit</code>, <code>X-RateLimit-Remaining</code></p>
    </section>

    <section class="mb-8">
      <h2 class="text-xl font-semibold mb-2">Best Practices</h2>
      <ul class="list-disc list-inside space-y-1 text-gray-700">
        <li>Follow PSR-12 and SOLID in contributions</li>
        <li>Use repository pattern for data access</li>
        <li>Avoid N+1 queries; leverage eager loading or raw SQL when needed</li>
        <li>Prefer streaming for large exports</li>
        <li>Run tests before pushing changes: <code>php artisan test</code></li>
      </ul>
    </section>

    <section>
      <h2 class="text-xl font-semibold mb-2">Useful Commands</h2>
      <pre class="bg-gray-100 rounded p-3 overflow-x-auto"><code># Shell into container
docker exec -it {container} bash

# Refresh DB and seed base
php artisan migrate:fresh --seed

# Seed 200k dataset
php artisan migrate:fresh --seed --seeder=Database\Seeders\MemoryEfficientSeeder

# Run tests
php artisan test</code></pre>
    </section>
  </div>
</template>

<script setup>
// No additional logic required; static help content
</script>

<style scoped>
</style>



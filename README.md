Lara-Ollama-RAG 🧠🐘
![alt text](https://img.shields.io/badge/Laravel-11.x-red.svg)

![alt text](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)

![alt text](https://img.shields.io/badge/license-MIT-green.svg)

![alt text](https://img.shields.io/badge/AI-Ollama-orange.svg)
Lara-Ollama-RAG is a high-performance, locally-hosted Retrieval-Augmented Generation (RAG) system built on Laravel 11. It enables developers to transform unstructured document directories into a searchable, intelligent knowledge base without any data leaving their infrastructure.
🏗️ System Architecture
The project is designed with scalability in mind, utilizing a decoupled architecture that separates heavy computational tasks (AI embeddings) from the user-facing web interface.
1. The Ingestion Pipeline (Asynchronous)
Artisan CLI Scanner: Recursively scans directories and registers documents.
Smart Paragraph Chunker: Implements paragraph-aware splitting to preserve semantic context (avoiding mid-sentence cuts).
Redis Queue: Dispatches background jobs using Laravel Horizon/Queues to prevent web-thread blocking during large-scale ingestion.
Vectorization: Communicates with the nomic-embed-text model via Ollama to generate 768-dimension vectors.
2. The Storage Layer (pgvector)
PostgreSQL 17: Serves as the primary relational store.
Vector Extension: Uses pgvector for mathematical similarity lookups.
HNSW Indexing: Implements Hierarchical Navigable Small World (HNSW) indexing on the embedding column for sub-second retrieval performance across millions of records.
3. The Retrieval Engine (RAG Flow)
Query Embedding: The user's question is vectorized in real-time.
Similarity Search: Performs a Cosine Distance comparison in the database to find the top 
K
K
 most relevant document chunks.
Prompt Augmentation: The retrieved context is injected into a specialized system prompt.
Generation: Llama 3.2 generates an answer based strictly on the provided context.
🌊 Real-Time Streaming UI
The frontend is built for a premium UX, mimicking modern AI platforms:
Server-Sent Events (SSE): The Laravel backend pipes chunks from the Ollama API directly to the browser.
AlpineJS Reactivity: A lightweight frontend logic layer captures the stream and updates the UI character-by-character.
Auto-Scrolling Messaging: A smooth, reactive thread that handles dynamic content length.
🛠️ Technical Stack
Backend: Laravel 11 (PHP 8.2+)
AI Models:
Chat: llama3.2:3b-instruct-q5_K_M
Embeddings: nomic-embed-text
Database: PostgreSQL + pgvector
Queue: Redis (via predis)
Frontend: Tailwind CSS + AlpineJS
🚀 Quick Start
1. Infrastructure Setup
Run the vector-enabled database via Docker:
code
Bash
docker run --name lara-recall-db -e POSTGRES_PASSWORD=root -p 5433:5432 -d pgvector/pgvector:pg17
2. Environment Configuration
Set up your .env to connect to the local AI and Database:
code
Env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=root

QUEUE_CONNECTION=redis
REDIS_CLIENT=predis

OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL_CHAT=llama3.2:3b-instruct-q5_K_M
OLLAMA_MODEL_EMBED=nomic-embed-text
3. Usage
A. Ingest knowledge:
code
Bash
php artisan recall:ingest /path/to/documents
B. Start the scalable worker:
code
Bash
php artisan queue:work
C. Launch and Chat:
code
Bash
php artisan serve
📂 Key Codebase Highlights
app/Services/RecallEngine.php: The "Brain" of the application handling vector math and AI prompts.
app/Jobs/GenerateEmbeddingJob.php: Scalable background processing for AI vectors.
app/Http/Controllers/ChatController.php: Handles the SSE streaming logic.
app/Console/Commands/IngestFiles.php: The recursive document scanner.
📄 License
Built with ❤️ for the Laravel and AI Community.

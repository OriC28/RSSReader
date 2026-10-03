# RSSReader

## Description
RSSReader is an intelligent RSS news aggregator and newsletter generator designed for a single administrator. It automatically fetches articles from subscribed feeds, processes them using a Large Language Model (LLM) to generate concise summaries and assign categories, and compiles them into a weekly curated email newsletter.

## Features
- Manage multiple RSS feeds from a single admin panel, including feed registration, health checks, and synchronization tracking.
- Automatically fetch and store the latest articles from subscribed sources on a scheduled basis.
- Process each article with an AI-powered pipeline to generate concise summaries and classify content by topic.
- Organize content into structured categories such as technology, science, business, culture, sports, and other topics.
- Keep a clear publication workflow with article states like pending, processed, skipped, and failed.
- Generate curated weekly newsletters from the best processed articles and send them by email to the administrator.
- Monitor feed performance, article status, and newsletter activity through a Filament-based dashboard.
- Use queued background jobs to keep ingestion, AI processing, and newsletter generation reliable and scalable.

## Tech Stack
- **Backend:** Laravel 13, PHP 8.3+
- **Admin Panel:** Filament PHP (^5.0), Livewire 3
- **Database:** PostgreSQL 15 (via Laravel Sail)
- **Queue & Caching:** Database
- **AI Integration:** Google Gemini API
- **Email:** SMTP (Mailtrap for development)
- **Testing:** Pest
- **Code Formatting:** Laravel Pint

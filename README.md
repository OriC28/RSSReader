# RSSReader

## Description
RSSReader is an intelligent RSS news aggregator and newsletter generator designed for a single administrator. It automatically fetches articles from subscribed feeds, processes them using a Large Language Model (LLM) to generate concise summaries and assign categories, and compiles them into a weekly curated email newsletter.

## Tech Stack
- **Backend:** Laravel 13, PHP 8.3+
- **Admin Panel:** Filament PHP (^5.0), Livewire 3
- **Database:** PostgreSQL 15 (via Laravel Sail)
- **Queue & Caching:** Database/Redis (development)
- **AI Integration:** Google Gemini API
- **Email:** SMTP (Mailtrap for development)
- **Testing:** Pest
- **Code Formatting:** Laravel Pint

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | OpenAI-compatible API (OpenAI, Azure OpenAI, LiteLLM, etc.)
    | Zonder OPENAI_API_KEY blijft alleen de regel-gebaseerde analyse actief.
    */
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => rtrim(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/'),
        /** Ruim genoeg voor uitgebreide JSON (aanbevelingen, observaties, narrative). */
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 4096),
    ],

    /*
    | Google Gemini via de OpenAI-compatibele endpoint. Als GEMINI_API_KEY gezet is,
    | heeft Gemini voorrang op OpenRouter en OpenAI. Zet de key alleen in de .env op
    | de server, nooit in de code of in git. max_tokens wordt gedeeld met de openai-config.
    */
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
        'base_url' => rtrim(env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai'), '/'),
    ],

    /*
    | OpenRouter (https://openrouter.ai). Als OPENROUTER_API_KEY gezet is, gaat de
    | LLM-analyse via OpenRouter in plaats van de OpenAI-instellingen hierboven.
    | max_tokens wordt gedeeld met de openai-config.
    */
    'openrouter' => [
        'key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'openai/gpt-4o-mini'),
        'base_url' => rtrim(env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'), '/'),
        /** Optionele attributie-headers (HTTP-Referer / X-Title) voor het OpenRouter-dashboard. */
        'site_url' => env('OPENROUTER_SITE_URL', env('APP_URL')),
        'app_name' => env('OPENROUTER_APP_NAME', env('APP_NAME')),
    ],

];

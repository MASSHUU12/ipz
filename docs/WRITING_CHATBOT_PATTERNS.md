# Writing chatbot patterns

This document explains how to create new chatbot patterns
and how to write callback handlers. It covers the JSON schema, fields,
templating, callbacks, modules, examples, and testing tips.

---

## Overview

A pattern defines:
- A regular expression to match user messages
- One or more responses (templated strings) or a callback that returns a response
- Metadata (severity, priority, enabled, access level, stop_processing)

---

## Pattern JSON / DB schema

Common fields you will set in the patterns table (the actual table may have more columns):

- `description` (string) — human-friendly description.
- `group` (string) — logical group (e.g. "User", "Weather").
- `pattern` (string) — a PHP-compatible regex string, including delimiters and flags.
Example: `"/\\b(?:hi|hello)\\b/i"`.
- `responses` (json array|null) — array of templated strings.
Mutually exclusive with `callback` (pattern can have a callback, or responses).
- `callback` (string|null) — PHP callable reference as `"App\\Http\\Callbacks\\SomeClass::method"`.
- `severity` (string) — used for ordering (e.g. `critical`, `high`, `medium`, `low`).
- `priority` (int) — secondary ordering.
- `enabled` (boolean) — whether pattern is active.
- `stop_processing` (boolean) — if true, matching stops after this pattern matches.
- `access_level` (string) — `public`, `authenticated`, or `super_admin`.

---

## Regex tips

- Store full regex including delimiters and flags (e.g. `/pattern/i`).
The controller calls `preg_match($pattern->pattern, $question, $matches)`.
- Use word boundaries `\b` or anchors `^`/`$` where appropriate to avoid accidental matches.
- Keep patterns focused; overly broad regexes can shadow more specific ones.
- Capture groups: `$matches[1]` is the first capture group.
The controller supports substituting `%1` in responses with `matches[1]`
(simple legacy support). Use `%1` only for the first capture; for richer logic use callbacks.
- Example: `"/^(show|list|view)\\s+(my\\s+)?(preferences|settings)/i"`

---

## Responses & templating

Responses are plain strings with support for:
- `%1` — replaced with first regex capture (if present).
- Placeholder hydration using curly braces and dot notation:
`{user.name}`, `{bot.name}`, `{user_date}`, `{user_time}`, `{user_timezone}`.
- Fallback syntax: `{user.nickname ?? 'friend'}` or `{user.nickname ?? \`Guest\`}`
(supports backticks, single or double quotes).

Available variables supplied to the hydrator:
- `user` — the authenticated User model instance or `null` if anonymous.
- `bot` — object/array; at minimum `{ "name": "Marian" }`.
- `user_timezone` — timezone name from request or app config.
- `user_date` — date string (YYYY-MM-DD) in user's timezone.
- `user_time` — time string (HH:MM:SS) in user's timezone.

Hydrator behavior:
- Pattern: `/\{([a-zA-Z0-9_.]+)(?:\s*\?\?\s*(?:`...`|'...'|"..." ))?\}/`
- Dot notation resolves nested properties on arrays/objects.
- If a variable is null/absent and a fallback is provided, fallback is used.
- If the variable is missing and no fallback is present, the placeholder is left
as-is (so missing values are visible during testing).
- `%1` is replaced before hydration, so a response containing `%1` may also
have `{user.name}` placeholders.

Examples:
```json
{
  "pattern": "/\\b(?:hi|hello|hey)\\b/i",
  "responses": [
    "Hello {user.name ?? 'Guest'}! The time is {user_time}.",
    "Hi {user.name ?? 'friend'}, how can I help?"
  ],
  "access_level": "public"
}
```

## Payloads

Callbacks can return an optional `payload` key in addition to the plain `answer` string.
Payloads are structured instructions for the client to render richer UI or to provide action metadata.
The plain `answer` should always be provided as a safe textual fallback when a client cannot
or will not render the payload.

General rules:
- The callback return shape should be an array (or JSON) like:
  - `{ 'answer': 'Text shown to the user', 'payload': { ... } }`
- The client MUST validate any URLs in payloads (e.g., whitelist domains or mark as external).
- Keep payloads small and predictable - prefer a small set of well-documented `type` values.
- If the client cannot handle the payload, it should display the `answer` only.

Common payload types used by modules:

1. `image_url`
- Purpose: ask the client to display an image alongside the answer.
- Shape:
  - type: "image_url"
  - url: string (required)
  - alt: string (optional, for accessibility)
- Example:
```json
{
  "type": "image_url",
  "url": "https://images.example/cute-cat.jpg",
  "alt": "A cute cat"
}
```

2. `navigation_link`
- Purpose: ask the client to render a button or link that navigates to a page (internal or external).
- Shape:
  - type: "navigation_link"
  - url: string (required)
  - text: string (optional) — label for the link/button
- Example:
```json
{
  "type": "navigation_link",
  "url": "https://example.com/profile",
  "text": "Go to your profile"
}
```

3. buttons / quick_replies
- Purpose: present a list of short follow-up actions.
- Shape (suggested):
  - type: "quick_replies"
  - items: `[ { "title": "Yes", "payload": "/yes" }, { "title": "No", "payload": "/no" } ]`
- Example:
```json
{
  "type": "quick_replies",
  "items": [
    { "title": "Show me another fact", "payload": "/fact" },
    { "title": "No thanks", "payload": "/cancel" }
  ]
}
```

---

---

## Callbacks

Use callbacks for more complex logic or to query related models (preferences,
favorites, external APIs).

Callback format:
- Stored as string: `"App\\Http\\Callbacks\\UserCallbacks::listPreferences"`
- The controller resolves `"Class::method"` and calls it as
`call_user_func($callback, $matches, $request)`
- Signature (recommended):
  - public static function method(array $matches, \App\Http\Requests\MessageChatbotRequest $request): string

Example callback (file: `app/Http/Callbacks/UserCallbacks.php`):
```php
<?php
namespace App\Http\Callbacks;

use App\Http\Requests\MessageChatbotRequest;

class UserCallbacks
{
    public static function listFavoriteLocations(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();
        if (!$user) {
            return "You must be logged in to view favorite locations.";
        }
        $locations = $user->favoriteLocations()->get();
        if ($locations->isEmpty()) {
            return "You have no favorite locations, {user.name ?? 'friend'}.";
        }
        $lines = ["Your favorite locations, {user.name}:"];
        foreach ($locations as $i => $loc) {
            $lines[] = sprintf("%d) %s — lat: %s, lng: %s", $i+1, $loc->city ?? '—', $loc->lat ?? '—', $loc->lng ?? '—');
        }
        return implode("\n", $lines);
    }
}
```

Security & safety:
- Prefer dedicated callback classes for chatbot logic — do not allow arbitrary user-supplied callables.
- Ensure callbacks are non-destructive unless explicitly intended (confirm before deleting user data).
- Keep heavy operations (external API calls) async or cached to avoid long request times.

---

## Modules

Modules allow grouping related patterns and callbacks together as PHP classes
under `App/Chatbot/Modules`. They make it easier to add bundles of patterns
without touching the DB, or to provide programmatic patterns.

Key pieces:
- ModuleInterface — implements `getPatterns(): array` and `getMTime(): int`.
- Module classes live in `app/Chatbot/Modules`.
- ModuleLoader scans `app/Chatbot/Modules`, requires PHP files, finds classes
implementing `ModuleInterface`, calls `getPatterns()` and collects patterns.
- `getMTime()` should return the module file modification time
(used for caching / invalidation).

Module pattern schema is the same as DB patterns.
Example of a module-provided pattern:

```php
// app/Chatbot/Modules/CatModule.php
<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class CatModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => "/\\b(show me a cat|ascii cat|make me an ascii cat)\\b/i",
                "responses" => [],
                "callback" => "asciiCatCallback", // ModuleLoader will normalize this to [self::class, 'asciiCatCallback']
                "severity" => "low",
                "priority" => 20,
                "enabled" => true,
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function asciiCatCallback(array $matches, MessageChatbotRequest $request): string
    {
        $cat = <<<EOF
Here you go! 😺

 /\_/\
( o.o )
 > ^ <
EOF;
        return $cat;
    }
}
```

---

## Best practices

- Keep each pattern narrow and specific; avoid overly broad regexes that will match too much.
- Use severity + priority to order critical or admin-level patterns above casual ones.
- For user-visible data, always check authentication in the callback.
- Prefer callback for multi-row DB data (preferences, favorites) to keep response formatting flexible.
- Use fallback syntax for public patterns so one pattern can serve both guests and authenticated users.
- Return readable, concise responses. If you return multiline text, ensure client can handle line breaks.

---

## Examples

1) Greeting (public)
```json
{
  "pattern": "/\\b(?:hi|hello|hey|hiya|greetings|yo)\\b/i",
  "responses": [
    "Hello {user.name ?? 'Guest'}! How can I help you today?",
    "Hi {user.name ?? 'friend'}! What can I do for you?"
  ],
  "access_level": "public",
  "severity": "low",
  "priority": 20,
  "enabled": true
}
```

2) List preferences (callback, authenticated)
```json
{
  "description": "Lists the user's notification preferences.",
  "pattern": "/^(show|list|view|what are)\\s+(my\\s+)?(preferences|settings)/i",
  "callback": "App\\\\Http\\\\Callbacks\\\\UserCallbacks::listPreferences",
  "access_level": "authenticated",
  "severity": "medium",
  "priority": 50,
  "enabled": true,
  "stop_processing": true
}
```

---

## Troubleshooting

- Pattern not matching:
  - Ensure regex delimiters/flags are present.
  - Use `^`/`$` or `\b` as needed; test with `preg_match` locally.
- Variables not substituted:
  - `{user.name}` resolves only if `user` object is present and has `name`.
  - Use fallback: `{user.name ?? 'Guest'}`.
  - `%1` is replaced only when a capture group exists; for complex captures use a callback.

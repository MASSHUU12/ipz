<?php

namespace App\Observers;

use App\Models\Pattern;
use Illuminate\Support\Facades\Cache;

class PatternObserver
{
    /**
     * Handle the Pattern "created" event.
     */
    public function created(Pattern $pattern): void
    {
        Cache::forget('chatbot_patterns');
    }

    /**
     * Handle the Pattern "updated" event.
     */
    public function updated(Pattern $pattern): void
    {
        Cache::forget('chatbot_patterns');
    }

    /**
     * Handle the Pattern "deleted" event.
     */
    public function deleted(Pattern $pattern): void
    {
        Cache::forget('chatbot_patterns');
    }

    /**
     * Handle the Pattern "restored" event.
     */
    public function restored(Pattern $pattern): void
    {
        Cache::forget('chatbot_patterns');
    }

    /**
     * Handle the Pattern "force deleted" event.
     */
    public function forceDeleted(Pattern $pattern): void
    {
        Cache::forget('chatbot_patterns');
    }
}

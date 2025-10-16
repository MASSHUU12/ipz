<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageChatbotRequest;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;

if (!function_exists("mb_strrev")) {
    function mb_strrev(string $str): string
    {
        $len = mb_strlen($str);
        $out = "";
        while ($len-- > 0) {
            $out .= mb_substr($str, $len, 1);
        }
        return $out;
    }
}

class ChatbotController extends Controller
{
    private const BOT_NAME = "Marian";

    // TODO: Move to the JSON
    /**
     * Patterns may be:
     * - an array of response strings (behavior like before), OR
     * - an array with a 'callback' key containing a callable ($matches, Request) => string
     */
    private static $PATTERNS = [
        /**** Most specific and safety-critical ****/

        // Empty or whitespace-only message
        "/^\s*$/" => [
            "I didn't get any message — could you type your question or issue?",
            "It looks like your message was empty. Please tell me how I can help.",
        ],

        // Threats or aggressive actions (safety-focused)
        "/\b(?:kill|hurt|sue|report you|I'll (?:kill|hurt|sue))\b/i" => [
            "I can't assist with threats or harmful behavior. If you feel unsafe, please contact local authorities. For service issues, I can escalate your concern to a human agent.",
            "Threats are serious. I am unable to help with that. If this is about a product or service issue, I can escalate it to our support team for review.",
        ],

        // Explicit profanity (generic)
        "/\b(?:fuck|shit|bastard|asshole|damn)\b/i" => [
            "I want to help resolve this. Please avoid abusive language so I can assist you more effectively.",
            "I hear your frustration. Let's keep this respectful and I'll work with you to solve the problem. What specifically should I address first?",
        ],

        // Handle rude or abusive language (de-escalation)
        // Match common insults or direct abusive phrases
        "/\b(?:you(?:'re| are)?\s+(?:stupid|useless|an?\s+idiot|worthless))\b/i" => [
            "I'm sorry you're upset — I'm here to help. Could you tell me exactly what's wrong so I can try to fix it?",
            "I understand this is frustrating. I want to help, but I can't do that while being insulted. Please describe the issue and I'll do my best.",
        ],

        // Tell the bot to shut up or similar
        "/\b(?:shut up|stfu|be quiet)\b/i" => [
            "I'm here to assist whenever you're ready. If you'd prefer to continue later, that's fine.",
            "I want to help, but I can't if you don't want me to speak. If you'd like help, please describe the issue.",
        ],

        // Shouting / ALL CAPS detection (simple heuristic)
        "/[A-Z]{4,}/" => [
            "I can see you're upset. Writing in all caps can come across as shouting — please tell me what's wrong and I'll help.",
            "I want to help you calmly and effectively. Please describe the issue without using all caps so I can assist.",
        ],

        // Demands / urgency with aggressive tone
        "/\b(?:fix this now|do it now|right now|now!)\b/i" => [
            "I understand the urgency. Please share the details and I'll prioritize helping you.",
            "I want to resolve this quickly; please tell me what happened and any relevant details so I can assist right away.",
        ],

        // Intensity: many punctuation marks (urgent / frustrated)
        "/[!?]{2,}/" => [
            "I can tell this is urgent. Please tell me the core issue and any order/account details so I can help quickly.",
            "I understand the urgency — give me the key details and I'll prioritize assisting you.",
        ],

        /**** **** *****/

        /**** High priority support issues *****/

        // Requests to speak with a manager / escalate
        "/\b(?:manager|supervisor|escalate|complaint)\b/i" => [
            "I can escalate this to a supervisor for you. Please provide a brief summary of the issue and your preferred contact method.",
            "I understand you'd like to escalate. I'll note this and pass it to a human agent — please tell me the core issue and any order or account details.",
        ],

        // Account / login / password issues
        "/\b(?:forgot(?:ten)? password|reset password|can't log in|cannot log in|login issue|login failed)\b/i" => [
            "If you forgot your password, you can reset it using the 'Forgot password' link. Would you like instructions?",
            "I can help with login problems — are you seeing an error message or unable to reach the login page?",
        ],

        // Technical errors / bugs / site doesn't work
        "/\b(?:error|bug|doesn't work|does not work|failed|exception|page not found|404)\b/i" => [
            "I'm sorry you're seeing an error. Can you describe what you were doing and paste any error message?",
            "Thanks for reporting this — please tell me the steps to reproduce and any error text so I can escalate to engineering.",
        ],

        // Requests for help / support
        "/\b(?:help|support|assist|assistance)\b/i" => [
            "Sure — what do you need help with?",
            "I'm here to help. Please tell me more about the issue.",
        ],

        /**** **** *****/

        /**** User-intent matches *****/

        // Introductions
        "/\bmy name is\s+(.+)\b/i" => [
            "Hello %1! How can I assist you today?",
            "Nice to meet you, %1! What can I help you with?",
        ],

        // Asking the bot's name / identity
        "/\b(?:what is your name|who are you|what are you)\b/i" => [
            "I'm ChatBot, your assistant.",
            "I'm a friendly chatbot here to help. You can call me ChatBot.",
        ],

        // How the bot is doing
        "/\bhow are you\b/i" => [
            "I'm a bot, but I'm doing great! How about you?",
            "Doing well - ready to help you. How can I assist?",
        ],

        // Greetings
        "/\b(?:hi|hello|hey|hiya|greetings|yo)\b/i" => [
            "Hello! How can I help you today?",
            "Hi there! How may I assist you?",
            "Hey! What can I do for you?",
        ],

        // Thanks / appreciation
        "/\b(?:thanks|thank you|thx|thankyou)\b/i" => ["You're welcome!", "No problem — happy to help!", "Anytime!"],

        // Goodbyes
        "/\b(?:bye|goodbye|see you|see ya|talk to you later)\b/i" => [
            "Goodbye! Have a great day!",
            "See you later! If you need anything else, just ask.",
        ],

        /**** **** *****/

        /**** Conversational/ambiguous fallbacks *****/

        // Time / date request
        "/\b(?:what time is it|current time|time now|what's the time)\b/i" => [
            "callback" => [self::class, "timeCallback"],
        ],
        "/\b(?:what(?:'s| is) the date|what date is it|today's date)\b/i" => [
            "callback" => [self::class, "dateCallback"],
        ],

        // Small talk / friendly chat
        "/\b(?:how's the weather|what's up|how are you doing|what's going on)\b/i" => [
            "I'm doing fine — ready to help. What can I do for you today?",
            "All systems go! Tell me what you need and I'll do my best to assist.",
        ],

        // Very generic catch-all for conversational prompts
        "/\b(?:tell me more|explain|details|example|how do I|how to)\b/i" => [
            "Could you provide a little more detail so I can give a useful answer?",
            "Happy to explain — please tell me exactly what you want to know.",
        ],

        // Rickroll
        "/\b(rickroll|never gonna give you up)\b/i" => [
            "Never gonna give you up… 🎵",
            "You've been rickrolled! ♪ Never gonna let you down…",
        ],

        // Rock Paper Scissors: "I choose rock" or "play rock" or just "rps rock"
        "/\b(?:i choose|play|rps)?\s*(rock|paper|scissors)\b/i" => [
            "callback" => [self::class, "rpsCallback"],
        ],

        // ASCII art / small animal (cat)
        "/\b(show me a cat|ascii cat|make me an ascii cat)\b/i" => [
            "callback" => [self::class, "asciiCatCallback"],
        ],

        // Palindrome checker: "is 'racecar' a palindrome?" or is racecar a palindrome
        "/\bis\s+['\"]?(.+?)['\"]?\s+a\s+palindrome\??$/i" => [
            "callback" => [self::class, "palindromeCallback"],
        ],

        // Secret movie-quote completions
        "/\b(i'll be back|may the force be with you|you talking to me|here's looking at you)\b/i" => [
            "callback" => [self::class, "movieQuoteCallback"],
        ],

        // Fallback for questions containing a question mark
        "/\?\s*$/i" => [
            "That's a good question — can you give me a bit more detail?",
            "I can help with that. Could you clarify what you mean?",
        ],

        /**** **** *****/
    ];

    public function message(MessageChatbotRequest $request): JsonResponse
    {
        $question = $request["content"];
        $answer = "I'm sorry, my ability to respond is limited. Please ask your questions correctly.";

        foreach (self::$PATTERNS as $pattern => $entry) {
            if (preg_match($pattern, $question, $matches)) {
                if (is_array($entry) && isset($entry["callback"]) && is_callable($entry["callback"])) {
                    try {
                        $result = call_user_func($entry["callback"], $matches, $request);
                        if (is_string($result) && $result !== "") {
                            $answer = str_replace("ChatBot", self::BOT_NAME, $result);
                        }
                    } catch (Exception $e) {
                        $answer = "Sorry, I couldn't process that request right now.";
                        //$answer = $e->getMessage();
                    }
                } else {
                    $responses = is_array($entry) ? $entry : [$entry];
                    $response = $responses[array_rand($responses)];
                    if (strpos($response, "%1") !== false && isset($matches[1])) {
                        $response = str_replace("%1", $matches[1], $response);
                    }
                    $answer = str_replace("ChatBot", self::BOT_NAME, $response);
                }
                break;
            }
        }

        return response()->json([
            "question" => $question,
            "answer" => $answer,
        ]);
    }

    protected static function timeCallback(array $matches, $request): string
    {
        $tz = $request->input("timezone") ?? (config("app.timezone") ?? "UTC");

        try {
            $zone = new DateTimeZone($tz);
        } catch (Exception $e) {
            $zone = new DateTimeZone("UTC");
            $tz = "UTC";
        }

        $dt = new DateTime("now", $zone);
        return "The current time is " . $dt->format("H:i:s") . " (" . $tz . ").";
    }

    protected static function dateCallback(array $matches, $request): string
    {
        $tz = $request->input("timezone") ?? (config("app.timezone") ?? "UTC");
        try {
            $zone = new DateTimeZone($tz);
        } catch (Exception $e) {
            $zone = new DateTimeZone("UTC");
            $tz = "UTC";
        }

        $dt = new DateTime("now", $zone);
        return "Today's date is " . $dt->format("Y-m-d") . " (" . $tz . ").";
    }

    protected static function rpsCallback(array $matches, MessageChatbotRequest $request): string
    {
        $userMove = strtolower($matches[1] ?? "");

        $valid = ["rock", "paper", "scissors"];
        if (!in_array($userMove, $valid, true)) {
            return "I didn't catch your move. Say 'rock', 'paper' or 'scissors'.";
        }

        $botMove = $valid[array_rand($valid)];

        $result = "draw";
        if ($userMove === $botMove) {
            $result = "draw";
        } elseif (
            ($userMove === "rock" && $botMove === "scissors") ||
            ($userMove === "paper" && $botMove === "rock") ||
            ($userMove === "scissors" && $botMove === "paper")
        ) {
            $result = "user";
        } else {
            $result = "bot";
        }

        $emoji = [
            "rock" => "🪨",
            "paper" => "📄",
            "scissors" => "✂️",
        ];

        $outcomeText = match ($result) {
            "user" => "You win! 🎉",
            "bot" => "I win! 🤖",
            "draw" => "It's a draw. 🤝",
        };

        return "You played {$userMove} {$emoji[$userMove]} — I played {$botMove} {$emoji[$botMove]}. {$outcomeText}";
    }

    protected static function asciiCatCallback(array $matches, MessageChatbotRequest $request): string
    {
        $cat =
            <<<EOF
            Here you go! 😺\n
            EOF
            .
            " /\\_/\\\n" .
            "( o.o )\n" .
            " > ^ <";
        return $cat;
    }

    protected static function palindromeCallback(array $matches, MessageChatbotRequest $request): string
    {
        $raw = $matches[1] ?? "";
        $normalized = mb_strtolower(preg_replace("/[^\\p{L}\\p{N}]+/u", "", $raw));
        if ($normalized === "") {
            return "I couldn't find any letters or numbers in that phrase to check.";
        }

        $reversed = mb_strrev($normalized);
        $isPalindrome = $normalized === $reversed;

        return "'" . $raw . "' " . ($isPalindrome ? "is" : "is not") . " a palindrome " . ($isPalindrome ? "✅" : "❌");
    }

    protected static function movieQuoteCallback(array $matches, MessageChatbotRequest $request): string
    {
        $trigger = mb_strtolower($matches[1] ?? "");

        $map = [
            "i'll be back" => "Hasta la vista, baby. 😎",
            "may the force be with you" => "And also with you. ✨",
            "you talking to me" => "Well, are you? 😏",
            "here's looking at you" => "Kid. — Casablanca reference. 🍸",
        ];

        foreach ($map as $k => $v) {
            if (mb_stripos($trigger, $k) !== false) {
                return $v;
            }
        }

        return "That sounded familiar… but I don't have the line ready. Try another classic!";
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatLog;
use App\Models\ChatbotSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * API 1: INIT
     */
    public function init(Request $request)
    {
        $settings = $this->getSettings();
        if (empty($settings)) {
            return response()->json([
                'status'  => false,
                'message' => 'Chatbot settings not found.',
            ], 404);
        }

        $projects    = $this->getProjects();
        $projectList = [];
        foreach ($projects as $key => $p) {
            if (!empty($p['is_active'])) {
                $projectList[] = ['key' => $key, 'name' => $p['name']];
            }
        }
        $greeting = $settings['greeting']['tanglish']
                 ?? $settings['greeting']['en']
                 ?? ['Hello!'];
        return response()->json([
            'status'        => true,
            'bot_name'      => $settings['bot_name']     ?? 'Assistant',
            'company_name'  => $settings['company_name'] ?? '',
            'tagline'       => $settings['tagline']      ?? '',
            'working_hours' => $settings['working_hours'] ?? '',
            'greeting'      => $this->randomReply($greeting),
            'menu'          => $settings['menu']['tanglish']
                            ?? $settings['menu']['en']
                            ?? '',
            'projects'      => $projectList,
            'contact'       => '93 810 55 555',
        ], 200);
    }

    /**
     * API 2: CHAT
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:500',
            'session_id' => 'nullable|string|max:100',
        ]);

        $rawMessage = trim($request->input('message'));
        $message    = $this->normalize($rawMessage);
        $lang       = $this->detectLanguage($rawMessage);
        $sessionId  = $request->input('session_id');

        $session  = $sessionId ? ChatbotSession::getOrCreate($sessionId) : null;
        $settings = $this->getSettings();
        $projects = $this->getProjects();

        if (empty($settings)) {
            return response()->json([
                'status'  => false,
                'message' => 'Chatbot not configured.',
            ], 500);
        }

        // Detect project in message
        $matchedProjectKey = null;
        foreach ($projects as $key => $project) {
            if (empty($project['is_active'])) continue;
            if ($this->matchKeywords($message, $project['keywords'] ?? [])) {
                $matchedProjectKey = $key;
                break;
            }
        }

        // Fall back to session's last project
        if (!$matchedProjectKey && $session && $session->last_project) {
            $matchedProjectKey = $session->last_project;
        }

        if ($session && $matchedProjectKey) {
            $session->last_project = $matchedProjectKey;
            $session->save();
        }

        // ==========================================================
        // STEP 1: JSON INTENTS FIRST
        // ==========================================================
        $jsonReply = $this->matchJsonIntent(
            $message,
            $lang,
            $projects,
            $settings,
            $matchedProjectKey
        );

        if ($jsonReply) {
            Log::info('Chatbot: JSON intent matched', [
                'message' => $rawMessage,
                'lang'    => $lang,
            ]);
            return $this->success($jsonReply, $lang, 'json', $rawMessage, $sessionId);
        }

        // ==========================================================
        // STEP 2: LLM FALLBACK
        // ==========================================================
        Log::info('Chatbot: Calling LLM', [
            'message' => $rawMessage,
            'lang'    => $lang,
            'project' => $matchedProjectKey,
        ]);

        $llmReply = $this->callLLM(
            $rawMessage,
            $lang,
            $session,
            $projects,
            $settings,
            $matchedProjectKey
        );

        if ($llmReply) {
            Log::info('Chatbot: LLM reply success', [
                'preview' => substr($llmReply, 0, 100),
            ]);
            return $this->success($llmReply, $lang, 'llm:groq', $rawMessage, $sessionId);
        }

        Log::warning('Chatbot: LLM failed, using static fallback');

        // ==========================================================
        // STEP 3: STATIC FALLBACK
        // ==========================================================
        $fallback = $settings['fallback'][$lang]
                 ?? $settings['fallback']['en']
                 ?? ['Sorry, I did not understand.'];

        if (is_array($fallback)) {
            $fallback = $this->randomReply($fallback);
        }

        return $this->success($fallback, $lang, 'fallback', $rawMessage, $sessionId);
    }

    // ==========================================================
    // JSON INTENT MATCHING
    // ==========================================================
 private function matchJsonIntent($message, $lang, $projects, $settings, $matchedProjectKey)
{
    // ============================================
    // 1. YES/OK — context-aware reply
    // ============================================
    $yesWords = ['yes', 'aama', 'aamaa', 'sari', 'seri', 'ok', 'okay'];
    if ($this->matchKeywords($message, $yesWords)) {
        if ($matchedProjectKey && isset($projects[$matchedProjectKey])) {
            $projectName = $projects[$matchedProjectKey]['name'];
            return $lang === 'tanglish'
                ? "Super! 😊 *{$projectName}* la enna details venum?\n\n• 💰 Price\n• 📍 Location\n• ✨ Amenities\n• 📐 Configurations\n• 🏗️ Specifications\n\n👉 Enna venum sollunga?"
                : "Great! 😊 What would you like to know about *{$projectName}*?\n\n• 💰 Price\n• 📍 Location\n• ✨ Amenities\n• 📐 Configurations\n• 🏗️ Specifications\n\n👉 Which one?";
        }
        return $lang === 'tanglish'
            ? "Super! 😊 Enna project pathi theriyanum?\n\n1️⃣ *Gurudev* - Guduvancheri\n2️⃣ *Privana* - Perungudi\n\n👉 Project name type pannunga (Gurudev / Privana)"
            : "Great! 😊 Which project would you like to know about?\n\n1️⃣ *Gurudev* - Guduvancheri\n2️⃣ *Privana* - Perungudi\n\n👉 Type the project name";
    }

    


    // ============================================
    // 2. SUB-INTENT FIRST (price/location/amenities)
    // ============================================
    $subIntent = $this->detectSubIntent($message);
      // 👇 INTHA PUTHU BLOCK AH ADD PANNUNGA 👇
    if ($subIntent === 'contact') {
        if ($matchedProjectKey && isset($projects[$matchedProjectKey]['contact'])) {
            return $projects[$matchedProjectKey]['contact'][$lang] ?? $projects[$matchedProjectKey]['contact']['en'];
        }
        return $lang === 'tanglish'
            ? "Namma PKR Estates contact number: *93 810 55 555*.\n\nEnna project pathi theriyanum? (Gurudev / Privana)"
            : "Our PKR Estates contact number is *93 810 55 555*.\n\nWhich project would you like to know about? (Gurudev / Privana)";
    }
    // 👆 INTHA VARAI 👆
    if ($subIntent && $matchedProjectKey && isset($projects[$matchedProjectKey])) {
        $askingWhichProject = $this->matchKeywords($message, [
            'which project', 'enna project', 'entha project',
            'gurudev or privana', 'gurudev illa privana',
        ]);
        if (!$askingWhichProject) {
            $reply = $this->getProjectReply($projects[$matchedProjectKey], $subIntent, $lang);
            if ($reply) return $reply;
        }
    }
    

    // ============================================
    // 3. EXPLICIT PROJECT MENTION
    // ============================================
    if ($matchedProjectKey && isset($projects[$matchedProjectKey])) {
        $explicitMatch = $this->matchKeywords(
            $message,
            $projects[$matchedProjectKey]['keywords'] ?? []
        );
        if ($explicitMatch && !$subIntent) {
            return $this->getProjectReply($projects[$matchedProjectKey], 'info', $lang);
        }
    }

    // ============================================
    // 4. COMMON INTENTS
    // ============================================
    foreach ($settings['common_intents'] ?? [] as $intent) {
        // Skip "yes" intent (already handled in Step 1)
        if (($intent['name'] ?? '') === 'yes') continue;

        if ($this->matchKeywords($message, $intent['keywords'] ?? [])) {
            $reply = $intent['reply'][$lang] ?? $intent['reply']['en'] ?? '';
            if (is_array($reply)) $reply = $this->randomReply($reply);
            if ($reply) return $reply;
        }
    }

    // ============================================
    // 5. PERSONAL CHAT
    // ============================================
    foreach ($settings['personal_chat'] ?? [] as $intent) {
        if ($this->matchKeywords($message, $intent['keywords'] ?? [])) {
            $reply = $intent['reply'][$lang] ?? $intent['reply']['en'] ?? '';
            if (is_array($reply)) $reply = $this->randomReply($reply);
            if ($reply) return $reply;
        }
    }

    // ============================================
    // 6. REGEX PATTERNS
    // ============================================
    foreach ($settings['patterns'] ?? [] as $pattern) {
        $regex = $pattern['regex'] ?? null;
        if (!$regex) continue;
        $regex = trim($regex, '/');
        if (@preg_match('/' . $regex . '/i', $message)) {
            $reply = $pattern['reply'][$lang] ?? $pattern['reply']['en'] ?? '';
            if ($reply) return $this->interpolatePlaceholders($reply);
        }
    }

    // ============================================
// 1.5 SITE VISIT CHOICE (1 / 2 / 3)
// ============================================
$siteVisitWords = ['site visit', 'visit', 'appointment', 'visit pannanum', 'project paakanum'];
$isSiteVisitContext = $this->matchKeywords($message, $siteVisitWords);

// Customer just "1", "2", "3" type pannirundha
if (in_array(trim($message), ['1', '2', '3'], true) || $isSiteVisitContext) {

    $choice = null;
    if (in_array(trim($message), ['1', '2', '3'], true)) {
        $choice = trim($message);
    } elseif ($this->matchKeywords($message, ['car', 'pickup', 'car arrange'])) {
        $choice = '2';
    } elseif ($this->matchKeywords($message, ['naan vanthen', 'i will come', 'varigala', 'varuven'])) {
        $choice = '1';
    } elseif ($this->matchKeywords($message, ['date', 'time', 'naalaiku', 'tomorrow', 'today'])) {
        $choice = '3';
    }

    if ($choice) {
        $reply = $lang === 'tanglish'
            ? "👍 Super bro! Site visit arrange pannanum na, unga *WhatsApp number* kudunga.\n\n📱 Number type pannunga (10 digits)\n\nNamma team seekiram contact pannuvanga."
            : "👍 Great! To arrange your site visit, please share your *WhatsApp number*.\n\n📱 Type your 10-digit number\n\nOur team will contact you shortly.";

        return $reply;
    }
}

    return null;
}



    /**
     * Detect sub-intent
     */
    private function detectSubIntent($message)
    {
        $map = [
            'price'          => ['price', 'cost', 'rate', 'evlo', 'vilai',
                                 'amount', 'budget', 'pricing', 'how much', 'low amount'],
            'location'       => ['location', 'address', 'place', 'idam',
                                 'entha location', 'enga iruku', 'enga irukku',
                                 'location enna', 'address enna', 'where is'],
            'amenities'      => ['amenities', 'amenity', 'facilities',
                                 'facility', 'features', 'gym', 'pool', 'swimming', 'park'],
            'configuration'  => ['configuration', 'bhk', '1bhk', '2bhk', '3bhk',
                                 '4bhk', 'size', 'sqft', 'sq ft', 'layout',
                                 'floor plan', 'floorplan'],
            'specifications' => ['specification', 'specifications', 'specs', 'spec',
                                 'structure', 'flooring', 'kitchen details',
                                 'bathroom details', 'doors', 'windows', 'electrical'],
            'contact'        => ['contact', 'phone', 'call', 'whatsapp',
                                 'number', 'mobile'],
        ];

        foreach ($map as $intent => $keywords) {
            if ($this->matchKeywords($message, $keywords)) {
                return $intent;
            }
        }
        return null;
    }

    // ==========================================================
    // LLM (GROQ)
    // ==========================================================
    private function callLLM($userMessage, $lang, $session, $projects, $settings, $matchedProjectKey = null)
    {
        $apiKey = config('services.groq.key');
        $url    = config('services.groq.url', 'https://api.groq.com/openai/v1/chat/completions');
        $model  = config('services.groq.model', 'openai/gpt-oss-120b');

        if (empty($apiKey)) {
            Log::error('Groq LLM: API key empty');
            return null;
        }
        $context      = $this->buildLLMContext($projects, $settings, $session, $matchedProjectKey);
        $systemPrompt = $this->buildSystemPrompt($context, $lang);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(20)->post($url, [
                'model'       => $model,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userMessage],
                ],
                'temperature' => 0.6,
                'max_tokens'  => 400,
            ]);

            Log::info('Groq LLM response', [
                'status' => $response->status(),
                'model'  => $model,
            ]);

            if ($response->successful()) {
                $reply = $response->json()['choices'][0]['message']['content'] ?? null;
                return $reply ? trim($reply) : null;
            }

            Log::error('Groq LLM error', ['body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Groq LLM exception: ' . $e->getMessage());
            return null;
        }
    }

    private function buildSystemPrompt($context, $lang)
    {
        return <<<PROMPT
You are 'PKR Assistant', a friendly and helpful real estate chatbot for PKR Estates, Chennai.

CONTEXT DATA (Use this to answer accurately):
{$context}

RULES:
1. Reply in the SAME language as the user. Detected user language: '{$lang}'.
   - If 'tanglish': reply in Tanglish (Tamil typed in English letters — e.g., 'Vanakkam bro! Enna help venum?').
   - If 'en': reply in English.
   - NEVER use Tamil script (தமிழ்). Only Tanglish or English.
2. Be human-like, friendly, use emojis naturally. 😊
3. Keep replies SHORT — 2 to 5 lines max.
4. ALWAYS end with a helpful follow-up question.
   Example: 'Enna details venum? (price / location / amenities)'
5. Give exact details (price, location, BHK, amenities, specifications) when asked.
6. If the user asks something unrelated to real estate, politely redirect to property topics.
7. Use *bold* for project names and important info.
8. Contact: 93 810 55 555. Website: www.pkrestates.com
9. NEVER invent prices or details. If unsure, ask the user to call 93 810 55 555.
10. If user is just chatting (hi, how are you), be friendly and gently guide to property topics.
11. If user asks your name — say: "Naan PKR Assistant, PKR Estates la unga home search la help panna inga iruken."
12. If user asks for info NOT in CONTEXT (founder name, legal details, personal info, exact possession dates, exact prices not in data), say: "Adhu enaku theriyala bro 😊 93 810 55 555 ku call pannunga, avanga ungaluku correct details tharuvanga."
13. NEVER make up facts. If unsure → redirect to phone number 93 810 55 555.
PROMPT;
    }

    private function buildLLMContext($projects, $settings, $session, $matchedProjectKey = null)
    {
        $context = [];
        $context['company'] = [
            'name'    => $settings['company_name'] ?? 'PKR Estates',
            'tagline' => $settings['tagline']      ?? 'An Affordable Home Company',
            'contact' => '93 810 55 555',
            'website' => 'www.pkrestates.com',
            'office'  => 'Flat A10, Archana Castle, 4/23 Patrick Church Road, St.Thomas Mount, Chennai 600016',
        ];
        $context['projects'] = [];
        foreach ($projects as $key => $p) {
            if (empty($p['is_active'])) continue;
            $context['projects'][$key] = [
                'name'           => $p['name']                     ?? '',
                'info'           => $p['info']['en']               ?? '',
                'price'          => $p['price']['en']              ?? '',
                'location'       => $p['location']['en']           ?? '',
                'amenities'      => $p['amenities']['en']          ?? '',
                'configuration'  => $p['configuration']['en']      ?? '',
                'specifications' => $p['specifications']['en']     ?? '',
                // 'contact'        => $p['contact']['en']            ?? '',
            ];
        }
        if ($session) {
            $context['session'] = ['last_project' => $session->last_project];
        }
        if ($matchedProjectKey) {
            $context['matched_project'] = $matchedProjectKey;
        }
        return json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ==========================================================
    // JSON LOADERS
    // ==========================================================
    private function getProjects()
    {
        $path = storage_path('app/chatbot/projects.json');
        if (!file_exists($path)) {
            Log::warning('Chatbot: projects.json missing');
            return [];
        }
        return json_decode(file_get_contents($path), true) ?? [];
    }

    private function getSettings()
    {
        $path = storage_path('app/chatbot/settings.json');
        if (!file_exists($path)) {
            Log::warning('Chatbot: settings.json missing');
            return [];
        }
        return json_decode(file_get_contents($path), true) ?? [];
    }

    // ==========================================================
    // HELPERS
    // ==========================================================
    private function normalize($msg)
    {
        $msg = mb_strtolower($msg, 'UTF-8');
        $msg = preg_replace('/[^\p{L}\p{N}\s]/u', '', $msg);
        $msg = preg_replace('/\s+/', ' ', $msg);
        return trim($msg);
    }

    private function detectLanguage($msg)
    {
        if (preg_match('/[\x{0B80}-\x{0BFF}]/u', $msg)) return 'tanglish';
        $tanglishWords = [
            'hi','hello','hey','vanakkam','vanakam','nandri','romba','nalla','seri','sari',
            'enna','epdi','eppo','enga','ethan','ethana','entha','ethu','evlo','yaaru','edhu',
            'iruku','irukku','irukka','iruka','irukinga','illa','illaya','aama','aamaa',
            'venum','vendam','venumaa','kidaikum','kidaikuma','kedaikuma',
            'theriyum','theriyuma','puriyala','puriyum','pesalam','sollu','sollunga','solli',
            'kelu','kelunga','kekuren','pannunga','pannu','kudunga','kudu','varen','vaanga',
            'poren','poga','pogalam','pooren','pathen','parthutu','saptiya','saapten',
            'enaku','unaku','unakku','en','un','avan','aval','avanga','naan','nee','neenga',
            'pathi','patti','bro','da','di','machan','machi',
            'veedu','flat','manai','nilam','kattidam','kattadam','amount','low','high',
            'sollar','sollunga','details','vivaram','visayam',
            'project','projects','bhk','sqft','loan','emi','bank',
            'sugama','soukyama','epdi','irukinga','irukingala',
        ];
        $words = preg_split('/\s+/', mb_strtolower($msg, 'UTF-8'));
        foreach ($words as $w) {
            $w = preg_replace('/[^a-z]/', '', $w);
            if ($w && in_array($w, $tanglishWords, true)) return 'tanglish';
        }
        return 'en';
    }

    /**
     * STRICT keyword matching — exact word only, fuzzy only for long keywords
     */
    private function matchKeywords($message, $keywords)
    {
        if (empty($keywords)) return false;
        $msgWords = preg_split('/\s+/', $message);
        $msgWords = array_values(array_filter($msgWords));
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw), 'UTF-8');
            if ($kw === '') continue;
            if (mb_strpos($kw, ' ') !== false) {
                if (mb_strpos($message, $kw) !== false) return true;
                continue;
            }
            foreach ($msgWords as $word) {
                if ($word === $kw) return true;
                if (mb_strlen($kw) >= 6 && mb_strlen($word) >= 6) {
                    $distance   = levenshtein($word, $kw);
                    $maxAllowed = (mb_strlen($kw) >= 9) ? 2 : 1;
                    if ($distance <= $maxAllowed) return true;
                }
            }
        }
        return false;
    }

    private function getProjectReply($project, $sub, $lang)
    {
        if (empty($project[$sub])) $sub = 'info';
        $data = $project[$sub] ?? [];
        return $data[$lang]
            ?? $data['en']
            ?? $project['info'][$lang]
            ?? $project['info']['en']
            ?? null;
    }

    private function randomReply($arr)
    {
        if (!is_array($arr) || empty($arr)) return is_string($arr) ? $arr : '';
        return $arr[array_rand($arr)];
    }

    private function interpolatePlaceholders($text)
    {
        $now = now();
        return str_replace(
            ['{current_day}', '{current_date}', '{current_time}'], 
            [$now->format('l'), $now->format('d M Y'), $now->format('h:i A')],
             $text );
    }

    private function success($reply, $lang, $matchedBy, $userMessage = null, $sessionId = null)
    {
        if ($sessionId && $userMessage) {
            try {
                ChatLog::create([
                    'session_id'   => $sessionId,
                    'user_message' => $userMessage,
                    'bot_reply'    => $reply,
                    'language'     => $lang,
                    'matched_by'   => $matchedBy,
                ]);
            } catch (\Exception $e) {
                Log::error('ChatLog save failed: ' . $e->getMessage());
            }
        }
        return response()->json([
            'status'     => true,
            'reply'      => $reply,
            'language'   => $lang,
            'matched_by' => $matchedBy,
            'timestamp'  => now()->toIso8601String(),
        ], 200);
    }


    /**
 * API 3: CHAT HISTORY
 * GET /api/chatbot/history/{session_id}
 */
public function history(Request $request, $sessionId)
{
    if (empty($sessionId)) {
        return response()->json([
            'status'  => false,
            'message' => 'Session ID is required.',
        ], 400);
    }
    $logs = ChatLog::where('session_id', $sessionId)
        ->orderBy('created_at', 'asc')
        ->get();

    if ($logs->isEmpty()) {
        return response()->json([
            'status'  => true,
            'session_id' => $sessionId,
            'messages' => [],
            'total'    => 0,
        ], 200);
    }
    $messages = [];
    foreach ($logs as $log) {
        $messages[] = [
            'id'         => $log->id . '_user',
            'role'       => 'user',
            'message'    => $log->user_message,
            'language'   => $log->language,
            'timestamp'  => $log->created_at->toIso8601String(),
        ];
        $messages[] = [
            'id'         => $log->id . '_bot',
            'role'       => 'bot',
            'message'    => $log->bot_reply,
            'language'   => $log->language,
            'matched_by' => $log->matched_by,
            'timestamp'  => $log->created_at->toIso8601String(),
        ];
    }
    return response()->json([
        'status'     => true,
        'session_id' => $sessionId,
        'total'      => count($messages),
        'messages'   => $messages,
    ], 200);
}




}
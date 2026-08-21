<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\DirectionSubject;
use App\Models\TestQuestion;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    private function getSession(): ?TestSession
    {
        $id = session('cabinet_session_id');
        if (!$id) return null;
        return TestSession::with(['applicant', 'direction'])->find($id);
    }

    // Til tanlash sahifasi
    public function language(): Response
    {
        $session = $this->getSession();
        if (!$session) return redirect()->route('cabinet.login');

        if ($session->status === 'completed') {
            return redirect()->route('cabinet.test.result');
        }

        return Inertia::render('Cabinet/Test/Language', [
            'session' => $session->only('id', 'language', 'foreign_lang'),
            'applicant' => [
                'name'      => $session->applicant->last_name . ' ' . $session->applicant->first_name,
                'direction' => $session->direction->name_uz,
            ],
        ]);
    }

    // Til saqlash
    public function setLanguage(Request $request)
    {
        $request->validate([
            'language'     => 'required|in:uz,ru',
            'foreign_lang' => 'required|in:en,ar',
        ], [
            'language.required'     => 'Tilni tanlang',
            'foreign_lang.required' => 'Xorijiy tilni tanlang',
        ]);

        $session = $this->getSession();
        if (!$session) return redirect()->route('cabinet.login');

        $session->update([
            'language'     => $request->language,
            'foreign_lang' => $request->foreign_lang,
        ]);

        return redirect()->route('cabinet.test.start');
    }

    // Test boshlash
    public function start(): Response
    {
        $session = $this->getSession();
        if (!$session) return redirect()->route('cabinet.login');

        if ($session->status === 'completed') {
            return redirect()->route('cabinet.test.result');
        }

        $questions = $this->loadQuestions($session);

        if (empty($questions)) {
            return Inertia::render('Cabinet/Test/Error', [
                'message' => "Bu yo'nalish uchun savollar mavjud emas!",
            ]);
        }

        // Sessiyani faollashtirish
        if ($session->status === 'pending') {
            $session->update([
                'status'     => 'active',
                'started_at' => now(),
                'expires_at' => now()->addMinutes(90),
            ]);
        }

        // Vaqt tugaganini tekshirish
        if ($session->expires_at && $session->expires_at->isPast()) {
            $this->finishSession($session);
            return redirect()->route('cabinet.test.result');
        }

        return Inertia::render('Cabinet/Test/Taking', [
            'session' => [
                'id'          => $session->id,
                'language'    => $session->language,
                'foreign_lang'=> $session->foreign_lang,
                'started_at'  => $session->started_at,
                'expires_at'  => $session->expires_at,
            ],
            'questions' => $questions,
            'answers'   => $session->answers ?? [],
        ]);
    }

    // Javob saqlash (AJAX)
    public function saveAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'answer'      => 'required|in:a,b,c,d',
        ]);

        $session = $this->getSession();
        if (!$session || $session->status !== 'active') {
            return response()->json(['error' => 'Sessiya faol emas'], 403);
        }

        if ($session->expires_at && $session->expires_at->isPast()) {
            $this->finishSession($session);
            return response()->json(['expired' => true], 403);
        }

        $answers = $session->answers ?? [];
        $answers[$request->question_id] = $request->answer;
        $session->update(['answers' => $answers]);

        return response()->json(['success' => true]);
    }

    // Testni yakunlash
    public function finish(Request $request)
    {
        $session = $this->getSession();
        if (!$session || $session->status !== 'active') {
            return redirect()->route('cabinet.login');
        }

        $this->finishSession($session);
        return redirect()->route('cabinet.test.result');
    }

    // Natija sahifasi
    public function result(): Response
    {
        $session = $this->getSession();
        if (!$session) return redirect()->route('cabinet.login');

        if ($session->status !== 'completed') {
            return redirect()->route('cabinet.test.start');
        }

        $questions = $this->loadQuestions($session);

        return Inertia::render('Cabinet/Test/Result', [
            'session'   => $session->load('applicant', 'direction'),
            'questions' => $questions,
            'answers'   => $session->answers ?? [],
        ]);
    }

    // Sessiyani yakunlash va ball hisoblash
    private function finishSession(TestSession $session): void
    {
        $questions      = $this->loadQuestions($session);
        $answers        = $session->answers ?? [];
        $totalScore     = 0;
        $correctAnswers = 0;

        foreach ($questions as $q) {
            $userAnswer = $answers[$q['id']] ?? null;
            if ($userAnswer && $userAnswer === $q['correct_answer']) {
                $totalScore     += $q['score_per_question'];
                $correctAnswers++;
            }
        }

        $session->update([
            'status'          => 'completed',
            'finished_at'     => now(),
            'score'           => round($totalScore, 1),
            'correct_answers' => $correctAnswers,
            'total_questions' => count($questions),
        ]);
    }

    // Yo'nalish uchun savollarni yuklash
    private function loadQuestions(TestSession $session): array
    {
        $directionSubjects = DirectionSubject::where('direction_id', $session->direction_id)
            ->where('is_active', true)
            ->with('subject')
            ->get();

        $questions = [];

        foreach ($directionSubjects as $ds) {
            $lang = $ds->block_type === 'mandatory'
                ? $session->language
                : ($ds->subject->name_uz === 'Xorijiy til'
                    ? $session->foreign_lang
                    : $session->language);

            $subjectQuestions = TestQuestion::where('subject_id', $ds->subject_id)
                ->where('language', $lang)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit($ds->questions_count)
                ->get()
                ->makeVisible('correct_answer')
                ->toArray();

            foreach ($subjectQuestions as &$q) {
                $q['score_per_question'] = (float) $ds->score_per_question;
                $q['block_type']         = $ds->block_type;
                $q['subject_name']       = $session->language === 'uz'
                    ? $ds->subject->name_uz
                    : $ds->subject->name_ru;
            }

            $questions = array_merge($questions, $subjectQuestions);
        }

        return $questions;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportQuickQuestion;

class SupportQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of quick questions with Customer & Business tabs
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'customer');
        if (!in_array($tab, ['customer', 'business'])) {
            $tab = 'customer';
        }

        $customerQuestions = SupportQuickQuestion::where('user_type', 'customer')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $businessQuestions = SupportQuickQuestion::where('user_type', 'business')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('support_questions.index', compact('tab', 'customerQuestions', 'businessQuestions'));
    }

    /**
     * Store new question
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_type' => 'required|in:customer,business,all',
            'category' => 'nullable|string|max:100',
            'question' => 'required|string|min:3',
            'auto_reply' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        SupportQuickQuestion::create([
            'user_type' => $request->user_type,
            'category' => $request->category ?? 'General',
            'question' => $request->question,
            'auto_reply' => $request->auto_reply,
            'sort_order' => $request->sort_order ?? 0,
            'status' => 'active',
        ]);

        return redirect()->route('support.questions.index', ['tab' => $request->user_type === 'business' ? 'business' : 'customer'])
            ->with('success', 'Support question added successfully.');
    }

    /**
     * Update existing question
     */
    public function update(Request $request, $id)
    {
        $question = SupportQuickQuestion::findOrFail($id);

        $request->validate([
            'user_type' => 'required|in:customer,business,all',
            'category' => 'nullable|string|max:100',
            'question' => 'required|string|min:3',
            'auto_reply' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $question->update([
            'user_type' => $request->user_type,
            'category' => $request->category ?? 'General',
            'question' => $request->question,
            'auto_reply' => $request->auto_reply,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status,
        ]);

        return redirect()->route('support.questions.index', ['tab' => $question->user_type === 'business' ? 'business' : 'customer'])
            ->with('success', 'Support question updated successfully.');
    }

    /**
     * Toggle status (active/inactive) via AJAX
     */
    public function toggleStatus(Request $request, $id)
    {
        $question = SupportQuickQuestion::findOrFail($id);
        $question->status = $question->status === 'active' ? 'inactive' : 'active';
        $question->save();

        return response()->json([
            'success' => true,
            'status' => $question->status,
        ]);
    }

    /**
     * Delete question
     */
    public function destroy($id)
    {
        $question = SupportQuickQuestion::findOrFail($id);
        $userType = $question->user_type;
        $question->delete();

        return redirect()->route('support.questions.index', ['tab' => $userType === 'business' ? 'business' : 'customer'])
            ->with('success', 'Support question deleted successfully.');
    }
}

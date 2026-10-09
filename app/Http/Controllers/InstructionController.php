<?php

namespace App\Http\Controllers;

use App\Models\Instruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructionController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('instructions.view')) {
            abort(403, 'غير مصرح لك بمشاهدة التعليمات.');
        }

        $instructions = Instruction::with('user')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('instructions.index', compact('instructions'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('instructions.manage')) {
            abort(403, 'غير مصرح لك بإضافة تعليمات.');
        }

        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Instruction::create([
            'title'      => $request->title,
            'content'    => $request->content,
            'created_by' => Auth::id(),
            'is_pinned'  => $request->has('is_pinned'),
        ]);

        return back()->with('success', 'تم إضافة التعليمة/التنبيه بنجاح.');
    }

    public function togglePin(Instruction $instruction)
    {
        if (!Auth::user()->can('instructions.manage')) {
            abort(403, 'غير مصرح لك بتثبيت التعليمات.');
        }

        $instruction->update(['is_pinned' => !$instruction->is_pinned]);

        $status = $instruction->is_pinned ? 'تم تثبيت' : 'تم إلغاء تثبيت';
        return back()->with('success', "{$status} التعليمة بنجاح.");
    }

    public function destroy(Instruction $instruction)
    {
        if (!Auth::user()->can('instructions.manage')) {
            abort(403, 'غير مصرح لك بحذف التعليمات.');
        }

        $instruction->delete();

        return back()->with('success', 'تم حذف التعليمة بنجاح.');
    }
}

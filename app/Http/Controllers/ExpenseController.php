<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->expenses()->with('category');

        if (!empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        $expenses = $query->get();

        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
        ]);

        $expense = Auth::user()->expenses()->create($request->all());

        return response()->json($expense, 201);
    }

    public function update(Request $request, $id)
    {
        $expense = Auth::user()->expenses()->findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
        ]);

        $expense->update($request->all());

        return response()->json($expense);
    }

    public function destroy($id)
    {
        $expense = Auth::user()->expenses()->findOrFail($id);
        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }

    public function summary(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $summary = Auth::user()->expenses()
            ->whereBetween('expense_date', [$request->start_date, $request->end_date])
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(fn($expenses) => $expenses->sum('amount'));

        return response()->json($summary);
    }

    public function load_expense()
    {
        return view('expense.expense');

       
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OperationalExpense::with('user')->orderBy('expense_date', 'desc');

        if ($request->filled('month')) {
            $query->whereMonth('expense_date', $request->month);
        }

        $expenses = $query->get();

        return response()->json([
            'success' => true,
            'total_amount' => $expenses->sum('amount'),
            'data' => $expenses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $expense = OperationalExpense::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Biaya operasional berhasil dicatat!',
            'data' => $expense,
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $expense = OperationalExpense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data biaya operasional berhasil dihapus.'
        ]);
    }
}

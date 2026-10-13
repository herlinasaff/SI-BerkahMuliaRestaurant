<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Ringkasan Dashboard Harian (Kartu Atas)
     */
    public function dashboard(): JsonResponse
    {
        $today = now()->toDateString();

        $todaySales = Sale::whereDate('sale_date', $today)->sum('final_total');
        $todayPlates = Sale::whereDate('sale_date', $today)->where('customer_type', 'yarwe')->sum('total_plates');
        $todayTransactions = Sale::whereDate('sale_date', $today)->count();

        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();

        // Penjualan per tipe pelanggan hari ini
        $umumTotal = Sale::whereDate('sale_date', $today)->where('customer_type', 'umum')->sum('final_total');
        $vipTotal = Sale::whereDate('sale_date', $today)->where('customer_type', 'vip')->sum('original_total');
        $biroTotal = Sale::whereDate('sale_date', $today)->where('customer_type', 'yarwe')->sum('final_total');

        return response()->json([
            'success' => true,
            'data' => [
                'today_sales' => $todaySales,
                'today_plates' => $todayPlates,
                'today_transactions' => $todayTransactions,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'by_customer_type' => [
                    'umum' => $umumTotal,
                    'vip_value' => $vipTotal,
                    'biro_yarwe' => $biroTotal,
                ]
            ]
        ]);
    }

    /**
     * Laporan Laba Rugi Bersih (Omset - Belanja Bahan - Biaya Operasional)
     */
    public function profitLoss(Request $request): JsonResponse
    {
        $period = $request->period ?? 'monthly'; // daily, weekly, monthly, yearly

        $filterDate = function ($query, $column) use ($period) {
            return match ($period) {
                'daily' => $query->whereDate($column, now()->toDateString()),
                'weekly' => $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]),
                'monthly' => $query->whereMonth($column, now()->month)->whereYear($column, now()->year),
                'yearly' => $query->whereYear($column, now()->year),
                default => $query,
            };
        };

        // 1. Total Penjualan (Omset)
        $salesQuery = Sale::query();
        $filterDate($salesQuery, 'sale_date');
        $totalSales = $salesQuery->sum('final_total');

        // 2. HPP / Belanja Bahan
        $purchaseQuery = Purchase::query();
        $filterDate($purchaseQuery, 'purchase_date');
        $totalHpp = $purchaseQuery->sum('total_amount');

        // 3. Biaya Operasional
        $expenseQuery = OperationalExpense::query();
        $filterDate($expenseQuery, 'expense_date');
        $totalExpenses = $expenseQuery->sum('amount');

        // 4. Laba Kotor & Laba Bersih
        $grossProfit = $totalSales - $totalHpp;
        $netProfit = $grossProfit - $totalExpenses;
        $margin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;

        return response()->json([
            'success' => true,
            'period' => $period,
            'data' => [
                'omset' => $totalSales,
                'hpp_belanja' => $totalHpp,
                'biaya_operasional' => $totalExpenses,
                'laba_kotor' => $grossProfit,
                'laba_bersih' => $netProfit,
                'profit_margin_percent' => round($margin, 2),
            ]
        ]);
    }
}

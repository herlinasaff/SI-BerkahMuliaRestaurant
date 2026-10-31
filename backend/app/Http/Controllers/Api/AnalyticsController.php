<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function getDashboard(Request $request)
    {
        $user = $request->user();
        $role = $user->role->name ?? 'kasir';
        $today = Carbon::today();

        $nowTime = Carbon::now()->format('H:i:s');
        $currentShift = Shift::where('is_active', true)
            ->where('start_time', '<=', $nowTime)
            ->where('end_time', '>=', $nowTime)
            ->first();

        $currentShiftId = $currentShift ? $currentShift->id : 1;

        $lowStockProducts = Product::where('is_stockable', true)
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->get(['id', 'name', 'stock', 'unit']);
        $lowStockCount = $lowStockProducts->count();

        $shiftSalesQuery = Sale::whereDate('sale_date', $today)
            ->where('shift_id', $currentShiftId);

        if ($role === 'kasir') {
            $shiftSalesQuery->where('user_id', $user->id);
        }

        $shiftOmzet = (float) $shiftSalesQuery->sum('grand_total');
        $shiftCount = (int) $shiftSalesQuery->count();

        $todaySalesQuery = Sale::whereDate('sale_date', $today);
        $todayOmzet = (float) $todaySalesQuery->sum('grand_total');
        $todayCount = (int) $todaySalesQuery->count();

        $shiftsList = Shift::where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();
        $shiftBreakdown = [];
        foreach ($shiftsList as $sh) {
            $sQuery = Sale::whereDate('sale_date', $today)->where('shift_id', $sh->id);
            $shiftBreakdown[] = [
                'shift_id' => $sh->id,
                'shift_name' => $sh->name,
                'time_range' => substr($sh->start_time, 0, 5) . ' - ' . substr($sh->end_time, 0, 5),
                'total_omzet' => (float) $sQuery->sum('grand_total'),
                'total_transactions' => (int) $sQuery->count(),
            ];
        }

        $customerBreakdown = [];
        foreach (['umum' => 'Umum', 'vip' => 'VIP', 'yarwe' => 'Biro/Yarwe'] as $ktype => $ktitle) {
            $q = Sale::whereDate('sale_date', $today)->where('customer_type', $ktype);
            $customerBreakdown[$ktype] = [
                'label' => $ktitle,
                'transactions' => (int) $q->count(),
                'omzet' => (float) $q->sum('grand_total'),
                'discount' => (float) $q->sum('total_discount'),
                'biro_fee' => (float) $q->sum('yarwe_total'),
                'total_plates' => (int) $q->sum('total_plates'),
            ];
        }

        $todayPurchases = (float) DB::table('purchases')->whereDate('purchase_date', $today)->sum('total_amount');
        $todayExpenses = (float) DB::table('operational_expenses')->whereDate('expense_date', $today)->sum('amount');
        $totalCost = $todayPurchases + $todayExpenses;
        $netProfitEstimate = $todayOmzet - $totalCost;

        return response()->json([
            'success' => true,
            'role' => $role,
            'data' => [
                'shift_omzet' => $shiftOmzet,
                'shift_transactions' => $shiftCount,
                'today_omzet' => $todayOmzet,
                'today_transactions' => $todayCount,
                'low_stock_count' => $lowStockCount,
                'low_stock_items' => $lowStockProducts,
                'shift_breakdown' => $shiftBreakdown,
                'customer_breakdown' => $customerBreakdown,
                'today_purchases' => $todayPurchases,
                'today_expenses' => $todayExpenses,
                'total_cost' => $totalCost,
                'net_profit_estimate' => $netProfitEstimate,
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = Carbon::today('Asia/Jakarta');
        $startOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth();

        // If staff, render staff dashboard (or redirect to POS)
        if ($user->isStaff()) {
            return $this->staffDashboard($user, $today);
        }

        // Admin Dashboard Metrics
        $salesToday = Sale::where('status', 'completed')
            ->whereDate('sold_at', $today)
            ->sum('total');

        $salesThisMonth = Sale::where('status', 'completed')
            ->where('sold_at', '>=', $startOfMonth)
            ->sum('total');

        $transactionsToday = Sale::whereDate('sold_at', $today)->count()
            + Purchase::whereDate('purchased_at', $today)->count();

        $totalProducts = Product::count();

        // 7-day Sales for Chart.js
        $last7Days = [];
        $sales7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Jakarta')->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $label = $date->format('D, d M');

            $dayTotal = Sale::where('status', 'completed')
                ->whereDate('sold_at', $dateStr)
                ->sum('total');

            $last7Days[] = $label;
            $sales7Days[] = (float) $dayTotal;
        }

        // Monthly Sales for Chart.js (Last 6 Months)
        $monthlyLabels = [];
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now('Asia/Jakarta')->subMonths($i);
            $monthLabel = $month->format('M Y');

            $monthTotal = Sale::where('status', 'completed')
                ->whereYear('sold_at', $month->year)
                ->whereMonth('sold_at', $month->month)
                ->sum('total');

            $monthlyLabels[] = $monthLabel;
            $monthlySales[] = (float) $monthTotal;
        }

        // Top 5 Selling Products
        $topProducts = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('sale', function ($query) {
                $query->where('status', 'completed');
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Recent Transactions
        $recentSales = Sale::with('user')->latest()->limit(5)->get();
        $recentPurchases = Purchase::with(['supplier', 'user'])->latest()->limit(5)->get();

        // Low stock products
        $lowStockProducts = Product::whereRaw('stock <= minimum_stock')
            ->with('category')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact(
            'salesToday',
            'salesThisMonth',
            'transactionsToday',
            'totalProducts',
            'last7Days',
            'sales7Days',
            'monthlyLabels',
            'monthlySales',
            'topProducts',
            'recentSales',
            'recentPurchases',
            'lowStockProducts'
        ));
    }

    protected function staffDashboard($user, $today): View
    {
        $mySalesToday = Sale::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('sold_at', $today)
            ->sum('total');

        $myTxCountToday = Sale::where('user_id', $user->id)
            ->whereDate('sold_at', $today)
            ->count();

        $recentSales = Sale::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        $lowStockProducts = Product::whereRaw('stock <= minimum_stock')
            ->with('category')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard.staff', compact(
            'mySalesToday',
            'myTxCountToday',
            'recentSales',
            'lowStockProducts'
        ));
    }
}

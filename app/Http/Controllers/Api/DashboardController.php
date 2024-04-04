<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Saler;
use App\Models\Sales;
use App\Models\SalesDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function totalAmountSaler($id,Request $request)
    {
         // Get input parameters from the request
         $month = $request->input('month', 0); // Default to current month if not provided
         $year = $request->input('year', Carbon::now()->year); // Default to current year if not provided
         
        $lastMonth = Carbon::now()->subMonth();

        $saler = Saler::where('id', $id)->first();

        if (!$saler) {
            // Saler found, you can continue with your logic
            return new PostResource(false, 'Not found!', null);
         } 

        // Saler Total Sales
        $summarySaler = Sales::where('kode_salesman', $saler->Kode)
    ->whereBetween('date', [$lastMonth->startOfMonth()->toDateTimeString(), $lastMonth->endOfMonth()->toDateTimeString()])
    ->selectRaw('COUNT(*) as sales_count, SUM(total_amount) as total_amount, SUM(tax_amount) as tax_amount, SUM(paid_amount) as total_paid_amount')
    ->first();
    $data = [
        'total_amount_saler' => $summarySaler->total_amount ?? 0,
       
    ];

    return response()->json([
        'success' => true,
        'message' => 'Data Total Amount Saler Dashboard',
        'data' => $data,
    ]);
    }

    public function getSummary(Request $request)
    {
        // // Get input parameters from the request
        // $month = $request->input('month', 0); // Default to current month if not provided
        // $year = $request->input('year', Carbon::now()->year); // Default to current year if not provided
        
        // Get input parameters from the request
$month = $request->input('month', date('m')); // Default to current month if not provided
$year = $request->input('year', date('Y')); // Default to current year if not provided

$startDate = Carbon::createFromDate($year, $month)->startOfMonth();
$endDate = Carbon::createFromDate($year, $month)->endOfMonth();

// Query for the summary
$summary = Sales::whereBetween('date', [
        $startDate->toDateTimeString(),
        $endDate->toDateTimeString()
    ])
    ->selectRaw('COUNT(*) as sales_count, SUM(total_amount) as total_amount, SUM(tax_amount) as tax_amount, SUM(paid_amount) as total_paid_amount')
    ->first();

// Count of unique customers with transactions in this month
$countUniqueCustomersThisMonth = Sales::whereBetween('date', [
        $startDate->toDateTimeString(),
        $endDate->toDateTimeString()
    ])
    ->distinct('customer_name')
    ->count('customer_name');

// Total sales for this month
$thisMonthDataSales = Sales::whereBetween('date', [
        $startDate->toDateTimeString(),
        $endDate->toDateTimeString()
    ])
    ->selectRaw('SUM(total_amount) as total_amount')
    ->first();

// Total quantity of products sold this month
$totalProductsThisMonth = SalesDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
        $query->whereBetween('date', [
            $startDate->toDateTimeString(),
            $endDate->toDateTimeString()
        ]);
    })
    ->selectRaw('SUM(quantity) as total_quantity')
    ->first();


        
        // Build the data array
        $data = [
            // 'total_amount_saler' => $summarySaler->total_amount ?? 0,
            'total_sales_count' => floatval($summary->sales_count),
            'total_customers_last_month' => floatval($countUniqueCustomersThisMonth) ?? 0,
            'total_products_sold' => floatval($totalProductsThisMonth->total_quantity) ?? 0,
            'total_last_month' => floatval($thisMonthDataSales->total_amount) ?? 0,
            // 'tax_amount' => $summary->tax_amount ?? 0,
           
        ];
    $param=[
        'month' => $month,
            'year' => $year,
    ] ;
        return response()->json([
            'success' => true,
            'message' => 'Data Summary Dashboard',
            'data' => $data,
            'filters'=>$param
        ]);
    }
    public function getSummarySaler($id, Request $request)
    {
        // Get input parameters from the request
        $month = $request->input('month', 0); // Default to current month if not provided
        $year = $request->input('year', Carbon::now()->year); // Default to current year if not provided
        
        // Calculate the start and end dates based on the input
        $startDate = Carbon::create($year, Carbon::now()->month, 1)->subMonths($month)->startOfMonth();
        $endDate = Carbon::create($year, Carbon::now()->month, 1)->endOfMonth();
    
        // Get the seller code
        $codeSeller = Saler::where('user_id', $id)->pluck('Kode')->first();
        
        // Query for the summary
        $summary = Sales::whereBetween('date', [
                $startDate->toDateTimeString(),
                $endDate->toDateTimeString()
            ])
            ->where('kode_salesman', $codeSeller)
            ->selectRaw('COUNT(*) as sales_count, SUM(total_amount) as total_amount, SUM(tax_amount) as tax_amount, SUM(paid_amount) as total_paid_amount')
            ->first();
    
        // Count of unique customers with transactions last month
        $countUniqueCustomersLastMonth = Sales::whereBetween('date', [
                $startDate->startOfMonth()->toDateTimeString(),
                $startDate->endOfMonth()->toDateTimeString()
            ])
            ->where('kode_salesman', $codeSeller)
            ->distinct('customer_name')
            ->count('customer_name');
        
        // Total sales for the last month
        $lastMonthDataSales = Sales::whereBetween('date', [
                $startDate->startOfMonth()->toDateTimeString(),
                $startDate->endOfMonth()->toDateTimeString()
            ])
            ->where('kode_salesman', $codeSeller)
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();
    
        // Total quantity of products sold
        $totalProductsLastMonth = SalesDetail::whereHas('sale', function ($query) use ($startDate) {
                $query->whereBetween('date', [$startDate->startOfMonth()->toDateTimeString(), $startDate->endOfMonth()->toDateTimeString()]);
            })
            ->selectRaw('SUM(quantity) as total_quantity')
            ->first();
        
        // Build the data array
        $data = [
            // 'total_amount_saler' => $summarySaler->total_amount ?? 0,
            'total_sales_count' => floatval($summary->sales_count),
            'total_customers_last_month' => floatval($countUniqueCustomersLastMonth) ?? 0,
            'total_products_sold' => floatval($totalProductsLastMonth->total_quantity) ?? 0,
            'total_last_month' => floatval($lastMonthDataSales->total_amount) ?? 0,
            // 'tax_amount' => $summary->tax_amount ?? 0,
           
        ];
    $param=[
        'month' => $month,
            'year' => $year,
    ] ;
        return response()->json([
            'success' => true,
            'message' => 'Data Summary Dashboard',
            'data' => $data,
            'filters'=>$param
        ]);
    }
    public function profitLastMonth(Request $request)
    {
        $month = $request->input('month', date('m')); // Default to current month if not provided
        $year = $request->input('year', Carbon::now()->year); // Default to current year if not provided
        
        // Calculate the start and end dates based on the input month and year
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Total sales for the current month
        $currentMonthDataSales = Sales::whereBetween('date', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();
        
        // Total sales for the previous month
        $previousMonth = $startDate->copy()->subMonth();
        $previousMonthDataSales = Sales::whereBetween('date', [$previousMonth->startOfMonth()->toDateTimeString(), $previousMonth->endOfMonth()->toDateTimeString()])
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();
        
        // Create data array
        $data = [
            'total_profit' => floatval($currentMonthDataSales->total_amount ?? 0),
            'total_increase' => isset($previousMonthDataSales->total_amount) && $previousMonthDataSales->total_amount != 0
                ? ($currentMonthDataSales->total_amount - $previousMonthDataSales->total_amount)
                : 0,
            'percentage_increase' => isset($previousMonthDataSales->total_amount) && $previousMonthDataSales->total_amount != 0
                ? (($currentMonthDataSales->total_amount - $previousMonthDataSales->total_amount) / $previousMonthDataSales->total_amount) * 100
                : 0,
        ];
        
        $param = [
            'month' => $month,
            'year' => $year,
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Data Summary Dashboard: Profit Last Month',
            'data' => $data,
            'filters' => $param,
        ]);
        
    }
    public function weeklyProfitLastMonth(Request $request)
    {

        $month = $request->input('month', 0); // Default to current month if not provided
        $year = $request->input('year', Carbon::now()->year); // Default to current year if not provided
        
        // Calculate the start and end dates based on the input
        // Get the second last month
        $lastMonth = Carbon::create($year, Carbon::now()->month, 1)->subMonths($month)->startOfMonth();
        $endOfMonth = Carbon::create($year, Carbon::now()->month, 1)->endOfMonth();
    
        // $lastMonth = Carbon::now()->subMonth();

// Get the start and end of the last month
$startOfMonth = $lastMonth->toDateTimeString();
$endOfMonth = $endOfMonth->toDateTimeString();

// Initialize an array with default values for each week
$weeklyData = array_fill(0, 4, 0);

// Weekly purchases for the last month
$lastMonthDataPurchasesWeekly = Purchase::whereBetween('date', [$startOfMonth, $endOfMonth])
    ->selectRaw('SUM(total_amount) as total_amount, WEEK(date) as week_number')
    ->groupBy('week_number')
    ->get();

// Update the array with actual data for each week
foreach ($lastMonthDataPurchasesWeekly as $purchase) {
    $weekNumber = $purchase->week_number;
    $weeklyData[$weekNumber - 1] += $purchase->total_amount; // Adjusting index to start from 0
}

// Weekly sales for the last month
$lastMonthDataSalesWeekly = Sales::whereBetween('date', [$startOfMonth, $endOfMonth])
    ->selectRaw('SUM(total_amount) as total_amount, WEEK(date) as week_number')
    ->groupBy('week_number')
    ->get();

// Update the array with actual data for each week
foreach ($lastMonthDataSalesWeekly as $sale) {
    $weekNumber = $sale->week_number;
    $weeklyData[$weekNumber - 1] = ($weeklyData[$weekNumber - 1] ?? 0) + $sale->total_amount; // Adjusting index to start from 0
}

// Calculate profit for each week
$profitLastMonthWeekly = array_sum($weeklyData);

$data = [
    'profit_last_month_weekly' => $profitLastMonthWeekly,
    'weekly_data' => array_values($weeklyData), // Resetting keys to start from 0
];
$param=[
    'month' => $month,
        'year' => $year,
] ;

return response()->json([
    'success' => true,
    'message' => 'Data Summary Dashboard',
    'data' => $data,
    'filters' => $param,
]);

    }
    public function totalAmount(Request $request)
    {
        // Get input parameters from the request
        $month = $request->input('month', 0); // Default to current month if not provided
        $year = $request->input('year', Carbon::now()->year); // Default to current year if not provided
        
        // Calculate the start and end dates based on the input
        $lastMonth =Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, Carbon::now()->month, 1)->endOfMonth();
    
        $start = Carbon::create($year, Carbon::now()->month, 1)->subMonths($month)->startOfMonth();
        $end = Carbon::create($year, Carbon::now()->month, 1)->endOfMonth();
    
        // Query for the summary
        $secondLastMonthDataSales = Sales::whereBetween('date', [
            $start->toDateTimeString(),
            $end->toDateTimeString()
        ])
    ->selectRaw('SUM(total_amount) as total_amount')
    ->first();

// Total sales for the last month
$lastMonthDataSales = Sales::whereBetween('date', [
        $lastMonth->startOfMonth()->toDateTimeString(),
        $lastMonth->endOfMonth()->toDateTimeString()
    ])
    ->selectRaw('SUM(total_amount) as total_amount')
    ->first();

$summary = Sales::whereBetween('date', [
        $lastMonth->startOfMonth()->toDateTimeString(),
        $lastMonth->endOfMonth()->toDateTimeString()
    ])
    ->join('saler', 'sales.kode_salesman', '=', 'saler.id')
    ->join('users', 'saler.user_id', '=', 'users.id')
    ->selectRaw('users.role_id, SUM(sales.total_amount) as total_amount')
    ->groupBy('users.role_id')
    ->get();

$data = $summary->pluck('total_amount', 'role_id')->all();
$totalAmount = floatval($lastMonthDataSales->total_amount);

$firstPortion = $totalAmount * 0.5; // 50%
$remainingAmount = $totalAmount * 0.5; // 50% left for remaining portions

$portion1 = $firstPortion;
$portion2 = $remainingAmount * 0.2; // 20%
$portion3 = $remainingAmount * 0.2; // 20%
$portion4 = $remainingAmount * 0.1; // 10%

$total_amount = [$portion1, $portion2, $portion3, $portion4];

// Initialize an array with default values
$newData = [
    'total_profit' => floatval($lastMonthDataSales->total_amount) ?? 0,
    'percentage_increase' => isset($secondLastMonthDataSales->total_amount) && $secondLastMonthDataSales->total_amount != 0
        ? (($lastMonthDataSales->total_amount - $secondLastMonthDataSales->total_amount) / $secondLastMonthDataSales->total_amount) * 100
        : 0,
    'total_amount' => $total_amount,
];

// Assign values based on role_id
foreach ($data as $roleId => $totalAmount) {
    switch ($roleId) {
        case 4:
            $newData['total_amount'][0] = $totalAmount;
            break;
        case 5:
            $newData['total_amount'][1] = $totalAmount;
            break;
        case 6:
            $newData['total_amount'][2] = $totalAmount;
            break;
        case 7:
            $newData['total_amount'][3] = $totalAmount;
            break;
        // Add additional cases if needed for other role_id values
    }
}

$data = $newData; // Remove the outer array to make $data a direct object

// Now $data is a direct object

$param=[
    'month' => $month,
        'year' => $year,
] ;
    
        return response()->json([
            'success' => true,
            'message' => 'Data Summary Dashboard',
            'data' => $data,
            'filters' => $param,
        ]);
    }
    public function getSaleTax($currentYear)
    {
        $monthlySalesData = Sales::whereYear('date', $currentYear)
        ->selectRaw('MONTH(date) as month, SUM(total_amount) as total_amount, SUM(tax_amount) as tax_amount')
        ->groupBy('month')
        ->get();
    
    // Initialize arrays for 12 months with default value 0
    $totalAmountData = array_fill(0, 12, 0);
    $taxAmountData = array_fill(0, 12, 0);
    
    foreach ($monthlySalesData as $monthlyData) {
        $totalAmountData[$monthlyData->month - 1] = $monthlyData->total_amount;
        $taxAmountData[$monthlyData->month - 1] = $monthlyData->tax_amount;
    }
    
    $data = [
        'total_amount' => $totalAmountData,
        'tax_amount' => $taxAmountData,
    ];
    // $param=[
    //     'month' => $month,
    //         'year' => $year,
    // ] ;
    
        return response()->json([
            'success' => true,
            'message' => 'Data Tax & Amount',
            'data' => $data,
            // 'filters' => $param,
        ]);

    }

public function getTopSellersData(Request $request)
{  
 // Get input parameters from the request
$month = $request->input('month', 0); // Default to current month if not provided
$year = $request->input('year', Carbon::now()->year); // Default to current year if not provided

$topSalers = DB::table('sales')
    ->join('saler', 'sales.kode_salesman', '=', 'saler.Kode')
    ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
    ->selectRaw('
        saler.Kode,
        saler.Nama,
        COUNT(sales.id) as sales_count,
        SUM(sales.total_amount) as total_amount_sum,
        SUM(sale_details.quantity) as product_quantity_sum
    ')
    ->whereYear('sales.date', $year) // Filter by year
    ->when($month, function ($query) use ($month) {
        return $query->whereMonth('sales.date', $month); // Filter by month if provided
    })
    ->groupBy('saler.Kode','saler.Nama')
    ->orderByDesc('sales_count')
    ->orderByDesc('total_amount_sum')
    ->orderByDesc('product_quantity_sum')
    ->take(5)
    ->get();


$data = $topSalers->map(function ($saler) {
    // Assign weights to each criterion
    $weightSalesCount = 2;
    $weightTotalAmount = 1.5;
    $weightProductQuantity = 1;

    // Calculate an overall score
    $overallScore = ($weightSalesCount * $saler->sales_count)
                  + ($weightTotalAmount * $saler->total_amount_sum)
                  + ($weightProductQuantity * $saler->product_quantity_sum);

    return [
        'code' => $saler->Kode,
        'name' => $saler->Nama,
        'total_sales' => $saler->sales_count,
        'total_amount' => floatval($saler->total_amount_sum),
        'product_quantity' => floatval($saler->product_quantity_sum),
        // 'role_name' => $saler->role_name,
        'overall_score' => floatval($overallScore),
    ];
})->all();

// Sort by overall score in descending order
usort($data, function ($a, $b) {
    return $b['overall_score'] <=> $a['overall_score'];
});
$param=[
    'month' => $month,
        'year' => $year,
] ;
return response()->json([
    'success' => true,
    'message' => 'Top 5 salers with calculated overall scores.',
    'data' => array_slice($data, 0, 10), // Take the top 10 based on overall score
    'filters'=> $param
]);

}

    public function getTopProducts(Request $request)
    {
   // Get input parameters from the request
$month = $request->input('month', 0); // Default to current month if not provided
$year = $request->input('year', Carbon::now()->year); // Default to current year if not provided

$topProducts = SalesDetail::join('sales', 'sales.id', '=', 'sale_details.sale_id')
    ->select('sale_details.product_name')
    ->selectRaw('COUNT(*) as frequency')
    ->selectRaw('SUM(sale_details.quantity) as total_quantity')
    ->when($month, function ($query) use ($month, $year) {
        return $query->whereMonth('sales.date', $month)
            ->whereYear('sales.date', $year);
    })
    ->groupBy('sale_details.product_name')
    ->orderByDesc('frequency')
    ->orderByDesc('total_quantity')
    ->take(5) // Take the top 5 products
    ->get();


   // Retrieve additional fields from the Product model
$productDetails = Product::whereIn('product_name', $topProducts->pluck('product_name')->toArray())
->select('id', 'category_id', 'product_name', 'product_code', 'image', 'product_unit', 'product_size', 'product_price')
->get();

// Combine the product details with the top products
$result = $topProducts->map(function ($topProduct) use ($productDetails) {
$productDetail = $productDetails->where('product_name', $topProduct->product_name)->first();

return [
    'product' => [
        'id' => $productDetail ? $productDetail->id : null,
        'name' => $productDetail ? $productDetail->product_name : $topProduct->product_name,
        'code' => $productDetail ? $productDetail->product_code : null,
        'image' => $productDetail ? $productDetail->image : null,
        'uom' => $productDetail ? $productDetail->product_unit : null,
        'size' => $productDetail ? $productDetail->product_size : null,
        'price' => $productDetail ? $productDetail->product_price : null,
    ],
    'frequency' => floatval($topProduct->frequency),
    'total_quantity' => floatval($topProduct->total_quantity),
];
});

$param=[
    'month' => $month,
        'year' => $year,
] ;
    return response()->json([
        'success' => true,
        'message' => 'Top 5 products based on frequency and total quantity with additional details.',
        'data' => $result,
        'fikters' => $param,
    ]); }
    public function getTopCustomer(Request $request)
    {
    // Get input parameters from the request
$month = $request->input('month', 0); // Default to current month if not provided
$year = $request->input('year', Carbon::now()->year); // Default to current year if not provided

$bestCustomers = Sales::select('customer_name')
    ->selectRaw('COUNT(*) as frequency')
    ->selectRaw('SUM(total_amount) as total_amount_sum')
    ->selectRaw('SUM(sale_details.quantity) as total_quantity_sum')
    ->leftjoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
    ->when($month, function ($query) use ($month, $year) {
        return $query->whereMonth('sales.date', $month)
            ->whereYear('sales.date', $year);
    })
    ->groupBy('customer_name')
    ->orderByDesc('frequency')
    ->orderByDesc('total_amount_sum')
    ->orderByDesc('total_quantity_sum')
    ->take(5)
    ->get();

    $bestCustomersWithDetails = $bestCustomers->map(function ($customer) {
        $customerInfo = Customer::where('customer_name', $customer->customer_name)->first();
    
        return [
            'customer' => [
                'id' => $customerInfo ? $customerInfo->id : null,
                'name' => $customerInfo ? $customerInfo->customer_name : $customer->customer_name,
                'email' => $customerInfo ? $customerInfo->customer_email : null,
                'city' => $customerInfo ? $customerInfo->city : null,
                'address' => $customerInfo ? $customerInfo->address : null,
            ],
            'frequency' => $customer->frequency,
            'total_amount' => floatval($customer->total_amount_sum),
            'total_quantity' => floatval($customer->total_quantity_sum),
        ];
    });
    
        $param=[
            'month' => $month,
                'year' => $year,
        ] ;
    
        return response()->json([
            'success' => true,
            'message' => 'Top 5 customers with calculated overall scores and customer details.',
            'data' => $bestCustomersWithDetails,
            'filters' => $param,
        ]);
    }
}

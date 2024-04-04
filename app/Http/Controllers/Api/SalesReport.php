<?php

namespace App\Http\Controllers\API;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\ReportSource;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
class SalesReport extends Controller
{
    //
    public function salesTax(Request $request)
    {
        try {
            $perPage = $request->input('perPage', 10);
            $search = $request->input('search');
            $filter = $request->all();
    
            $salesQuery = Sales::query()->select('id', 'date', 'reference', 'kode_depo', 'kode_salesman', 'customer_name', 'total_amount', 'tax_amount');
    
            // Apply search filter if provided
            if ($search) {
                $salesQuery->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%$search%")
                          ->orWhere('customer_name', 'like', "%$search%")
                          ->orWhere('kode_depo', 'like', "%$search%");
                });
            }
    
            // Apply other filters
            $salesQuery = $this->applyFilters($salesQuery, $request);
    
            // Get total amounts
            $sumTotalAmount = $salesQuery->sum('sub_total');
            $sumTaxAmount = $salesQuery ->sum('tax_amount');
         $exportFormat = $request->input('exports');
          
          if($exportFormat){
              
          $filteredSales = $salesQuery->get();

            $transformedDataExports = [];
            
            // Transform each sale item
            foreach ($filteredSales as $sale) {
                $transformedSale = [
                    "date" => ($sale->date), // Convert date to milliseconds
                    "reference" => $sale->reference,
                    "depo" => $sale->kode_depo,
                    "sales" => $sale->kode_salesman,
                    "customer" => $sale->customer_name,
                    "total_amount" => $sale->total_amount,
                    "tax_amount" => $sale->tax_amount,
                ];
                $transformedDataExports[] = $transformedSale;
            }
            $sum = [
                "sum_total_amount" => $sumTotalAmount,
                "sum_tax_amount" => $sumTaxAmount,
            ];
              // Check if export requested
       
        if ($exportFormat === 'csv') {
            return $this->exportSalesTaxAsCsv($transformedDataExports);
        } elseif($exportFormat === 'json') {
            return $this->exportSalesTaxAsJSON($transformedDataExports,$sum);
        } 
          }  
            // Paginate results
            $sales = $salesQuery->paginate($perPage);
    
            // Transform data
            $transformedData = [];
            foreach ($sales->items() as $sale) {
                $transformedSale = [
                    "id" => strval($sale->id),
                    "reference" => $sale->reference,
                    "customer_name" => $sale->customer_name,
                    "date" => strtotime($sale->date) * 1000,
                    "total_amount" => $sale->total_amount,
                    "sales" => $sale->kode_salesman,
                    "depo" => $sale->kode_depo,
                    "tax_amount" => $sale->tax_amount,
                ];
                $transformedData[] = $transformedSale;
            }
            $transformedSalesData = new LengthAwarePaginator(
                $transformedData,
                $sales->total(),
                $sales->perPage(),
                $sales->currentPage(),
                ['path' => url('/api/reports-sales-tax')]
            );
            // Prepare response
            $resource = new ReportSource(true, 'List of Sales and Tax Reports', $transformedSalesData, [
                "sum_total_amount" => $sumTotalAmount,
                "sum_tax_amount" => $sumTaxAmount,
            ], $filter);
    
            return $resource;
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while processing your request.'], 500);
        }
    }
    
    private function applyFilters($query, $request)
    {
        $filters = [
            'customer_name',
            'reference',
            'kode_depo' => 'depo',
            'kode_salesman' => 'seller',
            'payment_status',
            'total_amount',
            'status',
            'date',
            ['total_amount' => ['min', 'max']],
            ['date' => ['start_date', 'end_date']]
        ];
    
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $field => $param) {
                    if ($request->filled($param)) {
                        if ($field === 'date' && is_array($request->input($param))) {
                            $query->whereBetween($field, $request->input($param));
                        } else if ($request->has('start_date') && $request->has('end_date')) {
                            $startDate = $request->input('start_date');
                            $endDate = $request->input('end_date');
                            
                            $query->whereBetween('date', [$startDate, $endDate]);
                        }else {
                            $query->where($field, $request->input($param));
                        }
                    }
                }
            } else {
                $field = is_numeric($key) ? $value : $key;
                $param = is_numeric($key) ? $value : $value;
                if ($request->filled($param)) {
                    $query->where($field, $request->input($param));
                }
            }
        }
    
        return $query;
    }
    
    public function exportSalesTax(Request $request)
    {
        // Retrieve filtered data using the salesTax method
        $filteredSales = $this->salesTax($request)->getData()->data;
    
        // Initialize CSV content
        $csv = "ID,Reference,Customer Name,Date,Total Amount,Sales,Depo,Tax Amount\n";
    
        // Add each sale data to CSV content
        foreach ($filteredSales as $sale) {
            $csv .= "{$sale->id},{$sale->reference},{$sale->customer_name},{$sale->date},{$sale->total_amount},{$sale->sales},{$sale->depo},{$sale->tax_amount}\n";
        }
    
        // Generate a unique file name
        $fileName = 'sales_report_' . date('YmdHis') . '.csv';
    
        // Return CSV file as downloadable response
        return Response::make($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=' . $fileName);
    }
    
    private function exportSalesTaxAsCsv($transformedData)
    {
        // Initialize CSV content
        $csv = "Date,Reference,Depo,Sales,Customer,Total, Pajak\n";

        // Add each sale data to CSV content
        foreach ($transformedData as $sale) {
            $csv .= "{,{$sale['date']},{$sale['reference']},{$sale['depo']},{$sale['sales']},{$sale['customer']},{$sale['total_amount']},{$sale['tax_amount']}\n";
        }

        // Generate a unique file name
        $fileName = 'sales_report_' . date('YmdHis') . '.csv';

        // Return CSV file as downloadable response
        return response()->streamDownload(
            function () use ($csv) {
                echo $csv;
            },
            $fileName,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }
    private function exportSalesTaxAsJSON($transformedData,$sum)
    {
        return response()->json([
            'success' => true,
            'message' => 'List of Sales and Tax',
            'data' => $transformedData,
            'sum'=> $sum
        ]);
    }
}

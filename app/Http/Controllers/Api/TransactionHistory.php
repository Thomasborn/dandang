<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionHistory extends Controller
{
    public function getAll(){
        $sales = Sales::paginate(10)->toArray();
        foreach ($sales['data'] as &$sale) {
            $sale['depo'] = $sale['kode_depo'];
            $sale['saler'] = $sale['kode_salesman'];
        
            // Remove the old keys
            unset($sale['kode_depo']);
            unset($sale['kode_salesman']);
            unset($sale['created_at']);
            unset($sale['updated_at']);
        }
        return new AllResource(true, 'List of Transactions', $sales);
        // $sales = Sales::where('id', '=', $id)->with('customer', 'depo', 'saler', 'saleDetails')->get();

        if (!$sales) {
            return new AllResource(false, 'Sales not found!', null);
            
        }
        $sales->makeHidden(['created_at', 'updated_at']);
        foreach ($sales as $sale) {
            $sale->makeHidden(['created_at', 'updated_at']);
        
            if ($sale->depo) {
                $sale->depo->makeHidden(['created_at', 'updated_at', 'deleted_at', 'user_id', 'alamat']);
            }
        
            if ($sale->saler) {
                $sale->saler->makeHidden(['created_at', 'updated_at', 'deleted_at', 'user_id', 'alamat']);
            }
        
            if ($sale->saleDetails) {
                $sale->saleDetails->makeHidden(['created_at', 'updated_at', 'sale_id', 'id']);
            }
        
            if ($sale->customer) {
                $sale->customer->makeHidden(['created_at', 'updated_at']);
                $this->renameAndUnset($sale->customer, 'customer_name', 'name');
                $this->renameAndUnset($sale->customer, 'customer_email', 'email');
                $this->renameAndUnset($sale->customer, 'customer_phone', 'contact');
            }
        }
        
        return new AllResource(true, 'Sales details', $sales);
    }
    public function transactionPending(){
        $sales = Sales::where('status', '!=', 'completed')->paginate(10);


        $transformedData = [];
        
        foreach ($sales->items() as $sale) {
            $transformedSale = [
                "id" => $sale["id"],
                "reference" => $sale["reference"],
                "customer_name" => $sale["customer_name"],
                "date" => strtotime($sale["date"]) * 1000, // Convert date to milliseconds
                "status" => [
                    "code" => ($sale["status"] === "completed") ? 2 : 1,
                    "status" => ($sale["status"] === "completed") ? "selesai" : "belum selesai",
                ],
                "total_amount" => $sale["total_amount"],
                "sales" => $sale["kode_salesman"],
                "depo" => $sale["kode_depo"],
                "payment_status" => [
                    "code" => ($sale["payment_status"] === "Paid") ? 2 : 1,
                    "status" => ($sale["payment_status"] === "Paid") ? "lunas" : "parsial",
                ],
            ];
        
            $transformedData[] = $transformedSale;
        }
        
        $transformedSalesData = new LengthAwarePaginator(
            $transformedData,
            $sales->total(),
            $sales->perPage(),
            $sales->currentPage(),
            ['path' => url('/api/transactions')]
        );
        return new AllResource(true, 'List of Transactions', $transformedSalesData);
    }
}

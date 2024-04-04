<?php

namespace App\Http\Controllers\API;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\FilterResource;
use App\Http\Resources\PostResource;
use App\Models\Customer;
use App\Models\Depo;
use App\Models\Product;
use App\Models\ProductSaler;
use App\Models\Saler;
use App\Models\Sales;
use App\Models\SalesDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {
        $perPage = $request->input('perPage', 10);
        $filter = $request->all();

        $salesQuery = Sales::query()->select('id', 'date', 'reference', 'kode_depo', 'kode_salesman', 'customer_name', 'total_amount', 'tax_amount');

        // Apply filters
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
                            $salesQuery->whereBetween($field, $request->input($param));
                        } else {
                            $salesQuery->where($field, $request->input($param));
                        }
                    }
                }
            } else {
                $field = is_numeric($key) ? $value : $key;
                $param = is_numeric($key) ? $value : $value;
                if ($request->filled($param)) {
                    $salesQuery->where($field, $request->input($param));
                }
            }
        }

        // Paginate results
        $sales = $salesQuery->paginate($perPage);

        // Transform paginated data
        $transformedData = [];
        foreach ($sales->items() as $sale) {
            $transformedSale = [
                "id" => strval($sale->id),
                "reference" => $sale->reference,
                "customer_name" => $sale->customer_name,
                "date" => strtotime($sale->date) * 1000,
                'status' => (object) [
                    'code' => 1,
                    'status' => "selesai",
                ],
                "total_amount" => $sale->total_amount,
                "sales" => $sale->kode_salesman,
                "depo" => $sale->kode_depo,
                'payment_status' => (object) [
                    'code' => 1,
                    'status' => "lunas",
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

        // Return paginated and transformed data
        return new FilterResource(true, 'List of Transactions', $transformedSalesData, $filter);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'An error occurred while processing your request.'], 500);
    }
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
private function exportSalesTaxAsJSON($transformedData)
{
    return response()->json([
        'success' => true,
        'message' => 'List of Sales and Tax',
        'data' => $transformedData,
      
    ]);
}
    private function isAllowedRoleSaler($roleId) {
        return $roleId == 5 || $roleId == 6;
    }
    private function updateProductQuantity($product, $amount) {
        $product->product_quantity -= $amount;
        $product->save();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        try {

        // $validatedData = $request->validate([
        //     'saler_id' => 'required|integer',
        //     'customer_id' => 'integer', // customer_id is optional, but if present, it should be an integer
        //     'new_customer' => 'required_without:customer_id|array', // new_customer is required if customer_id is not present
        //     'new_customer.name' => 'required_without:customer_id|string',
        //     'new_customer.contact' => 'required_without:customer_id|string',
        //     'new_customer.address' => 'required_without:customer_id|string',
        //     'new_customer.city' => 'required_without:customer_id|string',
        //     'depo_id' => 'required|integer',
        //     'products' => 'required|array|min:1',
        //     'products.*.id' => 'required|integer',
        //     'products.*.amount' => 'required|integer|min:1',
        //     'payment_method' => 'required|string',
        //     // 'due' => 'required|string',
        //     'tax.ppn' => 'required|integer',
        //     'tax.amount' => 'required|integer',
        //     // 'discount.disc' => 'required|integer',
        //     // 'discount.amount' => 'required|integer',
        // ]);
        $validatedData = $request->validate([
            'user_id' => 'required_without:saler_id|integer',
            'saler_id' => 'sometimes|nullable|integer',
            'customer_id' => 'integer',
            'new_customer' => 'required_without:customer_id|array',
            'new_customer.name' => 'required_without:customer_id|string',
            'new_customer.contact' => 'required_without:customer_id|string',
            'new_customer.address' => 'required_without:customer_id|string',
            'new_customer.city' => 'required_without:customer_id|string',
            'depo_id' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|integer',
            'products.*.amount' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'tax.ppn' => 'required|integer',
            'tax.amount' => 'required|integer',
        ]);
        
        
        
     } catch (ValidationException $e) {
            // Validation failed
            $errors = $e->errors();
            return response()->json(['error' => $errors], 422);
        }
        try{
            DB::beginTransaction();

            // Use optional() to avoid errors if 'user_id' or 'saler_id' is not set
            $saler = Saler::where('user_id', optional($request)['user_id'])
                          ->orWhere('id', optional($request)['saler_id'])
                          ->first();
        
            $depo = Depo::where('id', '=', $request['depo_id'])->first();
        
            // The rest of your code...
        
        
            
            // Create or retrieve customer
            $customer = $this->createOrUpdateCustomer($request);
            
            // Generate reference
            $reference = $depo->Kode . "INVADM" . mt_rand(1000, 9999);

            // Determine status
            $status = ($request->due === null) ? 'completed' : 'pending';
            
       
       
       
       
            
            // Create Sales instance
            $sale = Sales::create([
                'date' => now(),
                'reference' => $reference,
                'customer_id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'tax_percentage' => $validatedData['tax']['ppn'] ?? 0,
                'kode_depo' => $depo->Kode,
                'kode_salesman' => $saler->Kode,
                'tax_amount' => 0,
                'discount_percentage' => $validatedData['discount']['disc'] ?? 0,
                'discount_amount' => $validatedData['discount']['amount'] ?? 0,
                'shipping_amount' => $validatedData['shipping_amount'] ?? 0,
                'total_amount' => 0,
                'sub_total' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'due' => $validatedData['due'] ?? null,
                'status' => $status,
                'payment_status' => 'unpaid',
                'payment_method' => $validatedData['payment_method'] ?? null,
                'note' => $validatedData['note'] ?? null,
            ]);
                     

            // Calculate total amounts
            $totalAmount = 0;
            $totalTax = 0;
        
            // Create SalesDetail instances
            foreach ($validatedData['products'] as $productData) {
                $product = Product::find($productData['id']);
                $unitPrice = $product->product_price;
        
                $saleDetail = $this->createSalesDetail($product, $unitPrice, $productData, $validatedData,$sale);
                
                
                
                if ($request->has('saler_id')) {
                    // Retrieve the Saler model based on the saler_id
                    $role = Saler::with('user')->where('id', $request->saler_id)->first();
                    // return new PostResource(false, 'saler_id dan usr_id', $role);

                } elseif (isset($saler) && $saler instanceof Saler) {
                    // Use the existing $saler object
                    $role = Saler::with('user')->where('id', $saler->id)->first();
                    // return new PostResource(false, 'saler_id dan usr_id1', $role);

                } else {
                    // Handle the case when neither 'saler_id' is present nor $saler is set
                    $role = null; // Adjust this line based on your requirements
                    return new PostResource(false, 'saler_id dan user_id', $role);

                }
                
                
              
                
                if ($role && $role->user) {
                    $roleId = $role->user->role_id;
                
                    if ($this->isAllowedRoleSaler($roleId)) {
                        $productSaler = ProductSaler::where('product_id', $productData['id'])
                            ->where('saler_code', $role->Kode)
                            ->first();
                
                        if ($productSaler) {
                            $this->updateProductQuantity($productSaler, $productData['amount']);

                        } else {
                            // Handle the case when ProductSaler is not found
                            // You can throw an exception, log an error, or take any appropriate action
    return new PostResource(false, 'ProductSaler is not found', $product);

                        }
                    } else {
                        // Fetch or define the $product object before using it
                        $product = Product::find($productData['id']);

                        $this->updateProductQuantity($product, $productData['amount']);
                    }
                } else {
                    // Handle the case when Saler or User is not found
                    // You can throw an exception, log an error, or take any appropriate action
    return new PostResource(false, 'Saler is not found', $saler);

                }
                
                
                $totalAmount += $saleDetail->sub_total;
                $totalTax += $saleDetail->product_tax_amount;
        
                $sale->saleDetails()->save($saleDetail);
                $saleDetail->product()->associate($product);
                
            }
        
            // Update total amounts
            $sale->sub_total = $totalAmount;
            $sale->tax_amount = $totalTax;
            $sale->total_amount = $totalAmount+$totalTax;
        
            $sale->save();
            DB::commit();
           
            $sale_invoice = Sales::with('customer', 'depo', 'saler','saleDetails')->find($sale->id);
           
            $sale_invoice->makeHidden(['created_at', 'updated_at']);
           
            
                if ($sale_invoice->depo) {
                    $sale_invoice->depo->makeHidden(['created_at', 'updated_at', 'deleted_at', 'user_id', 'alamat']);
                }
            
                if ($sale_invoice->saler) {
                    $sale_invoice->saler->makeHidden(['created_at', 'updated_at', 'deleted_at', 'user_id', 'alamat']);
                }
            
                if ($sale_invoice->saleDetails) {
                    $sale_invoice->saleDetails->makeHidden(['created_at', 'updated_at', 'sale_id', 'id']);
                }
            
                if ($sale_invoice->customer) {
                    $sale_invoice->customer->makeHidden(['created_at', 'updated_at']);
                    $this->renameAndUnset($sale_invoice->customer, 'customer_name', 'name');
                    $this->renameAndUnset($sale_invoice->customer, 'customer_email', 'email');
                    $this->renameAndUnset($sale_invoice->customer, 'customer_phone', 'contact');
                }
if ($sale_invoice) {
    return new PostResource(true, 'Sales created successfully', $sale_invoice);

} else {
    return response()->json([
        'status' => 'error',
        'message' => 'Sales invoice not found',
    ], 404);
}

            // Return the created Sales as a resource
            // return new AllResource(true, 'Sales created successfully', $sale_invoice);
        } catch (Exception $e) {
            // Handle exceptions
            DB::rollBack();

            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        
        // Helper functions
        private function createOrUpdateCustomer($request) {
            if (!$request->has('customer_id') || $request->input('customer_id') == null) {
                $existingCustomer = Customer::where('customer_name', $request->new_customer['name'])
                    ->where('address', $request->new_customer['contact'])
                    ->first();
        
                if (!$existingCustomer) {
                    return Customer::create([
                        'customer_name' => $request->new_customer['name'],
                        'address' => $request->new_customer['address'],
                        'contact' => $request->new_customer['contact'],
                        'city' => $request->new_customer['city'],
                    ]);
                } else {
                    throw new Exception('Customer with the same name and address number already exists.');
                }
            } else {
                return Customer::find($request->customer_id);
            }
        }
        
        private function createSalesDetail($product, $unitPrice, $productData, $validatedData,$sale) {
            return SalesDetail::create([
                'sale_id'=>$sale->id,
                'quantity' => $productData['amount'],
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'price' => $unitPrice * $productData['amount'],
                'unit_price' => $unitPrice,
                'sub_total' => ($productData['amount'] * $unitPrice) + ($validatedData['tax']['ppn'] * $unitPrice * $productData['amount']),
                'product_discount_amount' => $validatedData['discount']['amount'] ?? 0,
                'product_discount_type' => 'percentage',
                'dpp' => $productData['amount'] * $unitPrice,
                'product_tax_amount' => ($validatedData['tax']['ppn'] * $unitPrice * $productData['amount']),
            ]);
        }

    /**
     * Display the specified resource.
     */

    private function renameAndUnset($object, $oldProperty, $newProperty) {
        $object->$newProperty = $object->$oldProperty;
        unset($object->$oldProperty);
    }
    public function show($id)
    {

        $sales = Sales::where('id', '=', $id)
        ->with('customer', 'depo', 'saler.user.role', 'saleDetails')
        ->get();
    
    if ($sales->isEmpty()) {
       return response()->json([
            'success' => false,
       
          
                'message' => 'Sale / Transaction not found',
            
        ], 404);
        
    }
    
    $transformedData = $sales->map(function ($sale) {
        $bill = ($sale->due_amount == 0 || is_null($sale->due_amount) || $sale->due_amount === $sale->paid_amount) ? 0 : ($sale->due_amount - $sale->paid_amount);
        $roleTypes = [
            4 => 'TO',
            5 => 'Mobilis',
            6 => 'Motoris',
        ];
    
        return (object) [
            'id' => $sale->id,
            'reference' => $sale->reference,
            'date' => strtotime($sale->date) * 1000, // Convert date to milliseconds
            'total' => $sale->total_amount,
            'sub_total' => $sale->sub_total,
            'shipping_amount' => $sale->shipping_amount,
            'tax' => (object) [
                'ppn' => $sale->tax_amount,
                'amount' => $sale->tax_percentage,
            ],
            'discount' => (object) [
                'disc' => $sale->discount_percentage,
                'amount' => $sale->discount_amount,
            ],
            'customer' => (object) [
                'id' => $sale->customer->id,
                'code' =>  $sale->customer->code,
                'name' => $sale->customer_name,
                'email' => $sale->customer->customer_email,
                'contact' => $sale->customer->customer_phone,
                'city' => $sale->customer->city,
                'country' => $sale->customer->country,
                'address' => $sale->customer->address,
            ],
           
                'status' => (object) [
                    'code' => 1,
                    'status' => "selesai",
                ],
                
                
               
            'sales' => (object) [
                'id' => $sale->saler->id,
                'code' => $sale->saler->Kode,
                'name' => $sale->saler->Nama,
                'contact' => $sale->saler->user->contact ?? 'null',
                'type' => $roleTypes[optional($sale->saler->user)->role_id] ?? "Saler",
                'depo' => $sale->depo->Kode,
            ],
            'products' => $sale->saleDetails->map(function ($detail) {
                return (object) [
                    'id' => $detail->product_id,
                    'name' => $detail->product_name,
                    'quantity' => $detail->quantity,
                    'unit_price' => $detail->price,
                    'total' => $detail->sub_total,
                ];
            }),
            'due' => $sale->due,
            'payment_method' => $sale->payment_method,
            'payment_status' => (object) [
                    'code' => 1,
                    'status' => "lunas",
                ],
              
            'due_amount' => $sale->due_amount,
            'paid_amount' => $sale->paid_amount,
            'bill' => $bill,
            'note' => $sale->note,
        ];
    });
    
    // Extract the first element from the collection
    $transformedSingleObject = $transformedData->first();
    
    
    // If needed, use the transformed data directly as an object
    // $transformedData is already a collection of objects
    
        
        // If needed, convert the transformed data into an array
        
        return new PostResource(true, 'Sales details', $transformedSingleObject);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'status' => 'in:completed,failed,pending,cancel',
                'pay_amount' => 'numeric|min:0',
                // Add validation rules for other fields if needed
            ]);
        
            // If validation fails, return a response with validation error messages
            if ($validator->fails()) {
                return new PostResource(false, 'Validation failed', $validator->errors()->toArray());
            }
        
            // Find the Sales instance by ID
            $sales = Sales::find($id);
        
            // If the Sales instance is not found, return a 404 response
            if (!$sales) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale / Transaction not found',
                ], 404);
            }
        
            // Check if the pay_amount exceeds the due_amount
            if ($request->pay_amount > $sales->due_amount) {
                return new PostResource(false, 'The amount exceeds what needs to be paid.', [null]);
            }
        
            // Check if the Sales instance has a due amount and is not fully paid
            if (is_numeric($sales->due_amount) && is_numeric($sales->paid_amount) && $sales->due_amount != $sales->paid_amount) {
                // Update the Sales instance with the validated data
                $pay = $sales->paid_amount + $request->pay_amount;
                $sales->update([
                    'status' => $request->status ?? $sales->status,
                    'paid_amount' => $pay,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale / Transaction not paid off',
                ], 404);
            }
        
            // Return the updated Sales as a resource
            return new PostResource(true, 'Sales updated successfully', $sales);
        
        } catch (\Exception $e) {
            // Handle other exceptions if any
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the Sales instance by ID
        $sales = Sales::find($id);

        if (!$sales) {
            return new AllResource(false, 'Sales not found!', null);
        }

        // Delete the Sales instance
        $sales->delete();

        // Return a success response
        return new AllResource(true, 'Sales deleted successfully', null);
    }
}

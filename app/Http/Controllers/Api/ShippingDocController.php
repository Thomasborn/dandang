<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\PostResource;
use App\Models\Product;
use App\Models\ProductSaler;
use App\Models\ProductShippingDoc;
use App\Models\Saler;
use App\Models\ShippingDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ShippingDocController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function index(Request $request)
{
    $perPage = $request->input('perPage', 10);
    $search = $request->input('search');

    // Query builder for ShippingDoc with eager loading
    $shippingDocsQuery = ShippingDoc::with('saler.user', 'productShippingDoc.product');

    // Apply search filters
    if ($search) {
        $shippingDocsQuery->where(function ($query) use ($search) {
            $query->where('date', 'like', '%' . $search . '%')
                  ->orWhereHas('saler', function ($query) use ($search) {
                      $query->where('Kode', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('saler', function ($query) use ($search) {
                      $query->where('tipe', 'like', '%' . $search . '%');
                  });
        });
    }

    // Fetch shipping docs based on applied filters
    $shippingDocs = $shippingDocsQuery->get();

    // Transform data
    $transformedData = $shippingDocs->map(function ($shippingDoc) {
        $totalQuantity = $shippingDoc->productShippingDoc->sum('quantity');
        $totalProductPrice = 0; // Initialize total product price

        // Calculate total product price
        foreach ($shippingDoc->productShippingDoc as $productShippingDoc) {
            $totalProductPrice += $productShippingDoc->quantity * $productShippingDoc->product->product_price;
        }

        $date = $shippingDoc->date;
        // Convert date string to Unix timestamp (seconds since Unix epoch)
        $timestamp = strtotime($date);

        // Convert seconds to milliseconds
        $milliseconds = $timestamp * 1000;

        return [
            'id' => $shippingDoc->id,
            'depo' => "DBDGGT.01", // Replace with the actual name column in the Saler model
            'date' => $milliseconds, // Replace with the actual date column in the ShippingDoc model
            'seller' => $shippingDoc->saler->Kode, // Replace with the actual code column in the Saler model
            'role' => optional(optional($shippingDoc->saler)->user)->role_id, // Replace with the actual role_id column in the Saler model
            'seller_type' => $shippingDoc->saler->tipe, // Replace with the actual tipe column in the Saler model
            'total_product' => $totalQuantity, // Total quantity of products
            'product_price' => $totalProductPrice, // Total price of all products
        ];
    });

    // Paginate the transformed data
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $paginator = new LengthAwarePaginator(
        $transformedData->forPage($currentPage, $perPage),
        $transformedData->count(),
        $perPage,
        $currentPage,
        ['path' => LengthAwarePaginator::resolveCurrentPath()]
    );
    // Now $paginator contains the paginated transformed data
    return new AllResource(true, 'List Data ShippingDocs', $paginator);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatorshippingDoc = Validator::make($request->all(), [
            'saler_id' => 'required|exists:saler,id',
            'products' => 'required|array', // Ensure that productss is an array
            'products.*.product_id' => 'required|string|exists:products,id',
            'products.*.amount' => 'required|integer|min:0',
        ]);
        $saler = Saler::where('id', $request->saler_id)->first();
        // return new AllResource(false, 'ShippingDoc not found', $saler);

        if ($saler) {
            $salerCode = $saler->Kode;
            
            // Now $kode contains the value of the 'Kode' column for the specified saler_id
        } else {
            // Handle the case where no saler with the given id is found
            return response()->json("Saler doesnt exist", 422);

        }
        
        // Check if validation fails for surat_jalan
        if ($validatorshippingDoc->fails()) {
            return response()->json($validatorshippingDoc->errors(), 422);
        }
        // $productData = 
        //             return response()->json($productData, 200);

        try {
            // Start a database transaction
             // Start a database transaction
            DB::beginTransaction();

            // Create a new surat_jalan
            $shippingDoc = shippingDoc::create([
                'saler_code' => $salerCode,
                'date' => date('Y-m-d')
            ]);

            $productShippingArray = [];

            // Loop through each product detail and create or update product_surat_jalan entries
            foreach ($request->products as $product) {
                $productshippingDocItem = ProductShippingDoc::updateOrCreate(
                    [
                        'shipping_doc_id' => $shippingDoc->id,
                        'product_id' => $product['product_id'],
                        
                    ],
                    [
                        'quantity' => $product['amount'],
                    ]
                );
                // Increment the quantity field in product_sales
                $productSaler=ProductSaler::updateOrCreate(
                    [
                        'saler_code' => $salerCode,
                        'product_id' => $product['product_id'],
                        'first_stock' =>  $product['amount']
                    ],
                    [
                        'product_quantity' => DB::raw('product_quantity + ' . $product['amount']),
                    ]
                );
                
                $productData = Product::find($productshippingDocItem->product_id);

                // // Check if the data is found
                // if ($productData) {
                //     // Return the JSON response
                //     return response()->json($productData, 200);
                // } else {
                //     // Return an error JSON response
                //     return response()->json(['message' => 'Data not found'], 404);
                // }
                

                if ($productData) {
                    $productData->update([
                        'product_quantity' => $productData->product_quantity - $product['amount']
                    ]);
                } else {
                    // Roll back the transaction in case of an error
                    DB::rollBack();

                    // Handle the case where the product data is not found
                    return response()->json(['message' => 'product is not found'], 404);
            }

                // Append the $productshippingDocItem to the array
                $productShippingArray[] = $productshippingDocItem;
            }

            // Commit the transaction if everything is successful
            DB::commit();

            // Return a success response
            return new AllResource(true, 'Data shippingDoc Berhasil Diubah!', [
                'shippingDoc' => $shippingDoc,
                'products' => $productShippingArray,
                // 'product_sales' => $product_sales,
                //opsional total product yang ditambahkan diperlihatkan dengan jumlah bartang yang telah dibawea oleh sales
            ]);

        } catch (\Exception $e) {
            // Roll back the transaction in case of any exception
            DB::rollBack();

            // Handle the exception (log, report, etc.)
            return response()->json(['message' => $e], 404);

            // return new AllResource(false, ', null, 500);
        }
    }
        // $this->validate($request, [
        //     'saler_id' => 'required',
        //     // Add other validation rules for the remaining fields
        // ]);

        // $shippingDoc = ShippingDoc::create($request->all());
        // return new AllResource(true, 'Data ShippingDoc created successfully', $shippingDoc);
    // }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // $shippingDoc = ShippingDoc::find($id);
        // // $shippingDoc = ShippingDoc::with('saler.user', 'productShippingDoc')->find($id);
$shippingDocs = ShippingDoc::with('saler.user', 'productShippingDoc.product')
                            ->where('id', $id)
                            ->first();

if (!$shippingDocs) {
    return new PostResource(false, 'ShippingDoc not found', null);
}
  $date = $shippingDocs->date;
        // Convert date string to Unix timestamp (seconds since Unix epoch)
        $timestamp = strtotime($date);

        // Convert seconds to milliseconds
        $milliseconds = $timestamp * 1000;
$transformedData = [
    'id' => $shippingDocs->saler->id,
    'depo' => "DBDGGT.01", // Replace with the actual name column in the Saler model
    'date' =>$milliseconds, // Replace with the actual date column in the ShippingDoc model
    'seller_code' => $shippingDocs->saler->Kode, // Replace with the actual code column in the Saler model
    'seller_name' => $shippingDocs->saler->Nama, // Replace with the actual code column in the Saler model
    'seller_address' => $shippingDocs->saler->alamat, // Replace with the actual code column in the Saler model
    'seller_type' => optional(optional($shippingDocs->saler)->user)->role_id, // Replace with the actual role_id column in the Saler model
    'total_product' => $shippingDocs->productShippingDoc->sum('quantity'), // Replace with the actual relation and attribute in the ShippingDoc model
    'product_price' => $shippingDocs->productShippingDoc->sum(function ($productShippingDoc) {
        return $productShippingDoc->quantity * $productShippingDoc->product->product_price;
    }), // Calculate the total product price based on the quantity and product price
    'products' => $shippingDocs->productShippingDoc->map(function ($productShippingDoc) {
        return [
            'id' => $productShippingDoc->product_id,
            'name' => $productShippingDoc->product->product_name, // Replace with the actual name column in the Product model
            'description' => $productShippingDoc->product->description, // Replace with the actual description column in the Product model
            'size' => $productShippingDoc->product->product_size,
            'uom' => $productShippingDoc->product->product_unit,
            'price' => $productShippingDoc->product->product_price,
            'stock' => $productShippingDoc->quantity,
            'image' => $productShippingDoc->product->image,
        ];
    }),
];

$result = $transformedData;

        
        // Now $result contains the desired structure
        
        // Now $result contains the desired JSON structure
        
        // return new AllResource(true, 'List Data ShippingDocs', $result);
        return new AllResource(true, 'Data ShippingDoc retrieved successfully', $result);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $shippingDoc = ShippingDoc::find($id);

        if (!$shippingDoc) {
            return new AllResource(false, 'ShippingDoc not found', null);
        }

        $shippingDoc->update($request->all());
        return new AllResource(true, 'Data ShippingDoc updated successfully', $shippingDoc);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $shippingDoc = ShippingDoc::find($id);

        if (!$shippingDoc) {
            return new AllResource(false, 'ShippingDoc not found', null);
        }

        $shippingDoc->delete();
        return new AllResource(true, 'ShippingDoc deleted successfully', null);
    }
}

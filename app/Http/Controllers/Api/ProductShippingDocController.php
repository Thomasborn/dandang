<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Models\ProductSaler;
use App\Models\ProductShippingDoc;
use App\Models\Saler;
use Illuminate\Http\Request;

class ProductShippingDocController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productSalers = ProductSaler::with('product.category')->get();

        $transformedData = $productSalers->map(function ($productSaler) {
            $product = $productSaler->product;
        
            return [
                'id' => $product->id,
                'name' => strtolower($product->product_name),
                'category'=>$product->category->category_name,
                // 'description' => $product->description,
                'size' => $product->product_size,
                'uom' => $product->product_unit,
                'price' => $product->product_price,
                'stock' => $product->product_quantity,
                'image' => $product->image,
            ];
        });
        
        return new AllResource(true, 'List Data ProductShippingDocs', $transformedData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'shipping_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required',
            // Add other validation rules for the remaining fields
        ]);

        $productShippingDoc = ProductShippingDoc::create($request->all());
        return new AllResource(true, 'Data ProductShippingDoc created successfully', $productShippingDoc);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

    $saler = Saler::where('user_id', $id)->first();
    // return new AllResource(false, 'ShippingDoc not found', $saler);

    if ($saler) {
        $salerCode = $saler->Kode;
        
        // Now $kode contains the value of the 'Kode' column for the specified saler_id
    } else {
        // Handle the case where no saler with the given id is found
        return response()->json("Saler doesnt exist", 422);

    }
        $productSalers = ProductSaler::with('product.category')
        ->where('saler_code', $salerCode) // Replace with your actual column and value for where_sales_id
        ->get();
    
   

        if (!$productSalers) {
            return new AllResource(false, 'ProductShippingDoc not found', null);
        }
        $transformedData = $productSalers->map(function ($productSaler) {
            $product = $productSaler->product;
        
            return [
                'id' => $product->id,
                'name' => strtolower($product->product_name),
                'category' => optional($product->category)->category_name, // Use optional to handle null
                // 'description' => $product->description,
                'size' => $product->product_size,
                'uom' => $product->product_unit,
                'price' => $product->product_price,
                'stock' => $product->product_quantity,
                'image' => $product->image,
            ];
        });
        return new AllResource(true, 'Data ProductShippingDoc retrieved successfully', $transformedData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $productShippingDoc = ProductShippingDoc::find($id);

        if (!$productShippingDoc) {
            return new AllResource(false, 'ProductShippingDoc not found', null);
        }

        $productShippingDoc->update($request->all());
        return new AllResource(true, 'Data ProductShippingDoc updated successfully', $productShippingDoc);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productShippingDoc = ProductShippingDoc::find($id);

        if (!$productShippingDoc) {
            return new AllResource(false, 'ProductShippingDoc not found', null);
        }

        $productShippingDoc->delete();
        return new AllResource(true, 'ProductShippingDoc deleted successfully', null);
    }
}

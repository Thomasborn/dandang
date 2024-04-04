<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve products with their categories
            $products = Product::with('category')->get();
        
            $transformedProducts = [];
        
            // Transform each product
            foreach ($products as $originalProduct) {
                $transformedProducts[] = [
                    'id' => strval($originalProduct->id),
                    'name' => strtolower($originalProduct->product_name), // Change this to the actual field name in your database
                    'type' => $originalProduct->category->category_name, // You can hardcode this value or get it from the original data if available
                    'description' => $originalProduct->product_note, // Change this to the actual field name in your database
                    'image' => $originalProduct->image,
                    'size' => $originalProduct->product_quantity, // Change this to the actual field name in your database
                    'uom' => $originalProduct->product_unit, // Change this to the actual field name in your database
                    'price' => $originalProduct->product_price, // Change this to the actual field name in your database
                    'stock' => $originalProduct->product_quantity, // Change this to the actual field name in your database
                ];
            }
        
            // $transformedProducts now contains the data in the new structure
            // You can use $transformedProducts as needed in your application
        
            return new AllResource(true, 'List Data Products', $transformedProducts);
        
        } catch (\Exception $e) {
            // Handle exceptions
            return new AllResource(false, 'An error occurred: ' . $e->getMessage(), null);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'category_id' => 'required',
            'product_name' => 'required',
            'product_code' => [
                'required',
                Rule::unique('products', 'product_code'),
            ],
            'product_quantity' => 'required',
            'product_cost' => 'required',
            'product_price' => 'required',
            'product_unit' => 'required',
            // Add other validation rules for the remaining fields
        ]);


        $product = Product::create($request->all());
        return new AllResource(true, 'Data Product created successfully', $product);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with('category')->find($id);
        
        // return new AllResource(false, 'Product not found', $id);

        if (!$product) {
            return new AllResource(false, 'Product not found', null);
        }
     
            $transformedProduct = [
                'id' => $product->id,
                'name' => strtolower($product->product_name),
                'type' => $product->category->name,
                'description' => $product->product_note,
                'image' => $product->image,
                'size' => $product->product_quantity,
                'uom' => $product->product_unit,
                'price' => $product->product_price,
                'stock' => $product->product_quantity,
                
            ];
        return new AllResource(true, 'Data Product retrieved successfully', $transformedProduct);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return new AllResource(false, 'Product not found', null);
        }

        $product->update($request->all());
        return new AllResource(true, 'Data Product updated successfully', $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return new AllResource(false, 'Product not found', null);
        }

        $product->delete();
        return new AllResource(true, 'Product deleted successfully', null);
    }
}
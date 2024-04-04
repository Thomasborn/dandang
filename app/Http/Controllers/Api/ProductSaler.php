<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllResource;
use App\Http\Resources\PostResource;
use App\Models\ProductSaler as ModelsProductSaler;
use App\Models\Saler;
use App\Models\ShippingDoc;
use Illuminate\Http\Request;

class ProductSaler extends Controller
{
    //
    public function getProductsSaler($id)
    {
        try {
            // Choose Saler with the given ID
            $saler = Saler::find($id);
        
            // Check if the Saler is not found
            if (!$saler) {
                return new PostResource(false, 'Saler not found.', null);
            }
        
            // Retrieve products associated with the Saler
            $productSellers = ModelsProductSaler::with('product')
                ->where('saler_code', $saler->Kode) // Adjust to the correct field name
                ->get();
        
            // Check if the Saler doesn't have associated products
            if ($productSellers->isEmpty()) {
                return new PostResource(false, 'Saler doesn\'t have products.', null);
            }
        
            $transformedDataSellers = [];
        
            foreach ($productSellers as $productSeller) {
                $transformedDataSellers[] = [
                    'name' => $productSeller->product->product_name,
                    'image' => $productSeller->product->image,
                    'size' => $productSeller->product->product_size,
                    'unit' => $productSeller->product->product_unit,
                    'category' => $productSeller->product->category->category_name,
                    'code' => $productSeller->product->product_code,
                    'stock' => $productSeller->product_quantity,
                    'sold' => $productSeller->sold,
                    'first_stock' => $productSeller->first_stock,
                    'quantity' => $productSeller->product_quantity,
                    'price' => $productSeller->product->product_price,
                    'total' => $productSeller->sold * $productSeller->product->product_price,
                ];
            }
        
            return new PostResource(true, 'Products for Saler ' . $saler->Nama, $transformedDataSellers);
        
        } catch (\Exception $e) {
            // Handle exceptions
            return new PostResource(false, 'An error occurred: ' . $e->getMessage(), null);
        }
        
    }
    public function getProductsWithSaler($id)
    {
        try {
            // Get Saler by user ID
            $seller = Saler::where('user_id', $id)->first();
        
            // Check if the Saler is not found
            if (!$seller) {
                return new PostResource(false, 'Saler not found.', null);
            }
        
            // Extract the Saler code
            $sellerCode = $seller->Kode;
        
            // Retrieve productSellers associated with the Saler
            $productSellers = ModelsProductSaler::with('product.category')
                ->where('saler_code', $sellerCode) // Replace with your actual column and value for where_sales_id
                ->get();
        
            // Check if products are not found
            if ($productSellers->isEmpty()) {
                return new AllResource(false, 'Products not found', null);
            }
        
            // Transform the data
            $transformedData = $productSellers->map(function ($productSaler) {
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
        
            return new AllResource(true, 'Data Product', $transformedData);
        
        } catch (\Exception $e) {
            // Handle exceptions
            return new PostResource(false, 'An error occurred: ' . $e->getMessage(), null);
        }
    
}
}

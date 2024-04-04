<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class ProductController extends Controller
{
    public function index()
    {
        // Replace 'your_bearer_token' with your actual Bearer token
        $bearerToken = '28|van35c7jIj0ujCN0q70zt09zHhpbDohjl9dRHx0efbbda254';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearerToken,
        ])->get('https://omahit.my.id/api/products');

        $products = $response->json()['data'];

        return view('products.index', compact('products'));
    }
    public function create()
    {
        $tipe = Tipe::all();
        return view('products.create', ['tipe' => $tipe]);
        
    }
    public function store(Request $request)
{
    // Replace 'your_bearer_token' with your actual Bearer token
    $bearerToken = '28|van35c7jIj0ujCN0q70zt09zHhpbDohjl9dRHx0efbbda254';

    // Validation rules
    $rules = [
        'nama'      => 'required|unique_nama_tipe_combination',
        'harga'     => 'required|numeric|min:0',
        'stok'      => 'required|integer|min:0',
        'tipe'      => 'required|exists:tipe,id',
        'gambar'    => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'deskripsi' => 'required',
    ];

    // Check if there is a kemasan_id in the request
    if ($request->has('kemasan_id')) {
        // If kemasan_id is present, uom and ukuran are not required
        $rules['kemasan_id'] = 'integer|min:0';
    } else {
        // If kemasan_id is not present, require uom and ukuran
        $rules['kemasan_id'] = 'integer|min:0';
        $rules['uom'] = 'required';
        $rules['ukuran'] = 'required|numeric|min:0';
    }

    $customMessages = [
        'unique_nama_tipe_combination' => 'Nama dan tipe tersebut sudah ada.',
    ];

    // Validator instance
    $validator = Validator::make($request->all(), $rules, $customMessages);
    // dd($request->all());
    // Check if validation fails
    if ($validator->fails()) {
        return redirect('/products/create')
            ->withErrors($validator)
            ->withInput();
    }
    
    // Make the API request to add a new product
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $bearerToken,
    ])->attach('gambar', $request->file('gambar')->path(), $request->file('gambar')->getClientOriginalName())
    ->post('https://omahit.my.id/api/products', $request->except('gambar'));
    // Check if the request was successful (status code 2xx)
    
    if ($response->successful()) {
        // Product added successfully, you can redirect or perform any other action
        return redirect('/products')->with('success', 'Product added successfully');
    } else {
        $errorResponse = $response->json();
        // Handle the case where the API request was not successful
        return redirect('/products/create')->with('error', json_encode($errorResponse), 'Failed to add the product');
    }
    }
}

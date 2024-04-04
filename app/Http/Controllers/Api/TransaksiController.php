<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AllResource;
use App\Models\Sales;
use App\Models\Barang;
use App\Models\Barang_kemasan;
use App\Models\Barang_sales;
use App\Models\Bonus_transaksi;
use App\Models\Customer;
use App\Models\Transaksi;use Illuminate\Support\Str;
use App\Models\Transaksi_detail;
use DateTime;

//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    public function index()
    {
        //get all transaksis
        $transaksis = Transaksi::with('transaksiDetail', 'bonusTransaksi')->get();

        //return collection of transaksis as a resource
        return new AllResource(true, 'List Data transaksis', $transaksis);
    }
     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);
        // $is=1;
        // return new AllResource(true, 'Data transaksi Berhasil Din!', $is);
       if ($validator->fails()) {
    return response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $validator->errors()
    ], 202);
}

        //Tambahkan customer
        try {
            $kode = 'CUS' . date('Ymd') . Str::random(4);
    
        // If customer_id is not present, create a new customer
        if (!$request->has('customer_id') || $request->input('customer_id') == null) {
            // Check if a customer with the same 'nama' and 'nomor_telepon' already exists
            $existingCustomer = Customer::where('nama', $request->nama)
            ->where('nomor_telepon', $request->nomor_telepon)
            ->first();
           
    
            if (!$existingCustomer) {

                // If no existing customer is found, create a new customer
                $customer = Customer::create([
                    'kode' => $kode,
                    'nama' => $request->nama,
                    'alamat' => $request->alamat,
                    'nomor_telepon' => $request->nomor_telepon,
                ]);
    
                // Assign the new customer_id to the request
                $request->merge(['customer_id' => $customer->id]);
            } else {
                // If a customer with the same 'nama' and 'nomor_telepon' already exists, return an error response
                return response()->json([
                    'error' => 'Customer with the same name and phone number already exists.',
                    'existing_customer_id' => $existingCustomer->nama,
                ], 422);
            }
        }
            $total_harga = $this->calculateTotalHarga($request->details);
        
            $transaksi = $this->createTransaksiRecord($request, $total_harga);
            $transaksiDetailArray = [];
            $bonusTransaksiArray = [];
            $sales = sales::where('id', $request->sales_id)->first();
        
            if ($sales && strpos(strtolower($sales->tipe), 'sales to') !== false) {
                
                $transaksiDetailArray = $this->createTransaksiTODetails($transaksi, $request->details);
                $bonusTransaksiArray = $this->createBonusTransaksi($transaksi, $request->details);
            } else {
                // return new AllResource(true, 'Data transaksi Berhasil Din!', $sales);

                $transaksiDetailArray = $this->createTransaksiDetails($transaksi, $request->details);
                $bonusTransaksiArray = $this->createBonusTransaksi($transaksi, $request->details);
            }
        
            $rincianTransaksi = [
                'transaksi' => $transaksi,
                'transaksiDetails' => $transaksiDetailArray,
                'bonusTransaksi' => $bonusTransaksiArray,
            ];
            return new AllResource(true, 'Data transaksi Berhasil', $rincianTransaksi);
        } catch (\Exception $e) {
            // Handle the exception as needed
            return response()->json(['error' => $e->getMessage()], 400);
        }
        
        
    }
    private function addOrUpdateCustomer(Request $request)
    {
        $kode = 'CUS' . date('Ymd') . Str::random(4);
    
        // If customer_id is not present, create a new customer
        if (!$request->has('customer_id')) {
            // Check if a customer with the same 'nama' and 'nomor_telepon' already exists
            $existingCustomer = Customer::where('nama', $request->nama)
                                        ->where('nomor_telepon', $request->nomor_telepon)
                                        ->first();
    
            if (!$existingCustomer) {
                // If no existing customer is found, create a new customer
                $customer = Customer::create([
                    'kode' => $kode,
                    'nama' => $request->nama,
                    'alamat' => $request->alamat,
                    'nomor_telepon' => $request->nomor_telepon,
                ]);
    
                // Assign the new customer_id to the request
                $request->merge(['customer_id' => $customer->id]);
            } else {
                // If a customer with the same 'nama' and 'nomor_telepon' already exists, return an error response
                return response()->json([
                    'error' => 'Customer with the same name and phone number already exists.',
                    'existing_customer_id' => $existingCustomer->id,
                ], 422);
            }
        }
    }
    
    protected function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'sales_id' => 'required|exists:sales,id',
            'metode_pembayaran' => 'required|string',
            // 'status_transaksi' => 'required|string',
            'tipe_transaksi' => '|string',
            'jatuh_tempo' => 'nullable|date',
            'nama' => 'required_if:customer_id,null|string',
            'nomor_telepon' => 'required_if:customer_id,null|string',
            'alamat' => 'required_if:customer_id,null|string',
            'customer_id' => 'nullable|exists:customer,id',
            'details' => 'required|array',
            'details.*.id' => 'required|exists:barang_kemasan,id',
            'details.*.amount' => 'required|numeric',
            'details.*.barang_bonus_id' => 'exists:barang_bonus,id',
            'details.*.jumlah_barang_bonus' => 'numeric',            // 'details.*.id' => 'required|exists:barang_sales,id',

        ]);
        
    }

    protected function calculateTotalHarga($details)
    {
        return array_reduce($details, function ($total, $detail) {
            $barangKemasan = Barang_kemasan::findOrFail($detail['id']);

            return $total + $detail['amount'] * $barangKemasan->harga;
        }, 0);
    }

    protected function createTransaksiRecord(Request $request, $totalHarga)
    {$requestJatuhTempo = $request->jatuh_tempo; // Assuming $request->jatuh_tempo contains the datetime string

        // Convert the datetime string to a DateTime object
        $jatuhTempoDateTime = new DateTime($requestJatuhTempo);
        
        // Get the date portion of the DateTime object
        $jatuhTempoDate = $jatuhTempoDateTime->format('Y-m-d');
        return Transaksi::create([
            'driver_id' => $request->driver_id,
            'sales_id' => $request->sales_id,
            'total_harga' => $totalHarga,
            'tanggal_transaksi' => now(),
            'jatuh_tempo' => $jatuhTempoDate,
            'metode_pembayaran' => $request->metode_pembayaran,
            'status_transaksi' => $request->status_transaksi,
            'tipe_transaksi' => $request->tipe_transaksi,
            'ppn' => $request->ppn,
            'gudang_id' => $request->gudang_id,
            'customer_id' => $request->customer_id,
            // Add other fields as needed
        ]);
    }

    protected function createTransaksiDetails($transaksi, $details)
    {
        return array_map(function ($detail) use ($transaksi) {
          $barangKemasan = Barang_kemasan::where('barang_id', $detail['barang_id'])
            ->where('harga', $detail['price'])
            ->get();
        
            $transaksiDetailItem = transaksi_detail::create([
                'transaksi_id' => $transaksi->id,
                'barang_id' => $barangKemasan->id,
                'jumlah_barang' => $detail['amount'],
                'harga_barang' => $detail['price'],
                // Add other fields as needed
            ]);
            
            barang_sales::where('sales_id', $transaksi->sales_id)
                ->where('id', $barangKemasan->id)
                ->decrement('jumlah_barang', $detail['amount']);

            return $transaksiDetailItem;
        }, $details);
    }
    protected function createTransaksiTODetails($transaksi, $details)
    {
        return array_map(function ($detail) use ($transaksi) {
      $barangKemasan = Barang_kemasan::where('barang_id', $detail['id'])
    ->where('harga', $detail['price'])
    ->first();

// Now $barangKemasan contains the retrieved record, or null if not found

            $transaksiDetailItem = transaksi_detail::create([
                'transaksi_id' => $transaksi->id,
                'barang_id' => $barangKemasan->id,
                'jumlah_barang' => $detail['amount'],
                'harga_barang' =>$detail['price'],
                // Add other fields as needed
            ]);
            
        


            if ($barangKemasan) {
                $barangKemasan->update([
                    'stok' => $barangKemasan->stok - $detail['amount']
                ]);
            }
            return $transaksiDetailItem;
        }, $details);
    }

    protected function createBonusTransaksi($transaksi, $details)
    {
        return array_map(function ($detail) use ($transaksi) {
            if (isset($detail['barang_bonus_id']) && isset($detail['jumlah_barang_bonus'])) {
                $bonusTransaksiItem = bonus_transaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_bonus_id' => $detail['barang_bonus_id'],
                    'jumlah_barang_bonus' => $detail['jumlah_barang_bonus'],
                    // Add other fields as needed
                ]);

                return $bonusTransaksiItem;
            }
        }, $details);
    }

    public function show($id)
    {
        //find transaksis by ID
        $transaksis = Transaksi::find($id);

        //return single transaksi as a resource
        return new AllResource(true, 'Detail Data transaksis!', $transaksis);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $transaksis
     * @return void
     */
    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'company_name'     => 'required',
            'address'     => 'required',
            'phone'   => 'required',
            'email'   => 'required',
            'website'   => 'required',
           
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find transaksis by ID
        $transaksis = Transaksi::find($id);

        //check if image is not empty
      
            //update transaksis without image
            $transaksis->update([
                'company_name'     => $request->company_name,
                'address'     => $request->address,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'website'   => $request->website,
            ]);
        

        //return response
        return new AllResource(true, 'Data transaksis Berhasil Diubah!', $transaksis);
    }

    /**
     * destroy
     *
     * @param  mixed $transaksis
     * @return void
     */
    public function destroy($id)
    {

        //find transaksis by ID
        $transaksis = Transaksi::find($id);

      
        //delete transaksis
        $transaksis->delete();

        //return response
        return new AllResource(true, 'Data transaksis Berhasil Dihapus!', null);
    }
    //
}

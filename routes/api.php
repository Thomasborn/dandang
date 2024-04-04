<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Laravel\Sanctum\Http\Controllers\AuthorizedAccessTokenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\KendaraanController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\GudangController;
use App\Http\Controllers\Api\SuratJalanController;
use App\Http\Controllers\Api\DepoController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\BarangSalesController;
use App\Http\Controllers\Api\PengirimanController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\BarangBonusController;
use App\Http\Controllers\Api\BarangKemasanController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductSaler;
use App\Http\Controllers\Api\ProductShippingDocController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SalerController;
use App\Http\Controllers\Api\SalesReport;
use App\Http\Controllers\Api\SellerControl;
use App\Http\Controllers\Api\ShippingDocController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionHistory;
use App\Http\Controllers\ProductShippingDocController as ControllersProductShippingDocController;
use App\Models\ProductShippingDoc;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Route::post('/sanctum/csrf-cookie', CsrfCookieController::class);
//register
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);

//unauthorized
Route::get('/unauthorized', [\App\Http\Controllers\Api\AuthController::class, 'unauthorized'])->name('unauthorized');

//login
Route::match(['get', 'post'], '/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');

//logout
Route::middleware('auth:sanctum')->post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->name('logout');


//refresh token
Route::middleware(['auth:sanctum'])->match(['get', 'post'], '/refresh-token', [\App\Http\Controllers\Api\AuthController::class, 'refreshToken'])
    ->name('refresh-token');
Route::fallback([\App\Http\Controllers\Api\ErrorController::class, 'notFound']);

//MAKE REFRESH TOKEN WITH VALIDITY EXPIRED USING MIDDLEWARE
// Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])
//     ->match(['get', 'post'], '/refresh-token', [\App\Http\Controllers\Api\AuthController::class, 'refreshToken'])
//     ->name('refresh-token');
Route::get('/reports-sales-tax-dev', [SalesReport::class, 'salesTax']);
Route::apiResource('/transactions-dev', TransactionController::class);
//user
Route::apiResource('/users-dev', UserController::class);

//sellers
Route::apiResource('/sellers-dev', SalerController::class);
Route::get('/sellers-products-dev/{id}', [ProductSaler::class,'getProductsWithSaler']);

//customers
Route::apiResource('/customers-dev', CustomerController::class);
Route::apiResource('/stores-dev', StoreController::class);

//shpippingdocs
Route::apiResource('/sd-dev', ShippingDocController::class);
Route::apiResource('/products-shipping-dev', ProductShippingDocController::class);
Route::apiResource('/products-packages-dev', ProductShippingDocController::class);
Route::get('/sd-dev/sellers-products-dev/{id}', [ProductSaler::class, 'getProductsSaler']);

//Reports
Route::get('/reports-sales-tax-dev', [SalesReport::class, 'salesTax']);
Route::get('/reports-export-sales-tax-dev', [SalesReport::class, 'exportSalesTax']);
Route::get('/search-seller-dev', [SalerController::class, 'searchSeller']);

//Transactions
Route::apiResource('/transactions-dev', TransactionController::class);
Route::get('/transactions/pending-dev', [TransactionHistory::class, 'transactionPending']);

//Dashboard
Route::get('/dashboard/total-amount-seller-dev/{id}', [DashboardController::class, 'totalAmountSaler']);
Route::get('/dashboard/summary-dev', [DashboardController::class, 'getSummary']);
Route::get('/dashboard/profit-last-month-dev', [DashboardController::class, 'profitLastMonth']);
Route::get('/dashboard/weekly-profit-last-month-dev', [DashboardController::class, 'weeklyProfitLastMonth']);
Route::get('/dashboard/total-amount-dev', [DashboardController::class, 'totalAmount']);
Route::get('/dashboard/sales-tax-dev/{id}', [DashboardController::class, 'getSaleTax']);
Route::get('/dashboard/top-sellers-dev', [DashboardController::class, 'getTopSellersData']);
Route::get('/dashboard/top-products-dev', [DashboardController::class, 'getTopProducts']);
Route::get('/dashboard/top-customers-dev', [DashboardController::class, 'getTopCustomer']);

//transports  
// Route::apiResource('/transports-dev', KendaraanController::class);

//inventories
// Route::apiResource('/inventories-dev', GudangController::class);  

// Route::apiResource('/sd/products', ShippingDocController::class);

//Depo/Warehouses
Route::apiResource('/warehouses-dev', DepoController::class);
// Route::apiResource('/transactions', TransactionController::class);

//Distributions
Route::apiResource('/distributions-dev', PengirimanController::class);
Route::apiResource('/drivers-dev', DriverController::class);

//products
Route::apiResource('/bonus-products-dev', BarangBonusController::class);
Route::apiResource('/products-dev', ProductController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    // Own account
    Route::put('/profile/{id}', [ProfileController::class, 'updateProfile']);
    Route::put('/account/{id}', [ProfileController::class, 'updateAccount']);
    
     //-----------------> ROLE ::::::: DEPO  <---------------//
    Route::middleware(['role:depo|Super Admin'])->group(function () {
        //user
        Route::apiResource('/users', UserController::class);

        //sellers
        Route::apiResource('/sellers', SalerController::class);
        Route::get('/sellers-products/{id}', [ProductSaler::class,'getProductsWithSaler']);

        //customers
        Route::apiResource('/customers', CustomerController::class);
        Route::apiResource('/stores', StoreController::class);

        //shpippingdocs
        Route::apiResource('/sd', ShippingDocController::class);
        Route::apiResource('/products-shipping', ProductShippingDocController::class);
        Route::apiResource('/product-packages', ProductShippingDocController::class);
        Route::get('/sd/sellers-products/{id}', [ProductSaler::class, 'getProductsSaler']);
        
        //Reports
        Route::get('/reports-sales-tax', [SalesReport::class, 'salesTax']);
        Route::get('/reports-export-sales-tax', [SalesReport::class, 'exportSalesTax']);
        Route::get('/search-seller', [SalerController::class, 'searchSeller']);
        
        //Transactions
        Route::apiResource('/transactions', TransactionController::class);
        Route::get('/transactions/pending', [TransactionHistory::class, 'transactionPending']);

        //Dashboard
        Route::get('/dashboard/total-amount-seller/{id}', [DashboardController::class, 'totalAmountSaler']);
        Route::get('/dashboard/summary', [DashboardController::class, 'getSummary']);
        Route::get('/dashboard/profit-last-month', [DashboardController::class, 'profitLastMonth']);
        Route::get('/dashboard/weekly-profit-last-month', [DashboardController::class, 'weeklyProfitLastMonth']);
        Route::get('/dashboard/total-amount', [DashboardController::class, 'totalAmount']);
        Route::get('/dashboard/sales-tax/{id}', [DashboardController::class, 'getSaleTax']);
        Route::get('/dashboard/top-sellers', [DashboardController::class, 'getTopSellersData']);
        Route::get('/dashboard/top-products', [DashboardController::class, 'getTopProducts']);
        Route::get('/dashboard/top-customers', [DashboardController::class, 'getTopCustomer']);

        //transports  
        Route::apiResource('/transports', KendaraanController::class);

        //inventories
        Route::apiResource('/inventories', GudangController::class);  

        // Route::apiResource('/sd/products', ShippingDocController::class);

        //Depo/Warehouses
        Route::apiResource('/warehouses', DepoController::class);
        // Route::apiResource('/transactions', TransactionController::class);

        //Distributions
        Route::apiResource('/distributions', PengirimanController::class);
        Route::apiResource('/drivers', DriverController::class);

        //products
        Route::apiResource('/bonus-products', BarangBonusController::class);
        Route::apiResource('/products', BarangController::class);
    });

    //-----------------> ROLE ::::::: Sales TO <---------------//
    Route::middleware(['role:sales TO|depo|Super Admin'])->group(function () {
        //products
        Route::apiResource('/products', ProductController::class);
        Route::apiResource('/transactions', TransactionController::class);
        Route::get('/transactions/pending', [TransactionHistory::class, 'transactionPending']);

        //dashboard
        Route::get('/dashboard/summary/{id}', [DashboardController::class, 'getSummarySaler']);
        Route::get('/dashboard/total-amount-seller/{id}', [DashboardController::class, 'totalAmountSaler']);

         //customers
         Route::apiResource('/customers', CustomerController::class);
         Route::apiResource('/stores', StoreController::class);

    });

  //-----------------> ROLE ::::::: ALL Sales <---------------//
    Route::middleware(['role:sales mobilis|sales TO|sales motoris|depo|Super Admin'])->group(function () {
        // Route::apiResource('/sales', SalerController::class);

        //customers
        Route::apiResource('/customers', CustomerController::class);
        Route::apiResource('/stores', StoreController::class);

        //dashboard
        Route::get('/dashboard/summary/{id}', [DashboardController::class, 'getSummarySaler']);
        Route::get('/dashboard/total-amount-seller/{id}', [DashboardController::class, 'totalAmountSaler']);
        
        //transactions
        Route::apiResource('/transactions', TransactionController::class);
        
        //products
        Route::apiResource('/products', ProductController::class)->only(['index', 'show']);
        Route::get('/sellers-products/{id}', [ProductSaler::class,'getProductsWithSaler']);

    });


  
});

// Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

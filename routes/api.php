<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\bank\BankController;
use App\Http\Controllers\calculator\CalculatorController;
use App\Http\Controllers\ChallanController;
use App\Http\Controllers\gst\GstController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\pan\PanController;
use App\Http\Controllers\mca\McaController;
use App\Http\Controllers\pincode\PincodeController;
use App\Http\Controllers\pan\PostofficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleSocialiteController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('auth/google', [GoogleSocialiteController::class, 'redirectToGoogle']);
Route::get('callback/google', [GoogleSocialiteController::class, 'handleCallback']);


Route::post('sign-up', [AuthController::class, 'signUp'])->name('signup');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('login/{provider}', [AuthController::class, 'loginSocialite'])->name('loginSocialite');
Route::post('udpate-profile', [AuthController::class, 'loginSocialite'])->name('loginSocialite');

Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::post('verify-otp', [AuthController::class, 'verifyOTP']);
Route::post('set-new-password', [AuthController::class, 'setNewPassword']);

// Route::get('login/{provider}', [AuthController::class, 'redirectToProvider']);
// Route::get('login/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

Route::prefix('admin')->group(function () {
    Route::post('sign-up', [AuthController::class, 'adminSignUp']);
    Route::post('login', [AuthController::class, 'adminLogin']);
});

Route::prefix('mca')->group(function () {
    Route::get('company-details', [McaController::class, 'getCompanyByCIN']);
});

Route::prefix('bank')->group(function () {
    Route::get('get-details', [BankController::class, 'getBankDetailsByIfsc']);
    Route::post('verify-account', [BankController::class, 'verifyBankAccount']);
});

Route::prefix('gsp')->group(function () {
    Route::get('gst-status', [GstController::class, 'gstStatus']);
    Route::get('search/gstin', [GstController::class, 'searchDetailsByGSTINNumber']);
    Route::get('search/gstin-by-pan', [GstController::class, 'searchGSTINNumberByPan']);
    Route::post('gst/return/track', [GstController::class, 'trackGSTReturn']);
    Route::post('gst/tax-payer/registration', [GstController::class, 'registerForGST']);
    Route::post('gst/tax-payer/generate-otp', [GstController::class, 'generateOTP']);
    Route::post('gst/tax-payer/verify-otp', [GstController::class, 'verifyOTP']);
    Route::post('gst/tax-payer/gstrs/gstr-4/upload', [GstController::class, 'uploadGSTR4']);
    Route::get('gst/tax-payer/gstrs/gstr-3b/summary', [GstController::class, 'getGstr3bSummary']);
    Route::post('gst/tax-payer/gstrs/gstr-3b/upload', [GstController::class, 'uploadGstr3b']);
    Route::post('gst/tax-payer/gstrs/gstr-3b/submit', [GstController::class, 'submitGstr3b']);
    Route::get('gst/tax-payer/gstrs/gstr-2a', [GstController::class, 'gstr2a']);
    Route::get('gst/tax-payer/gstrs/gstr-2a/b2b', [GstController::class, 'gstr2aB2B']);
    Route::get('gst/tax-payer/gstrs/gstr-2a/b2ba', [GstController::class, 'gstr2aB2BA']);
    Route::get('gst/tax-payer/gstrs/gstr-2a/cdn', [GstController::class, 'gstr2aCDN']);
    Route::get('gst/tax-payer/gstrs/gstr-2a/cdna', [GstController::class, 'gstr2aCDNA']);
    Route::get('gst/tax-payer/gstrs/gstr-2a/isd', [GstController::class, 'gstr2aISD']);
    Route::get('gst/tax-payer/gstrs/gstr-1/at', [GstController::class, 'gstr1AT']);
    Route::get('gst/tax-payer/gstrs/gstr-1/ata', [GstController::class, 'gstr1ATA']);
    Route::get('gst/tax-payer/gstrs/gstr-1/b2b', [GstController::class, 'gstr1B2B']);
    Route::get('gst/tax-payer/gstrs/gstr-1/b2ba', [GstController::class, 'gstr1B2BA']);
    Route::get('gst/tax-payer/gstrs/gstr-1/b2cl', [GstController::class, 'gstr1B2CL']);
    Route::get('gst/tax-payer/gstrs/gstr-1/b2cla', [GstController::class, 'gstr1B2CLA']);
    Route::get('gst/tax-payer/gstrs/gstr-1/b2cs', [GstController::class, 'gstr1B2CS']);
    Route::get('gst/tax-payer/gstrs/gstr-1/b2csa', [GstController::class, 'gstr1B2CSA']);
    Route::get('gst/tax-payer/gstrs/gstr-1/cdnr', [GstController::class, 'gstr1CDNR']);
    Route::get('gst/tax-payer/gstrs/gstr-1/cdnra', [GstController::class, 'gstr1CDNRA']);
    Route::get('gst/tax-payer/gstrs/gstr-1/cdnur', [GstController::class, 'gstr1CDNUR']);
    Route::get('gst/tax-payer/gstrs/gstr-1/cdnura', [GstController::class, 'gstr1CDNURA']);
    Route::get('gst/tax-payer/gstrs/gstr-1/doc-issued', [GstController::class, 'gstr1DocIssue']);
    Route::get('gst/tax-payer/gstrs/gstr-1/exp', [GstController::class, 'gstr1Exp']);
    Route::get('gst/tax-payer/gstrs/gstr-1/expa', [GstController::class, 'gstr1Expa']);
    Route::get('gst/tax-payer/gstrs/gstr-1/summary', [GstController::class, 'gstr1Summary']);
    Route::get('gst/tax-payer/gstrs/gstr-1/hsn-summary', [GstController::class, 'gstr1HSN']);
    Route::get('gst/tax-payer/gstrs/gstr-1/nil-supplies', [GstController::class, 'gstr1NIL']);
    Route::post('gst/tax-payer/gstrs/gstr-1/upload', [GstController::class, 'uploadGSTR1']);
    Route::post('gst/tax-payer/gstrs/gstr-1/submit', [GstController::class, 'submitGSTR1']);
    Route::post('gst/tax-payer/gstrs/gstr-1/file', [GstController::class, 'fileGSTR1']);
    Route::get('gst/tax-payer/gstrs/gstr-1/generate-evc', [GstController::class, 'fileGSTR1']);
});

Route::prefix('ledger')->group(function () {
    Route::get('cash-itc-balance', [LedgerController::class, 'cashITCBalance']);
    Route::get('cash-ledger', [LedgerController::class, 'cashLedger']);
    Route::get('itc-ledger', [LedgerController::class, 'itcLedger']);
    Route::get('tax-liability-ledger', [LedgerController::class, 'taxLiabilityLedger']);
    Route::get('other-ledger', [LedgerController::class, 'otherLedger']);
    Route::get('return-related-liability-balance', [LedgerController::class, 'returnRelatedLiabilityBalance']);
});


Route::prefix('pan')->group(function () {
    Route::get('check-pan-aadhaar-status', [PanController::class, 'checkPanAADHARStatus']);
    Route::get('get-pan-details', [PanController::class, 'getAdvancePanDetails']);
});

Route::prefix('calculator')->group(function () {
    Route::post('income-tax/new-regime', [CalculatorController::class, 'incomeTaxNewRegime']);
    Route::post('income-tax/old-regime', [CalculatorController::class, 'incomeTaxOldRegime']);
    Route::post('advance-income-tax/old-regime', [CalculatorController::class, 'advanceIncomeTaxOldRegime']);
    Route::post('advance-income-tax/new-regime', [CalculatorController::class, 'advanceIncomeTaxNewRegime']);
    Route::post('tax-from-tradebook', [CalculatorController::class, 'taxTradebook']);
    Route::post('tax-from-tradewise', [CalculatorController::class, 'taxTradewise']);
    Route::post('tax-from-scripwise', [CalculatorController::class, 'taxScripwise']);
    Route::post('tds', [CalculatorController::class, 'calculateTDS']);
});

Route::prefix('calculator/miscellaneous')->group(function () {
    Route::post('fixed-deposit', [CalculatorController::class, 'calFDReturn']);
    Route::post('simple-interest', [CalculatorController::class, 'calSimpleInterest']);
    Route::post('compound-interest', [CalculatorController::class, 'calCompoundInterest']);
    Route::post('emi', [CalculatorController::class, 'emi']);
    Route::post('home-loan-emi', [CalculatorController::class, 'emi']);
    Route::post('car-loan-emi', [CalculatorController::class, 'emi']);
    Route::post('business-loan-emi', [CalculatorController::class, 'emi']);
    Route::post('personal-loan-emi', [CalculatorController::class, 'emi']);
    Route::post('calculate-home-loan-eligibility', [CalculatorController::class, 'homeLoanEligibility']);
    Route::post('depreciation', [CalculatorController::class, 'depreciation']);
    Route::post('post-office-mis', [CalculatorController::class, 'mis']);
    Route::post('lump-sum', [CalculatorController::class, 'lumpSum']);
    Route::post('cagr', [CalculatorController::class, 'CAGR']);
    Route::post('sip-gain', [CalculatorController::class, 'sipGain']);
    Route::post('nps-returns', [CalculatorController::class, 'npsReturns']);
    Route::post('recursive-deposit', [CalculatorController::class, 'recursiveDeposit']);
    Route::post('hra', [CalculatorController::class, 'hra']);
    Route::post('capital-gain-calculator', [CalculatorController::class, 'capitalGainCalculator']);
    Route::post('gst-calculator', [CalculatorController::class, 'gstCalculator']);
});

Route::prefix('challan')->group(function () {
    Route::post('add-update', [ChallanController::class, 'addUpdate']);
    Route::post('fetch-one', [ChallanController::class, 'fetchOne']);
    Route::post('count', [ChallanController::class, 'count']);
    Route::post('fetch-many', [ChallanController::class, 'fetchMany']);
    Route::post('delete', [ChallanController::class, 'delete']);
});

Route::prefix('banks')->group(function () {
    Route::post('banklistbypin', [BankController::class, 'findbankslistbypin']);
    Route::post('bankbypinandname', [BankController::class, 'findbankbypinandbankname']);
    Route::post('bankbycitynameandbankname', [BankController::class, 'findbankbycitynameandbankname']);
    Route::post('bankbycityname', [BankController::class, 'findbankbycityname']);
});

Route::prefix('postoffice')->group(function () {
    Route::post('postofficebypin', [PostofficeController::class, 'postofficebypin']);
});

Route::prefix('pincode')->group(function () {
    Route::post('pincodebycity', [PincodeController::class, 'pincodebycity']);
    Route::post('pincodeinfo', [PincodeController::class, 'pincodeinfo']);
});


Route::middleware(['auth:api,admin'])->group(function () {
    Route::get('get-extract', [ServiceController::class, 'getExtractData']);

    Route::post('merge', [ServiceController::class, 'merge']);
    Route::post('imagetopdf', [ServiceController::class, 'imageToPdf']);
    Route::post('compress', [ServiceController::class, 'compress']);
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::post('extract', [ServiceController::class, 'extract']);
Route::post('extract-data', [ServiceController::class, 'extractData']);
Route::post('extract-invoice', [ServiceController::class, 'extractInvoice']);
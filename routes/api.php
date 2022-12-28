<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DigitalprController ;
use App\Http\Controllers\API\DcsController ;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route::group(['middleware' => ['auth:api']], function() {
//     // define your route, route groups here
//  });

Route::middleware('auth:api')->group( function(){

    //User DCS Office Info  
    Route::get('/office/user-office', [DigitalprController::class, 'user_dcs_office']); 

    //Deposite type  
    Route::get('/account/deposit-type', [DigitalprController::class, 'deposit_type']);

    //Agent/FA Info  
    Route::post('/office/agent-info', [DigitalprController::class, 'agent_info']); 
    //Route::get('/office/agent-info/{fa_code}', [DigitalprController::class, 'agent_info']); 

    //Proposal Info for new policy 
    Route::post('/proposal/proposal-info', [DigitalprController::class, 'proposal_info']);

    //commission Info for new policy 
    Route::post('/proposal/commission', [DigitalprController::class, 'commission']);

    //Policy Info for Renew or deffered policy  
    Route::post('/policy/policy-info', [DigitalprController::class, 'policy_info']); 

    //Next premium date for Renew or deffered policy  
    Route::post('/policy/next-prem-date', [DigitalprController::class, 'next_prem_date']);

    //MR account head info  
    Route::post('/account/acc-head', [DigitalprController::class, 'acc_head']);

     //Store PR data  
     Route::post('/pr/submit-prinfo', [DigitalprController::class, 'submit_prinfo']);

    //DCS number info  
    Route::post('/dcs/dcs-info', [DcsController::class, 'dcs_info']);

     //DCS number details  
     Route::post('/dcs/dcs-details', [DcsController::class, 'dcs_details']);

     //DCS bank code and name  
     Route::post('/dcs/bank-info', [DcsController::class, 'bank_info']);

     //DCS bank branch code and name  
     Route::post('/dcs/bank-branch', [DcsController::class, 'bank_branch']);
});






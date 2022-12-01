<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;
use App\Models\Pr_Info;
use Carbon\Carbon;
use DB;

class DcsController extends Controller
{
    /*
     @ Bank information
     @ parameter Project Type     
    */
    public function bank_info(Request $request)
    {

        $dcsBank = $request->all();
        $ACC_PRJ = $dcsBank['ACC_PRJ'];
        /*
          @ Sample SQL query
          SELECT BANKCD, BANKNM FROM PRCOLLECTION.BANKCD_ALL
            WHERE PRJ_CODE=:ACC_PRJ
            ORDER BY BANKNM;
            ACC_PRJ = 'SB';
        */
        $dcsBank = DB::select('SELECT BANKCD, BANKNM FROM PRCOLLECTION.BANKCD_ALL
        WHERE PRJ_CODE=:ACC_PRJ
        ORDER BY BANKNM', 
        ['ACC_PRJ' => $ACC_PRJ]);    
        
        if (!($dcsBank)) {
            return response(['message' => 'Bank does not exist'], 400);
           }        
   
          $dcsBank = json_decode( json_encode($dcsBank), true);
          
          return response([ 'Bank_info' => ApiResource::collection($dcsBank), 'message' => 'Success'], 200);        
    }

    /*
     @ Bank Branch information
     @ parameter Project code and bank code     
    */
    public function bank_branch(Request $request)
    {

        $bankBranch = $request->all();
        $bank_code = $bankBranch['bank_code'];
        $prj_code = $bankBranch['prj_code'];

        // dd($bankBranch);
        // exit();

        /*
          @ Sample SQL Query
          SELECT BANKCD, BRNCHCD, BRNCHNM, ACNO, ALLCODE,TR_TYPE FROM PRCOLLECTION.BANKBCD_ALL A
            WHERE BANKCD=:BK_CODE
            AND PRJ_CODE=:ACC_PRJ
            AND A.STATUS='A'
            ORDER BY BANKCD, BRNCHCD;
            ACC_PRJ = 'SB';
        */
        $bankBranch = DB::select('SELECT PRJ_CODE, BANKCD, BRNCHCD, BRNCHNM, ACNO, ALLCODE,TR_TYPE, STATUS FROM PRCOLLECTION.BANKBCD_ALL A
        WHERE BANKCD=:BK_CODE
        AND PRJ_CODE=:PRJ_CODE
        AND A.STATUS=:BK_STATUS
        ORDER BY BANKCD, BRNCHCD',
        ['BK_STATUS' => 'A', 'BK_CODE' => $bank_code, 'PRJ_CODE' => $prj_code]); 
        
        
        if (!($bankBranch)) {
            return response(['message' => 'Bank branch name does not exist'], 400);
           }        
   
          $bankBranch = json_decode( json_encode($bankBranch), true);
          
          return response([ 'Bank_branch' => ApiResource::collection($bankBranch), 'message' => 'Success'], 200);        
    }

}

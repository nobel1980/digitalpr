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
     @ DCS information
     @ parameter dcs_date     
    */
    public function dcs_info(Request $request)
    {
        $dcsInfo = $request->all();
        $user= auth()->user();
        $dcsDate = $dcsInfo['dcs_date'];
        $prjType = $dcsInfo['prj_type'];
        $orgCode = $user->dcs_org_code;

      // validation check
      $rules=[
        'dcs_date'=>'required',
        'prj_type'=>'required'      
      ];

      $customMessage=[
          'dcs_date.required'=>'DCS date is required',
          'prj_type.required'=>'Project type is required',
      ];

      $validator=Validator::make($dcsInfo,$rules,$customMessage);

      if($validator->fails()){
          return response()->json($validator->errors(),422);
      }
        //$orgCode = '067700'; 

        /*
          @ Sample SQL query (Need update query)
            SELECT DISTINCT DCS_NO,ORG_CODE,DCS_YM,DCS_DATE,PTYPE FROM PRCOLLECTION.PR_INFO@ONLINEPAY_DBLK
            WHERE ORG_CODE=:ORG_CODE
            AND TO_CHAR(DCS_DATE,'YYYYMMDD')=:DCS_DATE
            AND PTYPE=:PTYPE;
        */
        $dcsInfo= DB::select("SELECT DISTINCT DCS_NO,ORG_CODE,DCS_YM,DCS_DATE,PTYPE 
        FROM PRCOLLECTION.PR_INFO
        WHERE ORG_CODE=:ORG_CODE
        AND TO_CHAR(DCS_DATE,'YYYYMMDD')=:DCS_DATE
        AND PTYPE=:PTYPE", 
        ['ORG_CODE' => $orgCode, 'DCS_DATE' => $dcsDate, 'PTYPE' => $prjType, ]);  
         
        if (!($dcsInfo)) {
            return response(['message' => 'DCS info does not exist'], 400);
           }        
   
          $dcsInfo = json_decode( json_encode($dcsInfo), true);
          
          return response([ 'dcs_info' => ApiResource::collection($dcsInfo), 'message' => 'Success'], 200);        
    }

   /*
     @ DCS details information
     @ parameter dcs_no     
    */
    public function dcs_details(Request $request)
    {
        $dcsBank = $request->all();
        $DCS_NO = $dcsBank['dcs_no'];
        /*
          @ Sample SQL query
            SELECT DISTINCT ORG_CODE, DCS_NO, DCS_YM,SUM(PR_AMT)DCS_AMT,SUM(NVL(PR_AMT,0)) DEP_AMT,DCS_SLNO
            FROM PRCOLLECTION.PR_INFO@ONLINEPAY_DBLK where DCS_NO='FE051600-214-20221201'
            AND NVL(PR_STATUS,'X')<>'2'
            GROUP BY ORG_CODE, DCS_NO, DCS_YM, DCS_SLNO,DCS_SLNO;
        */
        $dcsDetails= DB::select("SELECT DISTINCT ORG_CODE, DCS_NO, DCS_YM,SUM(PR_AMT)DCS_AMT,SUM(NVL(PR_AMT,0)) DEP_AMT,DCS_SLNO
        FROM PRCOLLECTION.PR_INFO WHERE DCS_NO=:DCS_NO
        AND NVL(PR_STATUS,'X')<>'2'
        GROUP BY ORG_CODE, DCS_NO, DCS_YM, DCS_SLNO,DCS_SLNO", 
        ['DCS_NO' => $DCS_NO]);    
        
        if (!($dcsDetails)) {
            return response(['message' => 'DCS details does not exist'], 400);
           }        
   
          $dcsDetails = json_decode( json_encode($dcsDetails), true);
          
          return response([ 'dcs_details' => ApiResource::collection($dcsDetails), 'message' => 'Success'], 200);        
    }

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

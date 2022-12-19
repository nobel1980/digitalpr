<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;
use App\Models\Pr_Info;
use Carbon\Carbon;
use DB;

class digitalprController extends Controller
{
    /*
     User information
     @ user login with user and pass
     @ after successfyul login...... 
    */
    public function user_dcs_office(Request $request)
    {
        /*
          @ Sample SQL query
          $USERCODE = '00005942';
          $dcsOffice = DB::select('SELECT * from SYUSR.SYUSRMAS_ALL@ONLINEPAY_DBLK WHERE USERCODE = :USERCODE', ['USERCODE' => $USERCODE]);
          $policies = DB::select('SELECT POLICY_NO, INSTPREM AS AMOUNT, STATUS AS POLICY_STATUS, PROPOSER, DOB, MOBILE from POLICY.POLICY_ALL WHERE STATUS = 1 AND POLICY_NO = :policy_no', ['POLICY_NO' => $polNo]);
          $DCS_ZONE_CODE = user['zone_code'];
          $DCS_ZONE_CODE = '030';
          $PTYPE = 'SB';
        */
        $header = $request->header('Authorization');
        $DCS_ZONE_CODE = $request->header('zonecode');
        $PTYPE = $request->header('ptype');
        
        if ($PTYPE =1)
           { $PTYPE = 'EKOK'; }
        elseif($PTYPE =2)
           { $PTYPE ='SB'; }   
        //print_r($request->headers->all());
  

        $dcsOffice = DB::select('SELECT A.OFF_NAME AS AGENCY_NAME,A.AGENCY_CODE,A.Z_NAME, A.Z_CODE, A.SC_NAME, A.SC_CODE, A.DIV_NAME, A.DIV_CODE,MOTHER_OFFICE
        FROM DEV_ADMIN.OFF_ALL A
        WHERE A.Z_CODE=:DCS_ZONE_CODE
        AND A.PRJ=:PTYPE AND A.AGENCY_CODE IS NOT NULL', 
        ['DCS_ZONE_CODE' => $DCS_ZONE_CODE, 'PTYPE' => $PTYPE]);    
        
        if (!($dcsOffice)) {
            return response(['message' => 'DCS office does not exist'], 400);
           }        
   
          $dcsOffice = json_decode( json_encode($dcsOffice), true);
          
          return response([ 'office_info' => ApiResource::collection($dcsOffice), 'message' => 'Success'], 200);        
    }

    /*
     @ Deposit type information
     @  Parameter receipt type PR/MR
    */
    public function deposit_type(Request $request)
    {
        /*
          @ Sample SQL query
          SELECT DEPCODE, DEPNAME FROM PRCOLLECTION.DEPTYPE WHERE DTYPE='1' ORDER BY DEPCODE;
        */

        $rcptType = $request->all();
        $rcptType = $rcptType['rcpt_type'];
  
        // dd($rcptType);
        // exit();

        $depType = DB::select('SELECT DEPCODE, DEPNAME
        FROM PRCOLLECTION.DEPTYPE
        WHERE DTYPE=:dep_type', 
        ['dep_type' => $rcptType]);    
        
        if (!($depType)) {
            return response(['message' => 'Deposit type does not exist'], 400);
           }        
   
          $depType = json_decode( json_encode($depType), true);
          
          return response([ 'deposit_type' => ApiResource::collection($depType), 'message' => 'Success'], 200);        
    }

      
    /* 
      @ Proposal info for New policy 
    */  
    public function proposal_info(Request $request)
    {
        $propInfo = $request->all();
        $propNo = $propInfo['proposal_no'];
        $pjType = $propInfo['pj_type'];

        $rules=[
            'proposal_no'=>'required',
            'pj_type'=>'required'     
        ];

        $customMessage=[
            'proposal_no.required'=>'Proposal Number is required',
            'pj_type.required'=>'Project type is required'
        ];

        $validator=Validator::make($propInfo,$rules,$customMessage);
         
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        /*
         @ Sample SQL query by Nazmul sir
         @ select * from POLICY.APPS_POLICY_ALL where proposal_n = 'E00047/026200/134/2019' AND PTYPE = 'EKOK';
         SELECT A.* FROM POLICY.V_POLICY_DETAILS A 
            WHERE A.POLICY_NO='F00187/070300/128/2018' 
            AND DECODE(A.PTYPE,'EKOK','EKOK','FDPS','EKOK','SB')='EKOK';
        
            $propNo = E00067/010100/182/2022
            $propInfo = DB::table('POLICY.APPS_POLICY_ALL')
                    ->select(DB::raw('*'))
                    ->where('PROPOSAL_N', '=', $propNo)
                    ->where('PTYPE', '=', $pjType)
                    ->get();  
                            
        
        $propInfo = DB::table('POLICY.V_POLICY_DETAILS A')
                    ->select(DB::raw('*'))
                    ->where('PROPOSAL_N', '=', $propNo)
                    ->where('PTYPE', '=', $pjType)
                    ->get();    
                    
        */            
            $propInfo = DB::select('SELECT *
                FROM POLICY.V_POLICY_DETAILS A
                WHERE PROPOSAL_N=:dep_type AND PTYPE=:ptype', 
                ['dep_type' => $propNo, 'ptype' => $pjType]);            
      
        if (!($propInfo)) {
            return response(['message' => 'Invalid Proposal Number'], 400);
            }        
    
        $propInfo = json_decode( json_encode($propInfo), true);

// dd($propInfo);
// exit();
        
        return response([ 'proposal_info' => ApiResource::collection($propInfo), 'message' => 'Success'], 200);        
    }

      /* Policy info for Renew policy or Deffered */
      public function policy_info(Request $request)
      {
        $policyInfo = $request->all();
        $polNo = $policyInfo['policy_no'];
        
        /* @ validation check */
        $rules=[
            'policy_no'=>'required'      
        ];

        $customMessage=[
            'policy_no.required'=>'Policy Number is required',
        ];

        $validator=Validator::make($policyInfo,$rules,$customMessage);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        /*
         SELECT A.POLICY_NO,PROPOSAL_N,A.PROPOSER,RISKDATE,MOBILE,TABLE_ID,TERM,INSTMODE,SUM_INSURE,
                    INSTPREM AS TOTAL_PREM,COUNTRY_CODE,ISO_CODE,ACC_CODE,ACC_NAME FROM POLICY.APPS_POLICY_ALL A WHERE POLICY_NO = '1330001452';
        */
        $policyInfo = DB::table('POLICY.APPS_POLICY_ALL A')
                    ->select(DB::raw('A.POLICY_NO,PROPOSAL_N,A.PROPOSER,RISKDATE, NEXTPREM, MATURITY, MOBILE,TABLE_ID,TERM,INSTMODE,SUM_INSURE,
                    INSTPREM AS TOTAL_PREM,COUNTRY_CODE,ISO_CODE,ACC_CODE,ACC_NAME, STATUS'))
                    ->where('POLICY_NO', '=', $polNo)
                    ->get();             

        if (!($policyInfo)) {
            return response(['message' => 'Invalid Policy Number.'], 400);
           }        
    
          $policyInfo = json_decode( json_encode($policyInfo), true);

           $riskDate = Carbon::parse($policyInfo['0']['riskdate'])->format('d-m-Y');
           $nextPrem = Carbon::parse($policyInfo['0']['nextprem'])->format('d-m-Y');
           $maturity = Carbon::parse($policyInfo['0']['maturity'])->format('d-m-Y');
           $policyInfo['0']['riskdate'] = $riskDate;
           $policyInfo['0']['nextprem'] = $nextPrem;
           $policyInfo['0']['maturity'] = $maturity;
          
          return response([ 'policy_info' => ApiResource::collection($policyInfo), 'message' => 'Success'], 200);        
      }
      
    /*
     @ Agent/FA information
     @ only active and certified FA will be return
     @ public function agent_info(Request $request, $FaCode)
    */
    public function agent_info(Request $request)
    {
      $agentInfo = $request->all();
      $FaCode = $agentInfo['fa_code'];

      // validation check
      $rules=[
        'fa_code'=>'required'      
     ];

     $customMessage=[
        'fa_code.required'=>'Agent code is required',
     ];

     $validator=Validator::make($agentInfo,$rules,$customMessage);

     if($validator->fails()){
         return response()->json($validator->errors(),422);
     }
 
      // SELECT PRJ, CODE, NAME, STATUS, LIC_CER_STATUS FROM DEV_ADMIN.ORG_ALL_EKOK_SB WHERE STATUS = 'A' AND LIC_CER_STATUS = 'E' AND  CODE='90084980';
      $agentInfo = DB::table('DEV_ADMIN.ORG_ALL_EKOK_SB')
                  ->select(DB::raw('PRJ, CODE, NAME, STATUS, LIC_CER_STATUS'))
                  ->where('STATUS', '=', 'A')
                  ->where('LIC_CER_STATUS', '=', 'E')
                  ->where('CODE', '=', $FaCode)
                  ->get();             
                  

      if (!($agentInfo)) {
          return response(['message' => 'FA does not exist or expired'], 400);            
         }        
 
        $agentInfo = json_decode( json_encode($agentInfo), true);
        
        return response([ 'agent_info' => ApiResource::collection($agentInfo), 'message' => 'Success'], 200);        
    }

      /*
         @  Next premium date for Renew policy or Deffered
         @  Need to revised/Test
      */   
      public function next_prem_date(Request $request)
      {
        $policyInfo = $request->all();
        $plan = $policyInfo['plan'];
        $term = $policyInfo['term'];
        $payMode = $policyInfo['paymode'];
        $riskdate = $policyInfo['riskdate'];
        $TotInstPaid = $policyInfo['TotInstpaid'];
        
        /* 
        @ validation check 
        */
        $rules=[
            'plan'=>'required',
            'term'=>'required',
            'paymode'=>'required',
            'riskdate'=>'required',
            'TotInstpaid'=>'required'
        ];

        $customMessage=[
            'plan.required'=>'Plan is required',
            'term.required'=>'Term is required',
            'paymode.required'=>'Pay mode is required',
            'riskdate.required'=>'Risk date is required',
            'TotInstpaid.required'=>'Total Install paid is required'
        ];

        $validator=Validator::make($policyInfo,$rules,$customMessage);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        $riskdate = Carbon::parse($riskdate);

        // dd($riskdate);
        // exit;
        /*
        @ Sample SQL Query
         select  POLICY.PKG_FILIC.F_PNEXTPAY@ONLINEPAY_DBLK('12' ,'04','1','25-MAY-2011','2') AS NEXT_PREM_DATE FROM DUAL;
         select TO_DATE(POLICY.PKG_FILIC.F_PNEXTPAY ('12' ,'04','1','25-MAY-2011','2'),'YYYY-MM-DD') AS NEXT_PREM_DATE FROM DUAL;         
        */

        $nextPremDate = DB::select("SELECT  TO_CHAR( POLICY.PKG_FILIC.F_PNEXTPAY(:plan, :term, :paymode, : riskdate, :TotInstpaid), 'DD-MM-YYYY') AS NEXT_PREM_DATE FROM DUAL", ['PLAN' => $plan, 'TERM' => $term, 'PAYMODE' => $payMode, 'RISKDATE' => $riskdate, 'TotInstpaid'=> $TotInstPaid]);       
        //$nextPremDate = DB::select( "SELECT  TO_CHAR(to_date(POLICY.PKG_FILIC.F_PNEXTPAY('12' ,'04','1','25-MAY-2011','2'), 'DDMMRRRR'),'DDMMYYYY') AS NEXT_PREM_DATE FROM DUAL");
                    
        
        if (!($nextPremDate)) {
            return response(['message' => 'Invalid data.'], 400);
           }        
   
          $nextPremDate = json_decode( json_encode($nextPremDate), true);
          
          return response([ 'next_prem_date' => ApiResource::collection($nextPremDate), 'message' => 'Success'], 200);        
      }

      /*
        @ Commission calculation for new policy (FA, UM, BM)
        @ POLICY_DBL - for live connection
      */
      public function commission(Request $request)
      {
        $propInfo = $request->all();
        $depType = $propInfo['depType'];
        $depCode = $propInfo['depCode'];
        $propNo = $propInfo['propNo'];
        $tableId = $propInfo['tableId'];
        $term = $propInfo['term'];
        $instMode = $propInfo['instMode'];
        $sumAssure = $propInfo['sumAssure'];
        $policyForClaim = $propInfo['policyForClaim'];
        $riskdate = $propInfo['riskdate'];
        $receptAmt = $propInfo['receptAmt'];
        $currdate  = Carbon::now()->toDateTimeString(); 
        //$currdate  = Carbon::now()->format('d-M-Y'); 

       
        /* @ validation check */
        $rules=[
            'depType'=>'required',
            'depCode'=>'required',
            'propNo'=>'required',
            'tableId'=>'required',
            'term'=>'required',
            'instMode'=>'required',
            'sumAssure'=>'required',
            'policyForClaim'=>'required',
            'riskdate'=>'required',
            'receptAmt'=>'required'
        ];

        $customMessage=[
            'depType.required'=>'Deposite type is required',
            'depCode.required'=>'Deposite code is required',
            'propNo.required'=>'Proposal No is required',
            'tableId.required'=>'Table ID is required',
            'term.required'=>'Term is required',
            'instMode.required'=>'Installment mode is required',
            'sumAssure.required'=>'Sum assured is required',
            'policyForClaim.required'=>'Policy for claim is required',
            'riskdate.required'=>'Risk date is required',
            'receptAmt.required'=>'Receipt amount is required'
        ];

        $validator=Validator::make($propInfo,$rules,$customMessage);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        
        /*
          @ Sample SQL query
            SELECT FA AS FA,UM,BM FROM POLICY.PROPOSAL_ALL WHERE PROPOSAL_N='E00047/026200/134/2019' AND ROWNUM=1;
        */    
                    
        $agentInfo = DB::table('POLICY.PROPOSAL_ALL')
        ->select(DB::raw('FA, UM, BM'))        
        ->where('PROPOSAL_N', '=', $propNo)
        ->get();    
        
        /* 
          @ Sample SQL Query        
            SELECT NVL(PRCOLLECTION.COMM_ADJSUTABLE_STATUS@ONLINEPAY_DBLK('E00011/012900/228/2022','01','10','1','110000','N',NULL,'PR'),'N') AS STATUS FROM DUAL;
          @ Query for proposal status (Y/N)
          @ Work properly
        */
       
        $comStatus = DB::select("SELECT NVL(PRCOLLECTION.COMM_ADJSUTABLE_STATUS(:propNo, :TABLE_ID, :TERM, :INSTMODE, :sumAssure, :policyForClaim,  'NULL', 'PR'),'N') AS STATUS FROM DUAL", 
        ['propNo' => $propNo, 'TABLE_ID' => $tableId, 'TERM' => $term, 'INSTMODE' => $instMode, 'sumAssure' => $sumAssure, 'policyForClaim' =>$policyForClaim]);
        
        $comStatus = json_decode( json_encode($comStatus), true);


        /*
        @ Sample SQL Query
          $FaComm = DB::select("SELECT FLOOR(policy.pkg_comm.COMMISSION@POLICY_DBL(SYSDATE,'12-MAY-2011','1','X','01','10',0,'50000','N')*1-
          ((policy.pkg_comm.COMMISSION@POLICY_DBL(SYSDATE,'12-MAY-2011','1','X','01','10',0,'50000','N')*1)*15)/100)AS FA FROM DUAL", 
          );
        @ working properly
        */


    if ($comStatus[0]['status'] =='Y')
       {
        if($depType=='1' AND $depCode=='01' ){
            /*Start table check */
            if($tableId=='07'){
            $faComm = DB::select("SELECT FLOOR(policy.pkg_comm.COMMISSION(:currdate , :riskdate, '1', 'X', :TABLE_ID, :TERM, 0,  :receptAmt, 'N')*1 -
                ((policy.pkg_comm.COMMISSION(:currdate, :riskdate,'1','X', :TABLE_ID, :TERM, 0, :receptAmt, 'N')*1)*5)/100) AS FA FROM DUAL", 
                ['currdate' => $currdate,  'RISKDATE' => $riskdate, 'TABLE_ID' => $tableId, 'TERM' => $term, 'receptAmt' => $receptAmt]);


            $umComm = DB::select("SELECT FLOOR(policy.pkg_comm.COMMISSION(:currdate , :riskdate, '2', 'X', :TABLE_ID, :TERM, 0,  :receptAmt, 'N')*1 -
                ((policy.pkg_comm.COMMISSION(:currdate, :riskdate,'2','X', :TABLE_ID, :TERM, 0, :receptAmt, 'N')*1)*5)/100) AS UM FROM DUAL", 
                ['currdate' => $currdate,  'RISKDATE' => $riskdate, 'TABLE_ID' => $tableId, 'TERM' => $term, 'receptAmt' => $receptAmt]);


            $bmComm = DB::select("SELECT FLOOR(policy.pkg_comm.COMMISSION(:currdate , :riskdate, '3', 'X', :TABLE_ID, :TERM, 0,  :receptAmt, 'N')*1 -
                ((policy.pkg_comm.COMMISSION(:currdate, :riskdate,'3','X', :TABLE_ID, :TERM, 0, :receptAmt, 'N')*1)*5)/100) AS BM FROM DUAL", 
                ['currdate' => $currdate,  'RISKDATE' => $riskdate, 'TABLE_ID' => $tableId, 'TERM' => $term, 'receptAmt' => $receptAmt]);


                $faComm = json_decode( json_encode($faComm), true);
                $umComm= json_decode( json_encode($umComm), true);
                $bmComm = json_decode( json_encode($bmComm), true);

                $Com =  (array_merge($faComm,$umComm, $bmComm));

                $Comm[0]['fa'] = $Com[0]['fa'];
                $Comm[0]['um'] = $Com[1]['um'];
                $Comm[0]['bm'] = $Com[2]['bm'];
                
            }else{
            $faComm = DB::select("SELECT FLOOR(policy.pkg_comm.COMMISSION(:currdate , :riskdate, '1', 'X', :TABLE_ID, :TERM, 0,  :receptAmt, 'N')*1 -
                ((policy.pkg_comm.COMMISSION(:currdate, :riskdate,'1','X', :TABLE_ID, :TERM, 0, :receptAmt, 'N')*1)*15)/100) AS FA FROM DUAL", 
                ['currdate' => $currdate,  'RISKDATE' => $riskdate, 'TABLE_ID' => $tableId, 'TERM' => $term, 'receptAmt' => $receptAmt]);


            $umComm = DB::select("SELECT FLOOR(policy.pkg_comm.COMMISSION(:currdate , :riskdate, '2', 'X', :TABLE_ID, :TERM, 0,  :receptAmt, 'N')*1 -
                ((policy.pkg_comm.COMMISSION(:currdate, :riskdate,'2','X', :TABLE_ID, :TERM, 0, :receptAmt, 'N')*1)*15)/100) AS UM FROM DUAL", 
                ['currdate' => $currdate,  'RISKDATE' => $riskdate, 'TABLE_ID' => $tableId, 'TERM' => $term, 'receptAmt' => $receptAmt]);


            $bmComm = DB::select("SELECT FLOOR(policy.pkg_comm.COMMISSION(:currdate , :riskdate, '3', 'X', :TABLE_ID, :TERM, 0,  :receptAmt, 'N')*1 -
                ((policy.pkg_comm.COMMISSION(:currdate, :riskdate,'3','X', :TABLE_ID, :TERM, 0, :receptAmt, 'N')*1)*15)/100) AS BM FROM DUAL", 
                ['currdate' => $currdate,  'RISKDATE' => $riskdate, 'TABLE_ID' => $tableId, 'TERM' => $term, 'receptAmt' => $receptAmt]);

            }
          /* End table check */  
          
          $faComm = json_decode( json_encode($faComm), true);
          $umComm = json_decode( json_encode($umComm), true);
          $bmComm = json_decode( json_encode($bmComm), true);

          $Comm[0] = array_merge($faComm[0], $umComm[0], $bmComm[0]);
 
        }else{
            $Comm[0]['fa'] = 0;
            $Comm[0]['um'] = 0;
            $Comm[0]['bm'] = 0;
        } 
        
      }     
 
        if (!($Comm)) {
            return response(['message' => 'Invalid Policy Number.'], 400);
           }        
   
          $Comm = json_decode( json_encode($Comm), true);
          
          return response([ 'comm_info' => ApiResource::collection($Comm), 'message' => 'Success'], 200);        
      }


     /*
      * Store successfull PR data
     */
    public function submit_prinfo(Request $request)
    {    
        $prInfo = $request->all();
   
        $user= auth()->user(); 

        $prInfo['USER_ID'] = $user->emp_code;
        $prInfo['USER_NAME'] = $user->name;
        $prInfo['DCS_ORG_CODE'] = $user->dcs_org_code;
        $prInfo['ZONE_CODE'] = $user->zone_code;
        $prInfo['ORG_CODE'] = $user->org_code;
        $prInfo['PR_DATE']  = Carbon::now()->toDateTimeString();
        $prInfo['DCS_DATE']  = Carbon::now()->format('Ymd');

        /*
        Condition required here
        if (IFNULL (RECEIVEAMT,0) > 0){}
        @ PR number generate
        */
        if($prInfo['DTYPE']=='1'){
            if($prInfo['PTYPE']=='EKOK'){
                $prNo = DB::select("SELECT 'EP'|| LPAD(PRCOLLECTION.PRNUMBER.NEXTVAL,10,'0') AS NEXTVAL   FROM DUAL");
                $prNo = json_decode( json_encode($prNo), true);
            }
            elseif($prInfo['PTYPE']=='SB'){
                $prNo = DB::select("SELECT 'SP'||LPAD(PRCOLLECTION.PRNUMBER_SB.NEXTVAL,10,'0') AS NEXTVAL FROM DUAL");
                $prNo = json_decode( json_encode($prNo), true);
                
            }            
        }
        elseif($prInfo['DTYPE']=='2'){
            if($prInfo['PTYPE']=='EKOK'){
                $prNo = DB::select("SELECT 'EM'|| LPAD(PRCOLLECTION.PRNUMBER.NEXTVAL,10,'0') AS NEXTVAL   FROM DUAL");
                $prNo = json_decode( json_encode($prNo), true);
            }
            elseif($prInfo['PTYPE']=='SB'){
                $prNo = DB::select("SELECT 'SM'|| LPAD(PRCOLLECTION.PRNUMBER.NEXTVAL,10,'0') AS NEXTVAL   FROM DUAL");
                $prNo = json_decode( json_encode($prNo), true);

            }
            else{
                return response([ 'message' => 'Failed'], 400); 
            }
        }

        dd($prNo);
        exit();

        /* Generate DCS number start */
        if($prInfo['PTYPE']=='EKOK'){
            $V_DCS_NO='FE'.$prInfo['DCS_ORG_CODE'].'-'.$prInfo['ZONE_CODE'].'-'.$prInfo['DCS_DATE'];
        }elseif($prInfo['PTYPE']=='SB'){            
            $V_DCS_NO='FS'.$prInfo['DCS_ORG_CODE'].'-'.$prInfo['ZONE_CODE'].'-'.$prInfo['DCS_DATE'];
        }

        $prInfo['DCS_NO'] = $V_DCS_NO;
        /* Generate DCS number end */       
  
        //Insert in test table DIGITALPR.PR_INFO
        //$prInfo = DB::table( 'Pr_Info' )->insert( $prInfo );

        $prInfo = json_decode( json_encode($prInfo), true);
        return response([ 'pr_info' => ApiResource::collection($prInfo), 'message' => 'Success'], 200);        
    }


    /*
     @ Account head information
     @ GET method
     @ ACC_TYPE : MR
     @  Parameter PRJ_CODE :  EKOK/SB
    */
    public function acc_head(Request $request)
    {
      $accHead = $request->all();
      $prjType = $accHead['prj_type'];

      /* validation check */
      $rules=[
        'prj_type'=>'required'      
     ];

     $customMessage=[
        'prj_type.required'=>'Project type is required',
     ];

     $validator=Validator::make($accHead,$rules,$customMessage);

     if($validator->fails()){
         return response()->json($validator->errors(),422);
     }
 
      /*
        @ Sample SQL query
        SELECT DISTINCT NAME3 ACC_NAME,SUBCODE2,SUBCODE1,NAME2,CODE,NAME1,ALL_CODE FROM ACC.DCS_CREDIT_VOUCHER_ACC@POLICY_DBL
        WHERE ACC_TYPE='MR' 
        AND PRJ_CODE=:PTYPE
        ORDER BY CODE,SUBCODE1,SUBCODE2;       
       */           
        $accHead = DB::select('SELECT DISTINCT NAME3 ACC_NAME,SUBCODE2,SUBCODE1,NAME2,CODE,NAME1,ALL_CODE FROM ACC.DCS_CREDIT_VOUCHER_ACC
        WHERE ACC_TYPE=:ACC_TYPE
        AND PRJ_CODE=:PRJ_CODE',
        ['ACC_TYPE' => 'MR', 'PRJ_CODE' => $prjType]);            
                       
      if (!($accHead)) {
          return response(['message' => 'account name does not exist'], 400);            
         }        
 
        $accHead = json_decode( json_encode($accHead), true);
        
        return response([ 'acc_head' => ApiResource::collection($accHead), 'message' => 'Success'], 200);        
    }
}

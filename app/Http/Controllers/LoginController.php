<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Frammers;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public $key = "trr_key";

    public function genToken($id, $name)
    {
        $payload = array(
            "iss" => "trr",
            "aud" => $id,
            "lun" => $name,
            "iat" => Carbon::now()->timestamp,
            // "exp" => Carbon::now()->timestamp + 86400,
            "exp" => Carbon::now()->timestamp + 31556926,
            "nbf" => Carbon::now()->timestamp,
        );

        $token = JWT::encode($payload, $this->key);
        return $token;
    }

    public function checkLogin(Request $request)
    {
        $header = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $header);

        try {

            if ($token == "") {
                return $this->returnError('Token Not Found', 401);
            }

            $payload = JWT::decode($token, $this->key, array('HS256'));
            $payload->exp = Carbon::now()->timestamp + 86400;
            $token = JWT::encode($payload, $this->key);

            return response()->json([
                'code' => '200',
                'status' => true,
                'message' => 'Active',
                'data' => [],
                'token' => $token,
            ], 200);
        } catch (\Firebase\JWT\ExpiredException $e) {

            list($header, $payload, $signature) = explode(".", $token);
            $payload = json_decode(base64_decode($payload));
            $payload->exp = Carbon::now()->timestamp + 86400;
            $token = JWT::encode($payload, $this->key);

            return response()->json([
                'code' => '200',
                'status' => true,
                'message' => 'Token is expire',
                'data' => [],
                'token' => $token,
            ], 200);
        } catch (Exception $e) {
            return $this->returnError('Can not verify identity', 401);
        }
    }

    public function login(Request $request)
    {
        if (!isset($request->email)) {
            return $this->returnErrorData('[email] ไม่มีข้อมูล', 404);
        } else if (!isset($request->password)) {
            return $this->returnErrorData('[password] ไม่มีข้อมูล', 404);
        }

        $user = User::where('email', $request->email)
            ->where('password', md5($request->password))
            // ->where('status', 'Yes')
            ->first();

        if ($user) {

            //log
            $username = $user->email;
            $log_type = 'เข้าสู่ระบบ';
            $log_description = 'ผู้ใช้งาน ' . $username . ' ได้ทำการ ' . $log_type;
            $this->Log($username, $log_description, $log_type);
            //

            return response()->json([
                'code' => '200',
                'status' => true,
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'data' => $user,
                'token' => $this->genToken($user->id, $user),
            ], 200);
        } else {
            return $this->returnError('รหัสผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง', 401);
        }
    }

    public function requestOTP(Request $request)
    {
        if (!isset($request->tel)) {
            return $this->returnErrorData('กรุณาระบุเบอร์โทรศัพท์ให้เรียบร้อย', 404);
        }
        $tel = $request->tel;

        $otpKey = $this->sendOTP($tel, true);

        if ($otpKey['status'] != "success") {
            return $this->returnErrorData('ระบบ OTP ขัดข้อง กรุณาติดต่อเจ้าหน้าที่', 404);
        }

        DB::beginTransaction();
        try {
            $Otp = new Otp();
            $Otp->tel = $tel;
            $Otp->otp_code = null;
            $Otp->otp_ref = $otpKey['refno'];
            $Otp->otp_exp = null;
            $Otp->token = $otpKey['token'];
            $Otp->otp_type = 'login_tel';
            $Otp->save();
            //
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();

            return $this->returnError('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage());
        }

        return response()->json([
            'code' => '200',
            'status' => true,
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'data' => null,
            'otp' => $Otp
        ], 200);
    }

    public function confirmOtp(Request $request)
    {
        $tel = $request->tel;
        $otpCode = $request->otp_code;
        $otpRef = $request->otp_ref;
        $tokenOtp = $request->token_otp;

        $deviceNo = $request->device_no;
        $notifyToken = $request->notify_token;

        if (!isset($tel)) {
            return $this->returnErrorData('กรุณาระบุเบอร์โทรศัพท์ให้เรียบร้อย', 404);
        } elseif (!isset($otpCode)) {
            return $this->returnErrorData('กรุณาระบุรหัส OTP ให้เรียบร้อย', 404);
        } elseif (!isset($otpRef)) {
            return $this->returnErrorData('กรุณาระบุ OTP Ref ให้เรียบร้อย', 404);
        }

        DB::beginTransaction();

        try {

            // check otp
            $otpIsExist = $this->verifyOTP($otpCode, $tokenOtp, true);


            if (!$otpIsExist) {
                return $this->returnError('รหัส OTP ไม่ถูกต้อง');
            }

            $otpIsExist =  Otp::where('tel', $tel)
                ->where('otp_ref', $otpRef)
                ->where('token', $tokenOtp)
                ->first();

            if (!$otpIsExist) {
                return $this->returnError('รหัส OTP ไม่ถูกต้อง');
            }

            // update otp
            $otpIsExist->status = true;
            $otpIsExist->save();

            DB::commit();

            return response()->json([
                'code' => '200',
                'status' => true,
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'data' => $otpIsExist,
                // 'token' => $this->genToken($getUser->id, $getUser),

            ], 200);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 404);
        }
    }


    public function loginApp(Request $request)
    {

        $qouta_id = $request->qouta_id;

        if (!isset($qouta_id)) {
            return $this->returnErrorData('[qouta_id] ไม่มีข้อมูล', 404);
        }

        $Item = Frammers::where('qouta_id', $qouta_id)->first();

        if ($Item) {

            //app
            $deviceNo = $request->device_no;
            $notifyToken = $request->notify_token;

            if ($deviceNo && $notifyToken) {
                //check device
                $deviceIsExist =  Device::where('device_no', $deviceNo)
                    // ->where('notify_token', $notifyToken)
                    ->where('status', true)
                    ->where('qouta_id',  $qouta_id)
                    ->first();

                if (!$deviceIsExist) {
                    //add
                    $device = new Device();
                    $device->qouta_id =  $qouta_id;
                    $device->device_no =  $deviceNo;
                    $device->notify_token =  $notifyToken;
                    $device->status =  true;
                    $device->save();
                } else {
                    //update
                    $deviceIsExist->qouta_id =  $qouta_id;
                    $deviceIsExist->device_no =  $deviceNo;
                    $deviceIsExist->notify_token =  $notifyToken;
                    $deviceIsExist->status =  true;
                    $deviceIsExist->save();
                }
                //

            }

            return response()->json([
                'code' => '200',
                'status' => true,
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'data' => $Item,
                'token' => null,
            ], 200);
        } else {
            return $this->returnError('รหัสผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง', 401);
        }
    }
}

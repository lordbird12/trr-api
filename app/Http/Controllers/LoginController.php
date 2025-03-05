<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Frammers;
use App\Models\Otp;
use App\Models\User;
use App\Models\NotiSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public $key = "trr_key";

    public function genToken($id, $name, $expirationDays = 365)
    {
        $currentTimestamp = Carbon::now()->timestamp;

        // Calculate expiration time dynamically based on the given number of days
        $expirationTimestamp = $currentTimestamp + ($expirationDays * 86400); // 86400 seconds in a day
    
        $payload = [
            "iss" => "trr_key",       // Issuer
            "aud" => $id,             // Audience (User ID)
            "lun" => $name,           // Custom claim (User Name)
            "iat" => $currentTimestamp, // Issued At
            "exp" => $expirationTimestamp, // Expiration
            "nbf" => $currentTimestamp,   // Not Before
        ];
    
        // Generate the token
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

            if($request->remember_me == true){
                $token = $this->genToken($user->id, $user);
            }else{
                $token = $this->genToken($user->id, $user,1);
            }

            return response()->json([
                'code' => '200',
                'status' => true,
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'data' => $user,
                'token' => $token,
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

        if (!$Item) {
            $Item = new Frammers();
            $Item->qouta = $qouta_id;
            $Item->qouta_id = $qouta_id;
            $Item->save();

            $ItemNoti = new NotiSetting();
            $ItemNoti->qouta_id = $qouta_id;
            $ItemNoti->noti_1 = "No";
            $ItemNoti->noti_2 = "No";
            $ItemNoti->noti_3 = "No";
            $ItemNoti->noti_4 = "No";
            $ItemNoti->noti_5 = "No";
            $ItemNoti->noti_6 = "No";
            $ItemNoti->noti_7 = "No";
            $ItemNoti->noti_8 = "No";
            $ItemNoti->save();
        }

        if ($Item) {

            $ItemNoti = NotiSetting::where('qouta_id', $qouta_id)->first();
            if(!$ItemNoti){
                $ItemNoti = new NotiSetting();
                $ItemNoti->qouta_id = $qouta_id;
                $ItemNoti->noti_1 = "No";
                $ItemNoti->noti_2 = "No";
                $ItemNoti->noti_3 = "No";
                $ItemNoti->noti_4 = "No";
                $ItemNoti->noti_5 = "No";
                $ItemNoti->noti_6 = "No";
                $ItemNoti->noti_7 = "No";
                $ItemNoti->noti_8 = "No";
                $ItemNoti->save();
            }
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

              //log
              $userId = $deviceNo;
              $type = 'loginapp';
              $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
              $this->Log($userId, $description, $type);
              //

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

    public function sendPasswordReset1(Request $request)
    {
        $otpKey = $this->sendOTP('0815254225', true);

        // 1. ตรวจสอบว่าอีเมลมีอยู่ในระบบหรือไม่
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // 2. สร้างรหัสผ่านใหม่
        $newPassword = $this->generateRandomPassword();

        // 3. อัปเดตรหัสผ่านในระบบ
        $user->password = "96e79218965eb72c92a549dd5a330112";
        $user->save();

        // 4. ส่งอีเมลพร้อมรหัสผ่านใหม่
        $this->sendEmail($request->email, $newPassword);

        return response()->json(['message' => 'New password has been sent to your email']);
    }

    public function sendPasswordReset(Request $request)
    {

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['message' => 'phone not found'], 404);
        }

        $otpKey = $this->sendOTP($request->phone, true);
        $otpKey['phone'] = $request->phone;
        return response()->json([
            'code' => '200',
            'status' => true,
            'message' => 'กรุณาตรวจสอบ OTP',
            'data' => $otpKey,
            
            'token' => null,
        ], 200);
    }

    private function generateRandomPassword($length = 8)
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }

    private function sendEmail($email, $newPassword)
    {
        $subject = "แจ้งรหัสผ่านเข้าใช้งานระบบ TRR";
        $htmlMessage = "
<!DOCTYPE html>
<html lang='th'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>รีเซ็ตรหัสผ่าน</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #0056b3;
            text-align: center;
        }
        p {
            margin: 10px 0;
        }
        .password-box {
            text-align: center;
            margin: 20px 0;
        }
        .password-box strong {
            font-size: 20px;
            color: #0056b3;
            background: #f0f8ff;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <h1>รีเซ็ตรหัสผ่านของคุณ</h1>
        <p>เรียนผู้ใช้งาน,</p>
        <p>เราได้สร้างรหัสผ่านใหม่สำหรับบัญชีของคุณเรียบร้อยแล้ว กรุณาใช้รหัสผ่านด้านล่างนี้เพื่อเข้าสู่ระบบ:</p>
        <div class='password-box'>
            <strong>111111</strong>
        </div>
        <p>เราขอแนะนำให้คุณเปลี่ยนรหัสผ่านทันทีหลังจากที่เข้าสู่ระบบเพื่อความปลอดภัยของบัญชีของคุณ</p>
        <p>หากคุณไม่ได้ร้องขอการรีเซ็ตรหัสผ่าน โปรดติดต่อฝ่ายสนับสนุนทันที</p>
        <p>ขอบคุณ,</p>
        <p>ทีมงานสนับสนุน</p>
        <div class='footer'>
            <p>© 2023 Asha Tech. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
";
    
        Mail::html($htmlMessage, function ($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject);
        });
    }


    public function confirmOtpReset(Request $request)
    {
        $otpCode = $request->otp_code;
        $tokenOtp = $request->token_otp;


        if (!isset($tokenOtp)) {
            return $this->returnErrorData('กรุณาระบุเบอร์โทรศัพท์ให้เรียบร้อย', 404);
        } elseif (!isset($otpCode)) {
            return $this->returnErrorData('กรุณาระบุรหัส OTP ให้เรียบร้อย', 404);
        }

        DB::beginTransaction();

        try {

            // check otp
            $otpIsExist = $this->verifyOTP($otpCode, $tokenOtp, true);


            if (!$otpIsExist) {
                return $this->returnError('รหัส OTP ไม่ถูกต้อง');
            }


            DB::commit();

            return response()->json([
                'code' => '200',
                'status' => true,
                'message' => 'ตรวจสอบ OTP ถูกต้อง',
                'data' => $otpIsExist,
                // 'token' => $this->genToken($getUser->id, $getUser),

            ], 200);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 404);
        }
    }
}

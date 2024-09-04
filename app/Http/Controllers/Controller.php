<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\Device;
use App\Models\Inquiry_type;
use App\Models\Log;
use App\Models\Notify_log;
use App\Models\Notify_log_user;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $notification;

    public function __construct()
    {
        $this->notification = Firebase::messaging();
    }

    public function returnSuccess($massage, $data)
    {

        return response()->json([
            'code' => strval(200),
            'status' => true,
            'message' => $massage,
            'data' => $data,
        ], 200);
    }

    public function returnUpdate($massage)
    {
        return response()->json([
            'code' => strval(201),
            'status' => true,
            'message' => $massage,
            'data' => [],
        ], 201);
    }

    public function returnUpdateReturnData($massage, $data)
    {
        return response()->json([
            'code' => strval(201),
            'status' => true,
            'message' => $massage,
            'data' => $data,
        ], 201);
    }

    public function returnErrorData($massage, $code)
    {
        return response()->json([
            'code' => strval($code),
            'status' => false,
            'message' => $massage,
            'data' => [],
        ], 404);
    }

    public function returnError($massage)
    {
        return response()->json([
            'code' => strval(401),
            'status' => false,
            'message' => $massage,
            'data' => [],
        ], 401);
    }

    public function Log($userId, $description, $type)
    {
        $Log = new Log();
        $Log->user_id = $userId;
        $Log->description = $description;
        $Log->type = $type;
        $Log->save();
    }

    public function sendNotifyAll($title, $body, $target_id, $type)
    {

        $device =  Device::with('user')
            ->with('frammer')
            ->get();

        $notiToken = [];
        $notifyUser = [];

        for ($i = 0; $i < count($device); $i++) {

            $notiToken[] = $device[$i]->notify_token;
            // $notifyUser[] = $device[$i]->user_id;
            $notifyUser[] = $device[$i]->qouta_id;
        }

        $FcmToken = array_values(array_unique($notiToken));


        for ($i = 0; $i < count($FcmToken); $i++) {

            try {

                $message = CloudMessage::fromArray([
                    'token' => $FcmToken[$i],
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                ]);

                $this->notification->send($message);
            } catch (\Throwable $e) {
                //
            }
        }



        //add log
        $this->addNotifyLog($title, $body, $target_id, $type, $notifyUser);
    }

    public function sendNotify($title, $body, $target_id, $type, $qouta_id)
    {

        $device =  Device::with('user')
            ->with('frammer')
            ->where('qouta_id', $qouta_id)
            ->get();

        $notiToken = [];
        $notifyUser = [];

        for ($i = 0; $i < count($device); $i++) {

            $notiToken[] = $device[$i]->notify_token;
            // $notifyUser[] = $device[$i]->user_id;
            $notifyUser[] = $device[$i]->qouta_id;
        }

        $FcmToken = array_values(array_unique($notiToken));
        $NotifyUser = array_values(array_unique($notifyUser));

        for ($i = 0; $i < count($FcmToken); $i++) {
            try {
                $message = CloudMessage::fromArray([
                    'token' => $FcmToken[$i],
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                ]);

                $this->notification->send($message);
            } catch (\Throwable $e) {
                //
            }
        }




        //add log
        $this->addNotifyLog($title, $body, $target_id, $type, $NotifyUser);
    }

    public function sendNotifyMultiUser($title, $body, $target_id, $type, $qoutaId)
    {
        $notiToken = [];
        $notifyUser = [];

        for ($j = 0; $j < count($qoutaId); $j++) {

            $device =  Device::with('user')
                ->with('frammer')
                ->where('qouta_id', $qoutaId[$j])
                ->get();

            for ($i = 0; $i < count($device); $i++) {

                $notiToken[] = $device[$i]->notify_token;
                $notifyUser[] = $device[$i]->qouta_id;
            }
        }

        $FcmToken = array_values(array_unique($notiToken));
        $NotifyUser = array_values(array_unique($notifyUser));

        for ($i = 0; $i < count($FcmToken); $i++) {
            try {

                $message = CloudMessage::fromArray([
                    'token' => $FcmToken[$i],
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                ]);

                $this->notification->send($message);
            } catch (\Throwable $e) {
                //
            }
        }

        //add log
        $this->addNotifyLog($title, $body, $target_id, $type, $NotifyUser);
    }

    public function testNoti()
    {
        $message = CloudMessage::fromArray([
            'token' => 'cxb4Kp81TFqlZ7svhu0k4X:APA91bHl_HzAZmhH5TBOm9y2GybdtScb3Dsb704jiTZf1juenRytc0XVRDj5eat-WWW2vBMZU-5iTb-jNVRR2Djkf-3eHYUXzx6ImDAlpLWqllOr_j6bw2kn8D3d-vJzVYa5ijWt358B',
            'notification' => [
                'title' => 'ใช้ได้ยังพี่ค้อ',
                'body' => 'ใช้ได้ยังพี่ค้อ'
            ],
        ]);

        $this->notification->send($message);
    }

    public function addNotifyLog($title, $body, $target_id, $type, $NotifyUser)
    {

        $Notify_log = new  Notify_log();
        $Notify_log->title = $title;
        $Notify_log->detail = $body;
        $Notify_log->target_id = $target_id;
        $Notify_log->type = $type;
        $Notify_log->save();

        $result = array_unique($NotifyUser);
        sort($result); // เรียงลำดับ index ตามค่า

        //add notify user
        for ($i = 0; $i < count($result); $i++) {
            $Notify_log_user = new  Notify_log_user();
            $Notify_log_user->notify_log_id =  $Notify_log->id;
            // $Notify_log_user->user_id = $result[$i];
            $Notify_log_user->qouta_id = $result[$i];
            $Notify_log_user->read = false;

            $Notify_log_user->save();
        }

        return $Notify_log;
    }

    public function sendMail($email, $data, $title, $type)
    {

        $mail = new SendMail($email, $data, $title, $type);
        Mail::to($email)->send($mail);
    }

    public function sendLine($line_token, $text)
    {

        $sToken = $line_token;
        $sMessage = $text;

        $chOne = curl_init();
        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($chOne, CURLOPT_POST, 1);
        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($chOne);

        curl_close($chOne);
    }

    public function uploadImages(Request $request)
    {

        $image = $request->image;
        $path = $request->path;

        $input['imagename'] = md5(rand(0, 999999) . $image->getClientOriginalName()) . '.' . $image->extension();
        $destinationPath = public_path('/thumbnail');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $img = Image::make($image->path());
        $img->save($destinationPath . '/' . $input['imagename']);
        $destinationPath = public_path($path);
        $image->move($destinationPath, $input['imagename']);

        return $this->returnSuccess('ดำเนินการสำเร็จ', $path . $input['imagename']);
    }

    public function uploadImage($image, $path)
    {
        $input['imagename'] = md5(rand(0, 999999) . $image->getClientOriginalName()) . '.' . $image->extension();
        $destinationPath = public_path('/thumbnail');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $img = Image::make($image->path());
        $img->save($destinationPath . '/' . $input['imagename']);
        $destinationPath = public_path($path);
        $image->move($destinationPath, $input['imagename']);

        return $path . $input['imagename'];
    }

    public function uploadFiles(Request $request)
    {

        $file = $request->file;
        $path = $request->path;

        $input['filename'] = time() . '.' . $file->extension();

        $destinationPath = public_path('/file_thumbnail');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $destinationPath = public_path($path);
        $file->move($destinationPath, $input['filename']);

        return $this->returnSuccess('ดำเนินการสำเร็จ', $path . $input['filename']);
    }

    public function uploadFile($file, $path)
    {
        $input['filename'] = time() . '.' . $file->extension();
        $destinationPath = public_path('/file_thumbnail');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $destinationPath = public_path($path);
        $file->move($destinationPath, $input['filename']);

        return $path . $input['filename'];
    }

    public function getDropDownYear()
    {
        $Year = intval(((date('Y')) + 1) + 543);

        $data = [];

        for ($i = 0; $i < 10; $i++) {

            $Year = $Year - 1;
            $data[$i]['year'] = $Year;
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function getDropDownProvince()
    {

        $province = array("กระบี่", "กรุงเทพมหานคร", "กาญจนบุรี", "กาฬสินธุ์", "กำแพงเพชร", "ขอนแก่น", "จันทบุรี", "ฉะเชิงเทรา", "ชลบุรี", "ชัยนาท", "ชัยภูมิ", "ชุมพร", "เชียงราย", "เชียงใหม่", "ตรัง", "ตราด", "ตาก", "นครนายก", "นครปฐม", "นครพนม", "นครราชสีมา", "นครศรีธรรมราช", "นครสวรรค์", "นนทบุรี", "นราธิวาส", "น่าน", "บุรีรัมย์", "บึงกาฬ", "ปทุมธานี", "ประจวบคีรีขันธ์", "ปราจีนบุรี", "ปัตตานี", "พะเยา", "พังงา", "พัทลุง", "พิจิตร", "พิษณุโลก", "เพชรบุรี", "เพชรบูรณ์", "แพร่", "ภูเก็ต", "มหาสารคาม", "มุกดาหาร", "แม่ฮ่องสอน", "ยโสธร", "ยะลา", "ร้อยเอ็ด", "ระนอง", "ระยอง", "ราชบุรี", "ลพบุรี", "ลำปาง", "ลำพูน", "เลย", "ศรีสะเกษ", "สกลนคร", "สงขลา", "สตูล", "สมุทรปราการ", "สมุทรสงคราม", "สมุทรสาคร", "สระแก้ว", "สระบุรี", "สิงห์บุรี", "สุโขทัย", "สุพรรณบุรี", "สุราษฎร์ธานี", "สุรินทร์", "หนองคาย", "หนองบัวลำภู", "อยุธยา", "อ่างทอง", "อำนาจเจริญ", "อุดรธานี", "อุตรดิตถ์", "อุทัยธานี", "อุบลราชธานี");

        $data = [];

        for ($i = 0; $i < count($province); $i++) {

            $data[$i]['province'] = $province[$i];
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function getDownloadFomatImport($params)
    {

        $file = $params;
        $destinationPath = public_path() . "/fomat_import/";

        return response()->download($destinationPath . $file);
    }

    public function checkDigitMemberId($memberId)
    {

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {

            $sum += (int) ($memberId[$i]) * (13 - $i);
        }

        if ((11 - ($sum % 11)) % 10 == (int) ($memberId[12])) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function genCode(Model $model, $prefix, $number)
    {

        $countPrefix = strlen($prefix);
        $countRunNumber = strlen($number);

        //get last code
        $Property_type = $model::orderby('code', 'desc')->first();
        if ($Property_type) {
            $lastCode = $Property_type->code;
        } else {
            $lastCode = $prefix . $number;
        }

        $codelast = substr($lastCode, $countPrefix, $countRunNumber);

        $newNumber = intval($codelast) + 1;
        $Number = sprintf('%0' . strval($countRunNumber) . 'd', $newNumber);

        $runNumber = $prefix . $Number;

        return $runNumber;
    }

    public function isURL($url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            return false;
        }
    }


    // public function dateBetween($dateStart, $dateStop)
    // {
    //     $datediff = strtotime($dateStop) - strtotime($this->dateform($dateStart));
    //     return abs($datediff / (60 * 60 * 24));
    // }

    // public function log_noti($Title, $Description, $Url, $Pic, $Type)
    // {
    //     $log_noti = new Log_noti();
    //     $log_noti->title = $Title;
    //     $log_noti->description = $Description;
    //     $log_noti->url = $Url;
    //     $log_noti->pic = $Pic;
    //     $log_noti->log_noti_type = $Type;

    //     $log_noti->save();
    // }

    /////////////////////////////////////////// seach datatable  ///////////////////////////////////////////

    public function withPermission($query, $search)
    {

        $col = array('id', 'name', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('permission', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withMember($query, $search)
    {

        // $col = array('id', 'member_group_id','code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        // $query->orWhereHas('member', function ($query) use ($search, $col) {

        //     $query->Where(function ($query) use ($search, $col) {

        //         //search datatable
        //         $query->orwhere(function ($query) use ($search, $col) {
        //             foreach ($col as &$c) {
        //                 $query->orWhere($c, 'like', '%' . $search['value'] . '%');
        //             }
        //         });
        //     });

        // });

        // return $query;
    }


    public function withInquiryType($query, $search)
    {

        $col = array('id', 'code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('inquiry_type', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyType($query, $search)
    {

        $col = array('id', 'code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_type', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertySubType($query, $search)
    {

        $col = array('id', 'property_type_id', 'code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_sub_type', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyAnnouncer($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_announcer', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyColorLand($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_color_land', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyOwnership($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_ownership', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyFacility($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_facility', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertySubFacility($query, $search)
    {

        $col = array('id', 'property_facility_id', 'name', 'icon', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_sub_facility', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                $query = $this->withPropertyFacility($query, $search);
            });
        });

        return $query;
    }

    public function withPropertySubFacilityExplend($query, $search)
    {

        $col = array('id', 'property_sub_facility_id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_sub_facility_explend', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                $query = $this->withPropertySubFacility($query, $search);
            });
        });

        return $query;
    }

    /////////////////////////////////////////// seach datatable  ///////////////////////////////////////////


    public function sendOTP($tel, $open)
    {
        try {
            // เชค open otp
            if ($open == true) {

                // $body = [
                //     'key' => "1774008287493544",
                //     'secret' => "6b17ac71d2dbdadef3845da4cc83f035",
                //     'msisdn' => $tel
                // ];
                $body = [
                    'key' => "1786953766334606",
                    'secret' => "f474490133f124bbcd230f16d90b679d",
                    'msisdn' => $tel
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                ])->post('https://otp.thaibulksms.com/v2/otp/request', $body);

                if ($response->status() === 200) {
                    $data = $response->json();

                    return $data;
                } elseif ($response->status() === 400) {
                    $data['status'] = 'failed';
                    $data['token'] = null;
                    $data['refno'] =  null;

                    return $data;
                } else {
                    $data['status'] = 'failed';
                    $data['token'] = null;
                    $data['refno'] =  null;

                    return $data;
                }
            } else {

                // random otp
                $otpKey = $this->randomOtp();

                $data['status'] = 'success';
                $data['token'] = $otpKey['otp_ref'];
                $data['refno'] = $otpKey['otp_ref'];

                return $data;
            }
        } catch (\Throwable $e) {

            $data['status'] = 'failed';
            $data['token'] = null;
            $data['refno'] =  null;

            return $data;
        }
    }

    public function verifyOTP($otpCode, $tokenOTP, $open)
    {
        try {
            // เชค open verifyOTP
            if ($open == true) {

                $body = [
                    'key' => "1786953766334606",
                    'secret' => "f474490133f124bbcd230f16d90b679d",
                    'token' => $tokenOTP,
                    'pin' => $otpCode,
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                ])->post('https://otp.thaibulksms.com/v2/otp/verify', $body);

                $status = $response->status();
                $data = false;
                if ($status == 200) {
                    $data = true;
                }

                return $data;
            } else {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }
}

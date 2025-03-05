<?php

namespace App\Http\Controllers;

use App\Models\NotiSetting;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NotiSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!isset($request->frammer_id)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else if (!isset($request->position)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else if (!isset($request->status)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {


            $Item = NotiSetting::where('frammer_id', $request->frammer_id)
                ->where('position', $request->position)
                ->first();

            if ($Item) {
                if ($request->status == 'Yes') {
                    $Item->status = 'Yes';
                    $Item->save();
                } else {
                    $Item->delete();
                }
            } else {
                $Item = new NotiSetting();

                $Item->frammer_id = $request->frammer_id;
                $Item->status = $request->status;
                $Item->position = $request->position;
                $Item->save();
            }

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NotiSetting  $notiSetting
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = NotiSetting::where('qouta_id',$id)->first();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NotiSetting  $notiSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(NotiSetting $notiSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NotiSetting  $notiSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();


        try {
            $Item = NotiSetting::where('qouta_id',$id)->first();
            if(!$Item){
                $ItemNoti = new NotiSetting();
                $ItemNoti->qouta_id = $id;
                $ItemNoti->noti_1 = $request->noti_1;
                $ItemNoti->noti_2 = $request->noti_2;
                $ItemNoti->noti_3 = $request->noti_3;
                $ItemNoti->noti_4 = $request->noti_4;
                $ItemNoti->noti_5 = $request->noti_5;
                $ItemNoti->noti_6 = $request->noti_6;
                $ItemNoti->noti_7 = $request->noti_7;
                $ItemNoti->noti_8 = $request->noti_8;
                $ItemNoti->save();
            }else{
                if($request->noti_1)
                $Item->noti_1 = $request->noti_1;
                if($request->noti_2)
                $Item->noti_2 = $request->noti_2;
                if($request->noti_3)
                $Item->noti_3 = $request->noti_3;
                if($request->noti_4)
                $Item->noti_4 = $request->noti_4;
                if($request->noti_5)
                $Item->noti_5 = $request->noti_5;
                if($request->noti_6)
                $Item->noti_6 = $request->noti_6;
                if($request->noti_7)
                $Item->noti_7 = $request->noti_7;
                if($request->noti_8)
                $Item->noti_8 = $request->noti_8;
                $Item->save();
            }
        
            //

            //log
            $userId = "admin";
            $type = 'แก้ไขรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->user_id;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NotiSetting  $notiSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(NotiSetting $notiSetting)
    {
        //
    }
}

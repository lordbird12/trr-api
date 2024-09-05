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
    public function show(NotiSetting $notiSetting)
    {
        //
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
    public function update(Request $request, NotiSetting $notiSetting)
    {
        //
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\CompanyDetail;

class CompanyDetailController extends Controller
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
        // dd($request->all());
        DB::beginTransaction();

        try {
            $Item = new CompanyDetail();
            $Item->factory_id = $request->factory_id;
            $Item->factory_affiliation = $request->factory_affiliation;
            $Item->head_office = $request->head_office;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->time_start = $request->time_start;
            $Item->date_start = $request->date_start;
            $Item->time_end = $request->time_end;
            $Item->date_end = $request->date_end;
            $Item->youtube = $request->youtube;
            $Item->facebook = $request->facebook;
            $Item->tiktok = $request->tiktok;
            $Item->website = $request->website;

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
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
     * Display the specified resource.
     *
     * @param  \App\Models\CompanyDetail  $CompanyDetail
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = CompanyDetail::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompanyDetail  $CompanyDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyDetail $CompanyDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyDetail  $CompanyDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $Item = CompanyDetail::find($id);
            $Item->factory_id = $request->factory_id;
            $Item->factory_affiliation = $request->factory_affiliation;
            $Item->head_office = $request->head_office;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->time_start = $request->time_start;
            $Item->date_start = $request->date_start;
            $Item->time_end = $request->time_end;
            $Item->date_end = $request->date_end;
            $Item->youtube = $request->youtube;
            $Item->facebook = $request->facebook;
            $Item->tiktok = $request->tiktok;
            $Item->website = $request->website;

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
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
     * @param  \App\Models\CompanyDetail  $CompanyDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = CompanyDetail::find($id);
            $Item->delete();

            //log
            $userId = "admin";
            $type = 'ลบผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\CompanyDetail;
use App\Models\Factory;

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

            $Item->head_office = $request->head_office;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->time_start = $request->time_start;
            $Item->date_start = $request->date_start;
            $Item->time_end = $request->time_end;
            $Item->date_end = $request->date_end;
            $Item->link1 = $request->link1;
            $Item->link2 = $request->link2;
            $Item->link3 = $request->link3;
            $Item->link4 = $request->link4;
            $Item->link5 = $request->link5;
            $Item->image1 = $request->image1;
            $Item->image2 = $request->image2;
            $Item->image3 = $request->image3;
            $Item->image4 = $request->image4;
            $Item->image5 = $request->image5;

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


    public function getbyfacID(Request $request)
    {
        // dd($request->all());
        $id = $request->factory_id;
        $data = CompanyDetail::first();
        $data->factory = Factory::where('factory_id', $id)->first();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
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

            $Item->head_office = $request->head_office;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->time_start = $request->time_start;
            $Item->date_start = $request->date_start;
            $Item->time_end = $request->time_end;
            $Item->date_end = $request->date_end;
            $Item->link1 = $request->link1;
            $Item->link2 = $request->link2;
            $Item->link3 = $request->link3;
            $Item->link4 = $request->link4;
            $Item->link5 = $request->link5;
            $Item->image1 = $request->image1;
            $Item->image2 = $request->image2;
            $Item->image3 = $request->image3;
            $Item->image4 = $request->image4;
            $Item->image5 = $request->image5;

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

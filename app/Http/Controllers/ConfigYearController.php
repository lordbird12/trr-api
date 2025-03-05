<?php

namespace App\Http\Controllers;

use App\Models\ConfigYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ConfigYearController extends Controller
{

    public function getPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $col = array('id', 'year', 'date', 'time', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'year', 'date', 'time', 'create_by');

        $D = ConfigYear::select($col);

        if ($orderby[$order[0]['column']]) {
            $D->orderby($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if ($search['value'] != '' && $search['value'] != null) {

            $D->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orWhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                //search with
                // $query = $this->withPermission($query, $search);
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {

            //run no
            $No = (($page - 1) * $length);

            for ($i = 0; $i < count($d); $i++) {

                $No = $No + 1;
                $d[$i]->No = $No;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }
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
        $loginBy = $request->login_by;

        if (!isset($request->year)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {

            $Item = new ConfigYear();
           
            $Item->year = $request->year;
            $Item->date = $request->date;
            $Item->time = $request->time;

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ ConfigYear';
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
     * @param  \App\Models\ConfigYear  $configYear
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = ConfigYear::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }
     

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ConfigYear  $configYear
     * @return \Illuminate\Http\Response
     */
    public function edit(ConfigYear $configYear)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConfigYear  $configYear
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!isset($request->year)) {
            return $this->returnError('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else if (!isset($request->date)) {
            return $this->returnError('กรุณาระบุรายละเอียดให้เรียบร้อย', 404);
        }

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {

            $Item = ConfigYear::find($id);
            $Item->year = $request->year;
            $Item->date = $request->date;
            $Item->time = $request->time;
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'แก้ไขรายการ ConfigYear';
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
     * @param  \App\Models\ConfigYear  $configYear
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = ConfigYear::find($id);
            $Item->delete();

            //log
            $userId = "admin";
            $type = 'ลบ ConfigYear';
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

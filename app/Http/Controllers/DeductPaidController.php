<?php

namespace App\Http\Controllers;

use App\Models\DeductPaid;
use App\Models\DeductType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DeductPaidController extends Controller
{
    public function getList($frammer_id, $month, $year)
    {
        $Item = DeductPaid::where('frammer_id', $frammer_id)->where('month', $month)->where('year', $year)->get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['deduct'] = DeductType::where('code', $Item[$i]['code'])->first();
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $month = $request->month;
        $year = $request->year;

        $col = array('id', 'frammer_id', 'code', 'paid', 'month', 'year', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'frammer_id', 'code', 'paid', 'month', 'year', 'create_by', 'update_by', 'created_at', 'updated_at');


        $D = DeductPaid::select($col);

        if ($month) {
            $D->where('month', $month);
        }

        if ($year) {
            $D->where('year', $year);
        }


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
                $d[$i]->deduct = DeductType::where('code', $d[$i]->code)->first();
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
        if (!isset($request->frammer_id)) {
            return $this->returnError('กรุณาระบุชื่อประเภทงานให้เรียบร้อย', 404);
        } else if (!isset($request->code)) {
            return $this->returnError('กรุณาระบุรหัสประเภทงานให้เรียบร้อย', 404);
        }

        DB::beginTransaction();

        try {

            $Item = new DeductPaid();
            $Item->frammer_id = $request->frammer_id;
            $Item->code = $request->code;
            $Item->paid = $request->paid;
            $Item->month = $request->month;
            $Item->year = $request->year;
            $Item->create_by = "Admin";
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();

            //log
            $userId = "Admin";
            $type = 'เพิ่มประเภทงาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->user_id;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnError('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeductPaid  $deductPaid
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = DeductPaid::where('id', $id)
            ->first();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeductPaid  $deductPaid
     * @return \Illuminate\Http\Response
     */
    public function edit(DeductPaid $deductPaid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeductPaid  $deductPaid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();


        try {
            $Item = DeductPaid::find($id);
            $Item->frammer_id = $request->frammer_id;
            $Item->code = $request->code;
            $Item->paid = $request->paid;
            $Item->month = $request->month;
            $Item->year = $request->year;
            $Item->create_by = "Admin";
            $Item->updated_at = Carbon::now()->toDateTimeString();


            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
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
     * @param  \App\Models\DeductPaid  $deductPaid
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = DeductPaid::find($id);
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

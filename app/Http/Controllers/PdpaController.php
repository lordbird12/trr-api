<?php

namespace App\Http\Controllers;

use App\Models\Pdpa;
use App\Models\PdpaRegister;
use App\Models\AutoNotify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Models\Frammers;

class PdpaController extends Controller
{
    public function getList($id)
    {
        $Item = Pdpa::where('status','Y')->first();

        if($Item){
            $check = PdpaRegister::where('pdpa_id',$Item->id)
            ->where('quota_id',$id)
            ->first();
        }
     
        if($check){
            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', null);
        }else{
            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
        }
    }

    public function getPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $Status = $request->status;

        $col = array('id', 'title', 'detail', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'title', 'detail', 'status', 'expire', 'create_by');

        $D = Pdpa::select($col);

        if (isset($Status)) {
            $D->where('status', $Status);
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
                $d[$i]->views = PdpaRegister::where('pdpa_id',$d[$i]->id)->count();
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function getPageRegister(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $pdpa_id = $request->pdpa_id;

        $col = array('id', 'pdpa_id', 'quota_id', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'pdpa_id', 'quota_id', 'create_by', 'update_by');

        $D = PdpaRegister::select($col);

        if (isset($pdpa_id)) {
            $D->where('pdpa_id', $pdpa_id);
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
                $d[$i]->frmamer = Frammers::where('qouta_id',$d[$i]->quota_id)->first();
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

        if (!isset($request->title)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new Pdpa();
            $prefix = "#P-";
            $id = IdGenerator::generate(['table' => 'pdpas', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);
            $Item->code = $id;
            $Item->title = $request->title;
            $Item->detail = $request->detail;
            $Item->status = $request->status;

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ PDPA';
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
     * @param  \App\Models\Pdpa  $pdpa
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Pdpa::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pdpa  $pdpa
     * @return \Illuminate\Http\Response
     */
    public function edit(Pdpa $pdpa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pdpa  $pdpa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!isset($request->title)) {
            return $this->returnError('กรุณาระบุชื่อประเภทงานให้เรียบร้อย', 404);
        } else if (!isset($request->detail)) {
            return $this->returnError('กรุณาระบุรหัสประเภทงานให้เรียบร้อย', 404);
        }

        $title = $request->title;
        $detail = $request->detail;
        $status = $request->status;


        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            Pdpa::query()->update(['status' => 'N']);

            $Item = Pdpa::find($id);
            $Item->title = $title;
            $Item->detail = $detail;
            $Item->status = $status;
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'แก้ไขรายการ PDPA';
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
     * @param  \App\Models\Pdpa  $pdpa
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Pdpa::find($id);
            $Item->delete();

            //log
            $userId = "admin";
            $type = 'ลบ PDPA';
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


    public function registerPDPA(Request $request)
    {
        if (!isset($request->pdpa_id)) {
            return $this->returnErrorData('[pdpa_id] Data Not Found', 404);
        } else  if (!isset($request->quota_id)) {
            return $this->returnErrorData('[quota_id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {

            $Item = new PdpaRegister();
            $Item->pdpa_id = $request->pdpa_id;
            $Item->quota_id = $request->quota_id;

            $Item->save();

            //log
            $userId = "admin";
            $type = 'บันทึกสถานะ PDPA';
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

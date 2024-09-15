<?php

namespace App\Http\Controllers;

use App\Models\Frammers;
use App\Models\Country;
use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FrammersController extends Controller
{
    public function getList()
    {
        $Item = Frammers::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
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

        $Status = $request->status;

        $col = array('id', 'qouta', 'idcard', 'name', 'phone', 'email', 'country_code', 'province_code', 'area', 'image', 'count_area', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'qouta', 'idcard', 'name', 'phone', 'email', 'country_code', 'province_code', 'area', 'image', 'count_area', 'status', 'create_by');

        $D = Frammers::select($col);

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
                $d[$i]->image = url($d[$i]->image);
                $d[$i]->country_name = $d[$i]->country_code ? Country::where('code', $d[$i]->country_code)->first()->name ?? null : null;
                $d[$i]->province_name = $d[$i]->province_code ? Province::where('code', $d[$i]->province_code)->first()->name ?? null : null;
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

        if (!isset($request->idcard)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new Frammers();
            $Item->qouta = $request->qouta;
            $Item->idcard = $request->idcard;
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->country_code = $request->country_code;
            $Item->province_code = $request->province_code;
            $Item->area = $request->area;
            $Item->count_area = $request->count_area;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/frammers/');
            }

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
     * @param  \App\Models\Frammers  $frammers
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Frammers::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Frammers  $frammers
     * @return \Illuminate\Http\Response
     */
    public function edit(Frammers $frammers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Frammers  $frammers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Frammers $frammers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Frammers  $frammers
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Frammers::find($id);
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

    public function imageProfile(Request $request)
    {
        $loginBy = $request->login_by;

        if (!isset($request->qouta_id)) {
            return $this->returnErrorData('ไม่พบข้อมูล qouta_id', 404);
        }

        DB::beginTransaction();

        try {

            $qouta_id = $request->qouta_id;
            $Item = Frammers::where('qouta_id', $qouta_id)->first();
            if (!$Item) {
                $Item = new Frammers();
            }
            $Item->qouta_id = $request->qouta_id;
            $Item->image = $request->image;
            // if ($request->image && $request->image != null && $request->image != 'null') {
            //     $Item->image = $this->uploadImage($request->image, '/images/frammers/');
            // }

            $Item->save();

            if($Item){
                $Item->image = url($Item->image);
            }

            //log
            // $userId = $loginBy->username;
            // $type = 'แก้ไขผู้ใช้งาน';
            // $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $Item->username;
            // $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง '.$e, 404);
        }
    }

    public function GetProfile($id)
    {
        $Item = Frammers::where('qouta_id',$id)->first();

        if($Item){
            $Item->image = url($Item->image);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getEventYear(Request $request)
    {
        $qoutas = $request->qoutas;
        $year = $request->year;
        dd($qoutas);

        $Item = Frammers::where('year',$year)->get();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

}

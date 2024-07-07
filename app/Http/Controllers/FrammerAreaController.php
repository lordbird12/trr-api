<?php

namespace App\Http\Controllers;

use App\Models\FrammerArea;
use App\Models\Frammers;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FrammerAreaController extends Controller
{
    public function getList()
    {
        $Item = FrammerArea::get()->toarray();

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

        $col = array('id', 'frammer_id', 'year', 'contact_no', 'test_no', 'area', 'all_area', 'bonsucro', 'finish_good', 'country_code', 'province_code', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'frammer_id', 'year', 'contact_no', 'test_no', 'area', 'all_area', 'bonsucro', 'finish_good', 'country_code', 'province_code', 'status', 'create_by');

        $D = FrammerArea::select($col);

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
                $d[$i]->frammer = Frammers::where('id', intval($d[$i]->frammer_id))->first();
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

        if (!isset($request->point)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new FrammerArea();
            $prefix = "#FRMA-";
            $id = IdGenerator::generate(['table' => 'frammer_areas', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);
            $Item->code = $id;
            $Item->frammer_id = $request->frammer_id;
            $Item->year = $request->year;
            $Item->contact_no = $request->contact_no;
            $Item->test_no = $request->test_no;
            $Item->area = $request->area;
            $Item->all_area = $request->all_area;
            $Item->bonsucro = $request->bonsucro;
            $Item->gets_framing = $request->gets_framing;
            $Item->finish_good = $request->finish_good;
            $Item->country_code = $request->country_code;
            $Item->province_code = $request->province_code;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/frammer_areas/');
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
     * @param  \App\Models\FrammerArea  $frammerArea
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = FrammerArea::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FrammerArea  $frammerArea
     * @return \Illuminate\Http\Response
     */
    public function edit(FrammerArea $frammerArea)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FrammerArea  $frammerArea
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FrammerArea $frammerArea)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FrammerArea  $frammerArea
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = FrammerArea::find($id);
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

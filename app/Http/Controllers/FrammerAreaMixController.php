<?php

namespace App\Http\Controllers;

use App\Models\FrammerAreaMix;
use App\Models\FrammerAreaMixEventType;
use App\Models\FrammerAreaMixLocation;
use App\Models\Frammers;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FrammerAreaMixController extends Controller
{
    public function getList()
    {
        $Item = FrammerAreaMix::get()->toarray();

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

        $col = array('id', 'code', 'year', 'frammer_id', 'frammer_area_mix_event_type_id', 'frammer_area_mix_location_id', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'year', 'frammer_id', 'frammer_area_mix_event_type_id', 'frammer_area_mix_location_id', 'statys', 'create_by');

        $D = FrammerAreaMix::select($col);

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
                $d[$i]->frammer_area_mix_event_type = FrammerAreaMixEventType::where('id', intval($d[$i]->frammer_area_mix_event_type_id))->first();
                $d[$i]->frammer_ara_mix_location = FrammerAreaMixLocation::where('id', intval($d[$i]->frammer_area_mix_location_id))->first();

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
            $Item = new FrammerAreaMix();
            $prefix = "#FRMA-";
            $id = IdGenerator::generate(['table' => 'frammer_area_mixes', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);
            $Item->code = $id;
            $Item->year = $request->year;
            $Item->frammer_id = $request->frammer_id;
            $Item->frammer_area_mix_event_type_id = $request->frammer_area_mix_event_type_id;
            $Item->frammer_area_mix_location_id = $request->frammer_area_mix_location_id;

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
     * @param  \App\Models\FrammerAreaMix  $frammerAreaMix
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = FrammerAreaMix::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FrammerAreaMix  $frammerAreaMix
     * @return \Illuminate\Http\Response
     */
    public function edit(FrammerAreaMix $frammerAreaMix)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FrammerAreaMix  $frammerAreaMix
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FrammerAreaMix $frammerAreaMix)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FrammerAreaMix  $frammerAreaMix
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = FrammerAreaMix::find($id);
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

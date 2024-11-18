<?php

namespace App\Http\Controllers;

use App\Models\FrammerArea;
use App\Models\Frammers;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FrammerAreaController extends Controller
{
    public function getList(Request $request)
    {
        $data = $request->input();
        $query = FrammerArea::where("frammer_id", $data['frammer_id'])
            ->where("year", $data['year']);

        if (isset($data['sugartype'])) {
            if ($data['sugartype'] == 'อ้อยปลูกใหม่') {
                $query->where("sugarcane_age", "<=", 1);
            } else {
                $query->where("sugarcane_age", ">", 1);
            }
        }

        $items = $query->get()->toArray();

        if (!empty($items)) {
            foreach ($items as $index => &$item) {
                $item['No'] = $index + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
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

        DB::beginTransaction();

        try {
            $Item = new FrammerArea();
            $Item->frammer_id = $request->frammer_id;
            $Item->year = $request->year;
            $Item->area = $request->area;
            $Item->area_size = $request->area_size;
            $Item->sugarcane_age = $request->sugarcane_age;
            $Item->sugarcane_type = $request->sugarcane_type;
            $Item->product_per_rai = $request->product_per_rai;
            $Item->measuring_point = $request->measuring_point;
            $Item->distance = $request->distance;
            $Item->last_year_cumulative_rain = $request->last_year_cumulative_rain;
            $Item->curr_year_cumulative_rain = $request->curr_year_cumulative_rain;
            $Item->co_or_points = $request->co_or_points;
            $Item->center = $request->center;

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
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

    public function getPlotList(Request $request)
    {

        $FacID = $request->fac_i_d;
        $QuotaID = $request->quota_i_d;
        $Current_year = $request->current_year;

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post('https://canegrow.com:28099/api/cumulative_rain', [
                'FacID' => strval($FacID),
                'QuotaID' =>  strval($QuotaID),
                'Current_year' => strval($Current_year),
                // 'FacID' => "1",
                // 'QuotaID' => "327",
                // 'Current_year' => "2024",
            ]);


            if ($response->successful()) {
                $res = $response->body();
                $data = json_decode($res); //
                // return $data->result;

                if ($data->result == 1 || $data->result == '1') {

                    for ($i = 0; $i < count($data->data); $i++) {

                        $FrammerArea = FrammerArea::where('area', $data->data[$i]->plot_no)->first();
                        $data->data[$i]->frammer_area = $FrammerArea;
                    }
                }

                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
            } else {
                return $this->returnErrorData('ไม่พบข้อมูล api ', 404);
            }
        } catch (\Throwable $e) {


            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function updateImageArea(Request $request)
    {

        DB::beginTransaction();

        try {

            $frammer_id =  $request->frammer_id;
            $area =  $request->area;

            $Item = FrammerArea::where('area', $area)->first();
            if ($Item) {
                $Item->image = $request->image;
                $Item->save();
            } else {
                $addItem = new FrammerArea();
                $addItem->frammer_id = $frammer_id;
                $addItem->area = $area;
                $addItem->image = $request->image;
                $addItem->save();
            }

            //

            DB::commit();
            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}

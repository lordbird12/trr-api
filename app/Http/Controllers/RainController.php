<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rain;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RainController extends Controller
{
    public function getList(Request $request)
    {
        $Item = Rain::where('frammer_id', $request->frammer_id)
            ->where('year', $request->year)
            ->get();

            $Item = $Item->map(function ($item) {
                $item->image = url($item->image);
                return $item;
            });

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
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
            $Item = new Rain();
            $Item->frammer_id = $request->frammer_id;
            $Item->year = $request->year;
            $Item->plotsugar_id = $request->plotsugar_id;
            $Item->last_year_cumulative_rain = $request->last_year_cumulative_rain;
            $Item->curr_year_cumulative_rain = $request->curr_year_cumulative_rain;
            $Item->image = $request->image;
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
     * @param  \App\Models\Rain  $Rain
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Rain::find($id);
    
        if (!$item) {
            return $this->returnError('Item not found', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rain  $Rain
     * @return \Illuminate\Http\Response
     */
    public function edit(Rain $Rain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rain  $Rain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // dd($request->all());
        DB::beginTransaction();

        try {
            $Item = Rain::find($id);
            $Item->frammer_id = $request->frammer_id;
            $Item->year = $Item->year;
            $Item->plotsugar_id = $Item->plotsugar_id;
            $Item->last_year_cumulative_rain = $Item->last_year_cumulative_rain;
            $Item->curr_year_cumulative_rain = $Item->curr_year_cumulative_rain;
            $Item->image = $request->image;

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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rain  $Rain
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rain $Rain)
    {
        //
    }
}

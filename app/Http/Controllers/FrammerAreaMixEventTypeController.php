<?php

namespace App\Http\Controllers;

use App\Models\FrammerAreaMixEventType;
use App\Models\Feature;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Log;

class FrammerAreaMixEventTypeController extends Controller
{
    public function getList()
    {
        $Item = FrammerAreaMixEventType::get()->toarray();

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

        $col = array('id', 'code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'name', 'status', 'expire', 'create_by');

        $D = FrammerAreaMixEventType::select($col);

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
                $d[$i]->FrammerAreaMixEventType = FrammerAreaMixEventType::where('id', intval($d[$i]->FrammerAreaMixEventType_id))->first();
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

        if (!isset($request->name)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new FrammerAreaMixEventType();
            $prefix = "#FAME-";
            $id = IdGenerator::generate(['table' => 'frammer_area_mix_event_types', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);
            $Item->code = $id;
            $Item->name = $request->name;

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
     * @param  \App\Models\FrammerAreaMixEventType  $frammerAreaMixEventType
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = FrammerAreaMixEventType::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FrammerAreaMixEventType  $frammerAreaMixEventType
     * @return \Illuminate\Http\Response
     */
    public function edit(FrammerAreaMixEventType $frammerAreaMixEventType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FrammerAreaMixEventType  $frammerAreaMixEventType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FrammerAreaMixEventType $frammerAreaMixEventType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FrammerAreaMixEventType  $frammerAreaMixEventType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = FrammerAreaMixEventType::find($id);
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

    public function graphCircle()
    {
        setlocale(LC_TIME, 'th_TH.UTF-8');

        // Get the current month in English
        $currentMonth = strftime('%B');

        $months = [
            'January' => 'มกราคม',
            'February' => 'กุมภาพันธ์',
            'March' => 'มีนาคม',
            'April' => 'เมษายน',
            'May' => 'พฤษภาคม',
            'June' => 'มิถุนายน',
            'July' => 'กรกฎาคม',
            'August' => 'สิงหาคม',
            'September' => 'กันยายน',
            'October' => 'ตุลาคม',
            'November' => 'พฤศจิกายน',
            'December' => 'ธันวาคม',
        ];

        $Item = Feature::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['percent'] = 0;
            }
        }

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $count = Feature::sum('views');
        $currentMonth = date('F');
        $thaiMonth = $months[$currentMonth];

        if($count){
            $data = [
                "views" => $count,
                "month" => $thaiMonth,
                "graph" => $Item
            ];
        }else{
            $data = [
                "views" => 0,
                "graph" => $Item
            ];
        }
       

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function graphRegister()
    {
        setlocale(LC_TIME, 'th_TH.UTF-8');
    
        // Mapping เดือนภาษาอังกฤษเป็นเดือนภาษาไทยแบบย่อ
        $months = [
            'January' => 'มกราคม',
            'February' => 'กุมภาพันธ์',
            'March' => 'มีนาคม',
            'April' => 'เมษายน',
            'May' => 'พฤษภาคม',
            'June' => 'มิถุนายน',
            'July' => 'กรกฎาคม',
            'August' => 'สิงหาคม',
            'September' => 'กันยายน',
            'October' => 'ตุลาคม',
            'November' => 'พฤศจิกายน',
            'December' => 'ธันวาคม',
        ];
        
        $shortMonths = [
            'January' => 'ม.ค.',
            'February' => 'ก.พ.',
            'March' => 'มี.ค.',
            'April' => 'เม.ย.',
            'May' => 'พ.ค.',
            'June' => 'มิ.ย.',
            'July' => 'ก.ค.',
            'August' => 'ส.ค.',
            'September' => 'ก.ย.',
            'October' => 'ต.ค.',
            'November' => 'พ.ย.',
            'December' => 'ธ.ค.',
        ];
    
        // ดึงข้อมูล Feature
        $Item = Feature::get()->toarray();
    
        if (!empty($Item)) {
            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['percent'] = 0;
            }
        }
    
        // คำนวณยอดในเดือนปัจจุบัน
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $count = Log::where('type', 'loginapp')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
    
        // แปลงชื่อเดือนภาษาอังกฤษเป็นภาษาไทยแบบย่อ
        $currentMonthName = Carbon::now()->format('F');
        $thaiMonth = $months[$currentMonthName];
    
        // สร้างข้อมูล graph ย้อนหลัง 6 เดือน
        $graphData = [];
        for ($i = 5; $i >= 0; $i--) {

            $date = Carbon::now()->subMonths($i); // เดือนย้อนหลัง
            $year = $date->year;
            $month = $date->month;

            // ดึงยอดวิวจากฐานข้อมูล
            $views = Log::where('type', 'loginapp')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
            ->count();

            $date = Carbon::now()->subMonths($i); // เดือนย้อนหลัง
            $thaiMonthShort = $shortMonths[$date->format('F')];
            $yearShort = ($date->year + 543) % 100; // คำนวณปี พ.ศ. แบบย่อ
            $graphData[] = [
                "month" => "{$thaiMonthShort}{$yearShort}",
                "views" => $views."" // ใส่จำนวนตัวเลขตัวอย่างแทน (แก้ให้ใช้ข้อมูลจริงได้)
            ];
        }
    
        // จัดการข้อมูล response
        if ($count) {
            $data = [
                "views" => $count,
                "month" => $thaiMonth,
                "graph" => $graphData
            ];
        } else {
            $data = [
                "views" => 0,
                "graph" => $graphData
            ];
        }
    
        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }
    

    public function graphRegisterOld()
    {
        setlocale(LC_TIME, 'th_TH.UTF-8');

        // Get the current month in English
        $currentMonth = strftime('%B');

        $months = [
            'January' => 'มกราคม',
            'February' => 'กุมภาพันธ์',
            'March' => 'มีนาคม',
            'April' => 'เมษายน',
            'May' => 'พฤษภาคม',
            'June' => 'มิถุนายน',
            'July' => 'กรกฎาคม',
            'August' => 'สิงหาคม',
            'September' => 'กันยายน',
            'October' => 'ตุลาคม',
            'November' => 'พฤศจิกายน',
            'December' => 'ธันวาคม',
        ];

        $Item = Feature::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['percent'] = 0;
            }
        }

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $count = Log::where('type', 'loginapp')
        ->whereYear('created_at', $currentYear)
        ->whereMonth('created_at', $currentMonth)
        ->count();
        $currentMonth = date('F');
        $thaiMonth = $months[$currentMonth];

        if($count){
            $data = [
                "views" => $count,
                "month" => $thaiMonth,
                "graph" => [
                    [
                        "month" => 'ก.ค.57',
                        "views" => '25'
                    ],
                    [
                        "month" => 'ส.ค.57',
                        "views" => '42'
                    ],
                    [
                        "month" => 'ก.ย.57',
                        "views" => '75'
                    ],
                    [
                        "month" => 'ต.ค.57',
                        "views" => '54'
                    ],
                    [
                        "month" => 'พ.ย.57',
                        "views" => '68'
                    ],
                    [
                        "month" => 'ธ.ค.57',
                        "views" => '11'
                    ],
                ]
            ];
        }else{
            $data = [
                "views" => 0,
                "graph" =>  [
                    [
                        "month" => 'ก.ค.57',
                        "views" => '25'
                    ],
                    [
                        "month" => 'ส.ค.57',
                        "views" => '42'
                    ],
                    [
                        "month" => 'ก.ย.57',
                        "views" => '75'
                    ],
                    [
                        "month" => 'ต.ค.57',
                        "views" => '54'
                    ],
                    [
                        "month" => 'พ.ย.57',
                        "views" => '68'
                    ],
                    [
                        "month" => 'ธ.ค.57',
                        "views" => '11'
                    ],
                ]
            ];
        }
       
        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\FactoryActivity;
use App\Models\DeductPaid;

class FactoryActivityController extends Controller
{
    public function summaryActivity(Request $request)
    {
        $query = FactoryActivity::query();

        if ($request->has('frammer_id')) {
            $query->where('frammer_id', $request->frammer_id);
        }

        if ($request->has('sugartype')) {
            $query->where('sugartype', $request->sugartype);
        }

        if (isset($request->activitytype)) {
            $query->where('activitytype', $request->activitytype);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('selectdate', [$startDate, $endDate]);
        }
        $query->orderBy('plotsugar_id', 'asc');

        $items = $query->get()
            ->groupBy('plotsugar_id')
            ->map(function ($group) {
                return [
                    'plotsugar_id' => $group->first()->plotsugar_id,
                    'activities' => $group->map(function ($item, $index) {
                        return [
                            'No' => $item->No,
                            'id' => $item->id,
                            'frammer_id' => $item->frammer_id,
                            'sugartype' => $item->sugartype,
                            'activitytype' => $item->activitytype,
                            'selectdate' => $item->selectdate,
                            'image' => $item->image,
                            'fuelcost' => $item->fuelcost,
                            'laborwages' => $item->laborwages,
                            // Add other fields as needed
                        ];
                    })->values(),
                ];
            })->values();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
    }

    public function getList($id)
    {
        $Item = FactoryActivity::where('province_code', $id)->get();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function check_no(Request $request)
    {
        // dd($request->all());
        $query = FactoryActivity::query();

        if ($request->has('frammer_id')) {
            $query->where('frammer_id', $request->frammer_id);
        }

        if ($request->has('sugartype')) {
            $query->where('sugartype', $request->sugartype);
        }

        if (isset($request->activitytype)) {
            $query->where('activitytype', $request->activitytype);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('selectdate', [$startDate, $endDate]);
        }
        $query->orderBy('plotsugar_id', 'asc');
        $items = $query->get();
        $lastItem = $items->last()->No + 1 ?? 0;

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $lastItem);
    }

    public function schedule(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $frammerId = $request->frammer_id;

        $Item = FactoryActivity::where('frammer_id', $frammerId)
            ->whereDate('selectdate', '>=', $startDate)
            ->whereDate('selectdate', '<=', $endDate)
            ->get();

        if (!$Item->isEmpty()) {
            $Item = $Item->map(function ($item, $index) {
                $filteredItem = collect($item->toArray())
                    ->filter(function ($value) {
                        return $value !== null;
                    })
                    ->toArray();

                $filteredItem['No'] = $item->No;
                return $filteredItem;
            });
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getPage(Request $request)
    {
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        // dd($page);

        $activityType = $request->activitytype;
        $frammerId = $request->frammer_id;
        $sugarType = $request->sugartype;
        $plotsugar_id = $request->plotsugar_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $col = [
            'id', 'activitytype', 'frammer_id', 'sugartype', 'plotsugar_id',
            'selectdate', 'image', 'created_at', 'updated_at'
        ];

        switch ($activityType) {
            case '0':
                $col = array_merge($col, ['soilImprovement', 'plowingtype', 'subtypeplowing', 'insecticidecost', 'equipmentrent', 'laborwages', 'fuelcost']);
                break;
            case '1':
                $col = array_merge($col, ['sugarcane', 'plantingsystem', 'fertilizer', 'expenses', 'sugartypecost', 'sugarcaneplantingcost', 'fertilizercost', 'fuelcost']);
                break;
            case '2':
                $col = array_merge($col, ['wateringsystem', 'laborwages', 'fuelcost']);
                break;
            case '3':
                $col = array_merge($col, ['fertilizerquantity', 'otheringredients', 'amountureafertilizer', 'herbicide', 'othertypes', 'otheringredientcosts', 'herbicidecost', 'fertilizer', 'laborwages', 'fuelcost']);
                break;
            case '4':
                $col = array_merge($col, ['weed', 'plantdiseases', 'pests', 'pesticidecost', 'laborwages', 'fuelcost']);
                break;
            case '5':
                $col = array_merge($col, ['fertilizertype', 'fertilizer', 'fertilizerquantity', 'laborwages', 'fuelcost']);
                break;
            case '6':
                $col = array_merge($col, ['cuttingtype', 'sugarcanetype', 'sugarcanecuttinglabor', 'fuelcost']);
                break;
            case '7':
                $col = array_merge($col, ['laborwages', 'fuelcost']);
                break;
            default:
                $col = array_merge($col, [
                    'soilImprovement', 'plowingtype', 'subtypeplowing', 'insecticidecost', 'equipmentrent',
                    'sugarcane', 'plantingsystem', 'fertilizer', 'expenses', 'sugartypecost', 'sugarcaneplantingcost', 'fertilizercost',
                    'wateringsystem',
                    'fertilizerquantity', 'otheringredients', 'amountureafertilizer', 'herbicide', 'othertypes', 'otheringredientcosts', 'herbicidecost',
                    'weed', 'plantdiseases', 'pests', 'pesticidecost',
                    'fertilizertype',
                    'cuttingtype', 'sugarcanetype', 'sugarcanecuttinglabor',
                    'laborwages', 'fuelcost'
                ]);
                break;
        }

        $orderby = [
            '', 'activitytype', 'frammer_id', 'sugartype', 'plotsugar_id',
            'selectdate', 'created_at'
        ];

        $D = FactoryActivity::select($col);

        if (isset($activityType)) {
            $D->where('activitytype', $activityType);
        }

        if (isset($frammerId)) {
            $D->where('frammer_id', $frammerId);
        }

        if (isset($sugarType)) {
            $D->where('sugartype', $sugarType);
        }

        if (isset($plotsugar_id)) {
            $D->where('plotsugar_id', $plotsugar_id);
        }

        if (isset($startDate) && isset($endDate)) {
            $D->whereBetween('selectdate', [
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay()
            ]);
        }

        if ($orderby[$order[0]['column']]) {
            $D->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if ($search['value'] != '' && $search['value'] != null) {
            $D->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {
            $No = (($page - 1) * $length);

            foreach ($d as $key => $item) {
                $No++;
                $item->No = $No;
                if (isset($item->image)) {
                    $item->image = url($item->image);
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function getPagemobile(Request $request)
    {
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        // dd($request->all());

        $activityType = $request->activitytype;
        $frammerId = $request->frammer_id;
        $sugarType = $request->sugartype;
        $plotsugar_id = $request->plotsugar_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $col = [
            'id', 'activitytype', 'frammer_id', 'sugartype', 'plotsugar_id',
            'selectdate', 'image', 'created_at', 'updated_at'
        ];

        switch ($activityType) {
            case '0':
                $col = array_merge($col, ['soilImprovement', 'plowingtype', 'subtypeplowing', 'insecticidecost', 'equipmentrent', 'laborwages', 'fuelcost']);
                break;
            case '1':
                $col = array_merge($col, ['sugarcane', 'plantingsystem', 'fertilizer', 'expenses', 'sugartypecost', 'sugarcaneplantingcost', 'fertilizercost', 'fuelcost']);
                break;
            case '2':
                $col = array_merge($col, ['wateringsystem', 'laborwages', 'fuelcost']);
                break;
            case '3':
                $col = array_merge($col, ['fertilizerquantity', 'otheringredients', 'amountureafertilizer', 'herbicide', 'othertypes', 'otheringredientcosts', 'herbicidecost', 'fertilizer', 'laborwages', 'fuelcost']);
                break;
            case '4':
                $col = array_merge($col, ['weed', 'plantdiseases', 'pests', 'pesticidecost', 'laborwages', 'fuelcost']);
                break;
            case '5':
                $col = array_merge($col, ['fertilizertype', 'fertilizer', 'fertilizerquantity', 'laborwages', 'fuelcost']);
                break;
            case '6':
                $col = array_merge($col, ['cuttingtype', 'sugarcanetype', 'sugarcanecuttinglabor', 'fuelcost']);
                break;
            case '7':
                $col = array_merge($col, ['laborwages', 'fuelcost']);
                break;
            default:
                $col = array_merge($col, [
                    'soilImprovement', 'plowingtype', 'subtypeplowing', 'insecticidecost', 'equipmentrent',
                    'sugarcane', 'plantingsystem', 'fertilizer', 'expenses', 'sugartypecost', 'sugarcaneplantingcost', 'fertilizercost',
                    'wateringsystem',
                    'fertilizerquantity', 'otheringredients', 'amountureafertilizer', 'herbicide', 'othertypes', 'otheringredientcosts', 'herbicidecost',
                    'weed', 'plantdiseases', 'pests', 'pesticidecost',
                    'fertilizertype',
                    'cuttingtype', 'sugarcanetype', 'sugarcanecuttinglabor',
                    'laborwages', 'fuelcost'
                ]);
                break;
        }

        $orderby = [
            '', 'activitytype', 'frammer_id', 'sugartype', 'plotsugar_id',
            'selectdate', 'created_at'
        ];

        $D = FactoryActivity::select($col);

        if (isset($activityType)) {
            $D->where('activitytype', $activityType);
        }

        if (isset($sugarType)) {
            $D->where('sugartype', $sugarType);
        }

        if (isset($frammerId)) {
            $D->where('frammer_id', $frammerId);
        }

        if (!empty($plotsugar_id) && is_array($plotsugar_id)) {
            $D->whereIn('plotsugar_id', array_map('strval', $plotsugar_id));
        } elseif (isset($plotsugar_id)) {
            $D->where('plotsugar_id', $plotsugar_id);
        }

        if (isset($startDate) && isset($endDate)) {
            $D->whereDate('selectdate', '>=', $startDate)
                ->whereDate('selectdate', '<=', $endDate);
        }

        if ($orderby[$order[0]['column']]) {
            $D->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if ($search['value'] != '' && $search['value'] != null) {
            $D->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {
            $No = (($page - 1) * $length);

            foreach ($d as $key => $item) {
                $No++;
                $item->No = $No;
                if (isset($item->image)) {
                    $item->image = url($item->image);
                }
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
        // dd($request->all());

        $rules = [
            'activitytype' => 'required',
            'frammer_id' => 'required',
            'sugartype' => 'required',
            'plotsugar' => 'required',
            'selectdate' => 'required',
        ];

        $messages = [
            'activitytype.required' => 'activitytype is required',
            'frammer_id.required' => 'frammer_id is required',
            'sugartype.required' => 'sugartype is required',
            'plotsugar.required' => 'plotsugar is required',
            'selectdate.required' => 'selectdate is required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return $this->returnErrorData($errors, 422);
        }

        try {
            DB::beginTransaction();

            $data = [];

            foreach ($request->plotsugar as $value) {
                $Item = new FactoryActivity();
                $Item->No = $request->no;
                $Item->activitytype = $request->activitytype;
                $Item->frammer_id = $request->frammer_id;
                $Item->sugartype = $request->sugartype;
                $Item->plotsugar_id = $value;
                $Item->selectdate = $request->selectdate;

                $date = Carbon::parse($request->selectdate);
                $Item->image = $request->image;

                switch ($request->activitytype) {
                    case '0':
                        $Item->soilImprovement = $request->soil_improvement;
                        $Item->plowingtype = $request->plowingtype;
                        $Item->subtypeplowing = $request->subtypeplowing;
                        $Item->insecticidecost = $request->insecticidecost;
                        $Item->equipmentrent = $request->equipmentrent;
                        $Item->laborwages = $request->laborwages;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "insecticidecost" => $request->insecticidecost,
                            "equipmentrent" => $request->equipmentrent,
                            "laborwages" => $request->laborwages,
                            "fuelcost" => $request->fuelcost
                        ]);

                        break;
                    case '1':
                        $Item->sugarcane = $request->sugarcane;
                        $Item->plantingsystem = $request->plantingsystem;
                        $Item->fertilizer = $request->fertilizer;
                        $Item->expenses = $request->expenses;
                        $Item->sugartypecost = $request->sugartypecost;
                        $Item->sugarcaneplantingcost = $request->sugarcaneplantingcost;
                        $Item->fertilizercost = $request->fertilizercost;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "sugartypecost" => $request->sugartypecost,
                            "sugarcaneplantingcost" => $request->sugarcaneplantingcost,
                            "fertilizercost" => $request->fertilizercost,
                            "fuelcost" => $request->fuelcost
                        ]);

                        break;
                    case '2':
                        $Item->wateringsystem = $request->wateringsystem;
                        $Item->laborwages = $request->laborwages;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "laborwages" => $request->laborwages,
                            "fuelcost" => $request->fuelcost
                        ]);
                        break;
                    case '3':
                        $Item->fertilizerquantity = $request->fertilizerquantity;
                        $Item->otheringredients = $request->otheringredients;
                        $Item->amountureafertilizer = $request->amountureafertilizer;
                        $Item->herbicide = $request->herbicide;
                        $Item->othertypes = $request->othertypes;
                        $Item->otheringredientcosts = $request->otheringredientcosts;
                        $Item->herbicidecost = $request->herbicidecost;
                        $Item->fertilizer = $request->fertilizer;
                        $Item->laborwages = $request->laborwages;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "amountureafertilizer" => $request->amountureafertilizer,
                            "otheringredientcosts" => $request->otheringredientcosts,
                            "herbicidecost" => $request->herbicidecost,
                            "fertilizer" => $request->fertilizer,
                            "laborwages" => $request->laborwages,
                            "fuelcost" => $request->fuelcost
                        ]);
                        break;
                    case '4':
                        $Item->weed = $request->weed;
                        $Item->plantdiseases = $request->plantdiseases;
                        $Item->pests = $request->pests;
                        $Item->pesticidecost = $request->pesticidecost;
                        $Item->laborwages = $request->laborwages;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "pesticidecost" => $request->pesticidecost,
                            "laborwages" => $request->laborwages,
                            "fuelcost" => $request->fuelcost
                        ]);
                        break;
                    case '5':
                        $Item->fertilizertype = $request->fertilizertype;
                        $Item->fertilizer = $request->fertilizer;
                        $Item->fertilizerquantity = $request->fertilizerquantity;
                        $Item->laborwages = $request->laborwages;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "fertilizer" => $request->fertilizer,
                            "laborwages" => $request->laborwages,
                            "fuelcost" => $request->fuelcost
                        ]);
                        break;
                    case '6':
                        $Item->cuttingtype = $request->cuttingtype;
                        $Item->sugarcanetype = $request->sugarcanetype;
                        $Item->sugarcanecuttinglabor = $request->sugarcanecuttinglabor;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "sugarcanecuttinglabor" => $request->sugarcanecuttinglabor,
                            "fuelcost" => $request->fuelcost
                        ]);
                        break;
                    case '7':
                        $Item->laborwages = $request->laborwages;
                        $Item->fuelcost = $request->fuelcost;

                        $data = array_merge($data, [
                            "laborwages" => $request->laborwages,
                            "fuelcost" => $request->fuelcost
                        ]);
                        break;
                    default:
                        DB::rollBack();
                        return $this->returnErrorData("เกิดข้อผิดพลาด", 422);
                        break;
                }

                $Item->save();

                foreach ($data as $key => $value) {
                    $deduct = new DeductPaid();
                    $deduct->frammer_id = $request->frammer_id;
                    $deduct->factory_activity_id  = $Item->id;
                    switch ($key) {
                        case 'fuelcost':
                            $deduct->deduct_type_id = 1; // เงินหักค่าเชื้อเพลิง
                            break;
                        case 'laborwages':
                            $deduct->deduct_type_id = 2; // เงินหักค่าจ้างแรงงาน
                            break;
                        case 'fertilizer':
                            $deduct->deduct_type_id = 3; // เงินหักค่าปุ๋ยรองพื้น
                            break;
                        case 'pesticidecost':
                            $deduct->deduct_type_id = 4; // เงินหักค่ากำจัดศัตรูพืช
                            break;
                        case 'equipmentrent':
                            $deduct->deduct_type_id = 5; // เงินหักค่าเช่าอุปกรณ์
                            break;
                        case 'sugartypecost':
                            $deduct->deduct_type_id = 6; // เงินหักค่าพันธ์อ้อย
                            break;
                        case 'sugarcaneplantingcost':
                            $deduct->deduct_type_id = 7; // เงินหักค่าปลูกอ้อย
                            break;
                        case 'amountureafertilizer':
                            $deduct->deduct_type_id = 8; // เงินหักค่าปุ๋ยยูเรีย
                            break;
                        case 'otheringredientcosts':
                            $deduct->deduct_type_id = 9; // เงินหักค่าส่วนผสมอื่นๆ
                            break;
                        case 'herbicidecost':
                            $deduct->deduct_type_id = 10; // เงินหักค่ากำจัดวัชพืช
                            break;
                        case 'insecticidecost':
                            $deduct->deduct_type_id = 11; // เงินหักค่ากำจัดวัชพืช
                            break;
                        case 'sugarcanecuttinglabor':
                            $deduct->deduct_type_id = 12; // เงินหักค่าตัดอ้อย
                            break;
                        case 'fertilizercost':
                            $deduct->deduct_type_id = 3; // เงินหักค่าปุ๋ยรองพื้น
                            break;
                        default:
                            $deduct->deduct_type_id = 1; // Default value if no match is found
                            break;
                    }
                    $deduct->paid = $value;
                    $deduct->month = $date->month;
                    $deduct->year = $date->year;
                    // dd($deduct->all());
                    $deduct->save();
                }

                $data = [];
            }

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
     * @param  \App\Models\FactoryActivity  $FactoryActivity
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = FactoryActivity::find($id);

        if (!$item) {
            return $this->returnError('Item not found', 404);
        }

        $activityType = $item->activitytype;

        $data = [
            'id' => $item->id,
            'activitytype' => $item->activitytype,
            'frammer_id' => $item->frammer_id,
            'sugartype' => $item->sugartype,
            'plotsugar_id' => $item->plotsugar_id,
            'selectdate' => $item->selectdate,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];

        if (isset($item->image)) {
            $data = array_merge($data, [
                'image' => url($item->image)
            ]);
        } else {
            $data = array_merge($data, [
                'image' => null
            ]);
        }
        switch ($activityType) {
            case '0':
                $data = array_merge($data, [
                    'soilImprovement' => $item->soilImprovement,
                    'plowingtype' => $item->plowingtype,
                    'subtypeplowing' => $item->subtypeplowing,
                    'insecticidecost' => $item->insecticidecost,
                    'equipmentrent' => $item->equipmentrent,
                    'laborwages' => $item->laborwages,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            case '1':
                $data = array_merge($data, [
                    'sugarcane' => $item->sugarcane,
                    'plantingsystem' => $item->plantingsystem,
                    'fertilizer' => $item->fertilizer,
                    'expenses' => $item->expenses,
                    'sugartypecost' => $item->sugartypecost,
                    'sugarcaneplantingcost' => $item->sugarcaneplantingcost,
                    'fertilizercost' => $item->fertilizercost,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            case '2':
                $data = array_merge($data, [
                    'wateringsystem' => $item->wateringsystem,
                    'laborwages' => $item->laborwages,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            case '3':
                $data = array_merge($data, [
                    'fertilizerquantity' => $item->fertilizerquantity,
                    'otheringredients' => $item->otheringredients,
                    'amountureafertilizer' => $item->amountureafertilizer,
                    'herbicide' => $item->herbicide,
                    'othertypes' => $item->othertypes,
                    'otheringredientcosts' => $item->otheringredientcosts,
                    'herbicidecost' => $item->herbicidecost,
                    'fertilizer' => $item->fertilizer,
                    'laborwages' => $item->laborwages,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            case '4':
                $data = array_merge($data, [
                    'weed' => $item->weed,
                    'plantdiseases' => $item->plantdiseases,
                    'pests' => $item->pests,
                    'pesticidecost' => $item->pesticidecost,
                    'laborwages' => $item->laborwages,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            case '5':
                $data = array_merge($data, [
                    'fertilizertype' => $item->fertilizertype,
                    'fertilizer' => $item->fertilizer,
                    'fertilizerquantity' => $item->fertilizerquantity,
                    'laborwages' => $item->laborwages,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            case '6':
                $data = array_merge($data, [
                    'cuttingtype' => $item->cuttingtype,
                    'sugarcanetype' => $item->sugarcanetype,
                    'sugarcanecuttinglabor' => $item->sugarcanecuttinglabor,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            case '7':
                $data = array_merge($data, [
                    'laborwages' => $item->laborwages,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
            default:
                $data = array_merge($data, [
                    'soilImprovement' => $item->soilImprovement,
                    'plowingtype' => $item->plowingtype,
                    'subtypeplowing' => $item->subtypeplowing,
                    'insecticidecost' => $item->insecticidecost,
                    'equipmentrent' => $item->equipmentrent,
                    'sugarcane' => $item->sugarcane,
                    'plantingsystem' => $item->plantingsystem,
                    'fertilizer' => $item->fertilizer,
                    'expenses' => $item->expenses,
                    'sugartypecost' => $item->sugartypecost,
                    'sugarcaneplantingcost' => $item->sugarcaneplantingcost,
                    'fertilizercost' => $item->fertilizercost,
                    'wateringsystem' => $item->wateringsystem,
                    'fertilizerquantity' => $item->fertilizerquantity,
                    'otheringredients' => $item->otheringredients,
                    'amountureafertilizer' => $item->amountureafertilizer,
                    'herbicide' => $item->herbicide,
                    'othertypes' => $item->othertypes,
                    'otheringredientcosts' => $item->otheringredientcosts,
                    'herbicidecost' => $item->herbicidecost,
                    'weed' => $item->weed,
                    'plantdiseases' => $item->plantdiseases,
                    'pests' => $item->pests,
                    'pesticidecost' => $item->pesticidecost,
                    'fertilizertype' => $item->fertilizertype,
                    'cuttingtype' => $item->cuttingtype,
                    'sugarcanetype' => $item->sugarcanetype,
                    'sugarcanecuttinglabor' => $item->sugarcanecuttinglabor,
                    'laborwages' => $item->laborwages,
                    'fuelcost' => $item->fuelcost
                ]);
                break;
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FactoryActivity  $FactoryActivity
     * @return \Illuminate\Http\Response
     */
    public function edit(FactoryActivity $FactoryActivity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FactoryActivity  $FactoryActivity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd(request()->all());

        try {
            DB::beginTransaction();

            $Item =  FactoryActivity::find($id);
            $Item->No = $Item->No;
            $Item->activitytype = $Item->activitytype;
            $Item->frammer_id = $Item->frammer_id;
            $Item->sugartype = $Item->sugartype;
            $Item->plotsugar_id = $Item->plotsugar_id;
            $Item->selectdate = $Item->selectdate;
            if (isset($request->image)) {
                $Item->image = $request->image;
            } else {
                $Item->image = $Item->image;
            }

            switch ($Item->activitytype) {
                case '0':
                    $Item->soilImprovement = $request->soil_improvement;
                    $Item->plowingtype = $request->plowingtype;
                    $Item->subtypeplowing = $request->subtypeplowing;
                    $Item->insecticidecost = $request->insecticidecost;
                    $Item->equipmentrent = $request->equipmentrent;
                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                case '1':
                    $Item->sugarcane = $request->sugarcane;
                    $Item->plantingsystem = $request->plantingsystem;
                    $Item->fertilizer = $request->fertilizer;
                    $Item->expenses = $request->expenses;
                    $Item->sugartypecost = $request->sugartypecost;
                    $Item->sugarcaneplantingcost = $request->sugarcaneplantingcost;
                    $Item->fertilizercost = $request->fertilizercost;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                case '2':
                    $Item->wateringsystem = $request->wateringsystem;
                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                case '3':
                    $Item->fertilizerquantity = $request->fertilizerquantity;
                    $Item->otheringredients = $request->otheringredients;
                    $Item->amountureafertilizer = $request->amountureafertilizer;
                    $Item->herbicide = $request->herbicide;
                    $Item->othertypes = $request->othertypes;
                    $Item->otheringredientcosts = $request->otheringredientcosts;
                    $Item->herbicidecost = $request->herbicidecost;
                    $Item->fertilizer = $request->fertilizer;
                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                case '4':
                    $Item->weed = $request->weed;
                    $Item->plantdiseases = $request->plantdiseases;
                    $Item->pests = $request->pests;
                    $Item->pesticidecost = $request->pesticidecost;
                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                case '5':
                    $Item->fertilizertype = $request->fertilizertype;
                    $Item->fertilizer = $request->fertilizer;
                    $Item->fertilizerquantity = $request->fertilizerquantity;
                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                case '6':
                    $Item->cuttingtype = $request->cuttingtype;
                    $Item->sugarcanetype = $request->sugarcanetype;
                    $Item->sugarcanecuttinglabor = $request->sugarcanecuttinglabor;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                case '7':
                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;
                    break;
                default:
                    DB::rollBack();
                    return $this->returnErrorData("เกิดข้อผิดพลาด", 422);
                    break;
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FactoryActivity  $FactoryActivity
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = FactoryActivity::find($id);
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

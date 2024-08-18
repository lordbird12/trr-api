<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;

use App\Models\FactoryActivity;
use App\Models\DeductPaid;
use App\Models\FrammerArea;


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

        if (!empty($request->plotsugar_id) && is_array($request->plotsugar_id)) {
            $query->whereIn('plotsugar_id', array_map('strval', $request->plotsugar_id));
        } elseif (isset($request->plotsugar_id)) {
            $query->where('plotsugar_id', $request->plotsugar_id);
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

        // if (!empty($Item)) {

        //     for ($i = 0; $i < count($Item); $i++) {
        //         $Item[$i]['No'] = $i + 1;
        //     }
        // }

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

        if ($request->has('activitytype')) {
            $query->where('activitytype', $request->activitytype);
        }

        if ($request->has('plotsugar_id')) {
            $query->where('plotsugar_id', $request->plotsugar_id);
        }

        if (isset($request->selectdate)) {
            $query->whereDate('selectdate', '<=', $request->selectdate);
        }

        $query->orderBy('plotsugar_id', 'asc');

        $maxNo = $query->max('No');

        $nextNo = $maxNo !== null ? $maxNo : 0;

        if (isset($request->selectdate)) {
            $date = new DateTime($request->selectdate);
            $formattedDate = $date->format('Y-m-d');
            $exactDateMatch = $query->whereRaw("DATE(selectdate) = ?", [$formattedDate])->exists();

            if (!$exactDateMatch) {
                $nextNo += 1;
            }
        }


        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', (int)$nextNo);
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
            'id',
            'No',
            'activitytype',
            'frammer_id',
            'sugartype',
            'plotsugar_id',
            'selectdate',
            'image',
            'created_at',
            'updated_at'
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
                    'soilImprovement',
                    'plowingtype',
                    'subtypeplowing',
                    'insecticidecost',
                    'equipmentrent',
                    'sugarcane',
                    'plantingsystem',
                    'fertilizer',
                    'expenses',
                    'sugartypecost',
                    'sugarcaneplantingcost',
                    'fertilizercost',
                    'wateringsystem',
                    'fertilizerquantity',
                    'otheringredients',
                    'amountureafertilizer',
                    'herbicide',
                    'othertypes',
                    'otheringredientcosts',
                    'herbicidecost',
                    'weed',
                    'plantdiseases',
                    'pests',
                    'pesticidecost',
                    'fertilizertype',
                    'cuttingtype',
                    'sugarcanetype',
                    'sugarcanecuttinglabor',
                    'laborwages',
                    'fuelcost'
                ]);
                break;
        }

        $orderby = [
            '',
            'activitytype',
            'frammer_id',
            'sugartype',
            'plotsugar_id',
            'selectdate',
            'created_at'
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
            // $No = (($page - 1) * $length);

            foreach ($d as $key => $item) {
                // $No++;
                // $item->No = $No;
                if (isset($item->image)) {
                    $item->image = url($item->image);
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function getPagemobile(Request $request)
    {
        $length = 100;
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
            'id',
            'No',
            'trans_id',
            'activitytype',
            'frammer_id',
            'sugartype',
            'plotsugar_id',
            'selectdate',
            'image',
            'created_at',
            'updated_at'
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
                    'soilImprovement',
                    'plowingtype',
                    'subtypeplowing',
                    'insecticidecost',
                    'equipmentrent',
                    'sugarcane',
                    'plantingsystem',
                    'fertilizer',
                    'expenses',
                    'sugartypecost',
                    'sugarcaneplantingcost',
                    'fertilizercost',
                    'wateringsystem',
                    'fertilizerquantity',
                    'otheringredients',
                    'amountureafertilizer',
                    'herbicide',
                    'othertypes',
                    'otheringredientcosts',
                    'herbicidecost',
                    'weed',
                    'plantdiseases',
                    'pests',
                    'pesticidecost',
                    'fertilizertype',
                    'cuttingtype',
                    'sugarcanetype',
                    'sugarcanecuttinglabor',
                    'laborwages',
                    'fuelcost'
                ]);
                break;
        }

        $orderby = [
            '',
            'activitytype',
            'frammer_id',
            'sugartype',
            'plotsugar_id',
            'selectdate',
            'created_at'
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
        // $d = $D;

        $groupedData = [];
        if ($d->isNotEmpty()) {

            foreach ($d as $key => $item) {
                if (isset($item->image)) {
                    $item->image = url($item->image);
                }
            }

            foreach ($d as $item) {
                $key = $item->No . '-' . Carbon::parse($item->selectdate)->format('Y-m-d') . '-' . $item->plotsugar_id . '-' . $item->frammer_id . '-' . $item->sugartype . '-' . $item->activitytype;
                if (!isset($groupedData[$key])) {
                    $groupedData[$key] = [
                        'No' => $item->No,
                        'trans_id' => $item->trans_id,
                        'activitytype' => $item->activitytype,
                        'frammer_id' => $item->frammer_id,
                        'sugartype' => $item->sugartype,
                        'plotsugar_id' => $item->plotsugar_id,
                        'selectdate' => Carbon::parse($item->selectdate)->format('Y-m-d'),
                        'subdata' => []
                    ];
                }
                $subdata = [
                    "id" => $item->id,
                    'image' => $item->image,
                    'fulldate' => $item->selectdate,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
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
                ];

                $filteredSubdata = array_filter($subdata, function ($value, $key) {
                    return $value !== null || $key === 'image';
                }, ARRAY_FILTER_USE_BOTH);

                $groupedData[$key]['subdata'][] = $filteredSubdata;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', array_values($groupedData));
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
            //get sum area size
            // $frammer_area = FrammerArea::whereIn('area', array_map('strval', $request->plotsugar))
            //     ->where('frammer_id', $request->frammer_id)
            //     ->get();
            $query = FactoryActivity::query();
            $total_area_size = collect($request->areasize)->sum() ?? 0;
            // dd($total_area_size);
            $areaSizes = $request->areasize;
            $maxtrans = $query->max('trans_id');

            $nexttrnas = $maxtrans !== null ? $maxtrans + 1 : 1;
            $date = Carbon::parse($request->selectdate);
            $data = [];

            foreach ($request->plotsugar as $index => $value) {
                $Item = new FactoryActivity();
                $Item->No = $request->no;
                $Item->trans_id = $nexttrnas;
                $Item->activitytype = $request->activitytype;
                $Item->frammer_id = $request->frammer_id;
                $Item->sugartype = $request->sugartype;
                $Item->plotsugar_id = $value;
                $Item->selectdate = $request->selectdate;


                $Item->image = $request->image;

                $areaSize = $areaSizes[$index] ?? 0;
                $Item->areasize = $areaSize;
                // dd($areaSize);
                $areaRatio = round($areaSize / $total_area_size, 2);

                switch ($request->activitytype) {
                    case '0':
                        $Item->soilImprovement = $request->soil_improvement;
                        $Item->plowingtype = $request->plowingtype;
                        $Item->subtypeplowing = $request->subtypeplowing;
                        $Item->insecticidecost = $request->insecticidecost * $areaRatio;
                        $Item->equipmentrent = $request->equipmentrent * $areaRatio;
                        $Item->laborwages = $request->laborwages * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "insecticidecost" => $request->insecticidecost * $areaRatio,
                            "equipmentrent" => $request->equipmentrent * $areaRatio,
                            "laborwages" => $request->laborwages * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
                        ]);

                        break;
                    case '1':
                        $Item->sugarcane = $request->sugarcane;
                        $Item->plantingsystem = $request->plantingsystem;
                        $Item->fertilizer = $request->fertilizer;
                        $Item->expenses = $request->expenses;
                        $Item->sugartypecost = $request->sugartypecost * $areaRatio;
                        $Item->sugarcaneplantingcost = $request->sugarcaneplantingcost * $areaRatio;
                        $Item->fertilizercost = $request->fertilizercost * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "sugartypecost" => $request->sugartypecost * $areaRatio,
                            "sugarcaneplantingcost" => $request->sugarcaneplantingcost * $areaRatio,
                            "fertilizercost" => $request->fertilizercost * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
                        ]);

                        break;
                    case '2':
                        $Item->wateringsystem = $request->wateringsystem;
                        $Item->laborwages = $request->laborwages * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "laborwages" => $request->laborwages * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
                        ]);
                        break;
                    case '3':
                        $Item->fertilizerquantity = $request->fertilizerquantity;
                        $Item->otheringredients = $request->otheringredients;
                        $Item->amountureafertilizer = $request->amountureafertilizer;
                        $Item->herbicide = $request->herbicide;
                        $Item->othertypes = $request->othertypes;
                        $Item->otheringredientcosts = $request->otheringredientcosts * $areaRatio;
                        $Item->herbicidecost = $request->herbicidecost * $areaRatio;
                        $Item->fertilizer = $request->fertilizer;
                        $Item->laborwages = $request->laborwages * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "amountureafertilizer" => $request->amountureafertilizer,
                            "otheringredientcosts" => $request->otheringredientcosts * $areaRatio,
                            "herbicidecost" => $request->herbicidecost * $areaRatio,
                            "fertilizer" => $request->fertilizer,
                            "laborwages" => $request->laborwages * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
                        ]);
                        break;
                    case '4':
                        $Item->weed = $request->weed;
                        $Item->plantdiseases = $request->plantdiseases;
                        $Item->pests = $request->pests;
                        $Item->pesticidecost = $request->pesticidecost * $areaRatio;
                        $Item->laborwages = $request->laborwages * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "pesticidecost" => $request->pesticidecost * $areaRatio,
                            "laborwages" => $request->laborwages * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
                        ]);
                        break;
                    case '5':
                        $Item->fertilizertype = $request->fertilizertype;
                        $Item->fertilizer = $request->fertilizer;
                        $Item->fertilizerquantity = $request->fertilizerquantity;
                        $Item->laborwages = $request->laborwages * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "fertilizer" => $request->fertilizer,
                            "laborwages" => $request->laborwages * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
                        ]);
                        break;
                    case '6':
                        $Item->cuttingtype = $request->cuttingtype;
                        $Item->sugarcanetype = $request->sugarcanetype;
                        $Item->sugarcanecuttinglabor = $request->sugarcanecuttinglabor * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "sugarcanecuttinglabor" => $request->sugarcanecuttinglabor * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
                        ]);
                        break;
                    case '7':
                        $Item->laborwages = $request->laborwages * $areaRatio;
                        $Item->fuelcost = $request->fuelcost * $areaRatio;

                        $data = array_merge($data, [
                            "laborwages" => $request->laborwages * $areaRatio,
                            "fuelcost" => $request->fuelcost * $areaRatio
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
                    $deduct->paid = $value ?? 0;
                    $deduct->month = $date->month;
                    $deduct->year = $date->year;
                    // dd($deduct->all());
                    $deduct->save();
                }

                $data = [];
            }

            //
            foreach ($request->plotsugar as $plotsugar_id) {
                $this->reorderNo($request->frammer_id, $request->sugartype, $request->activitytype, $plotsugar_id);
            }
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

    private function reorderNo($frammerId, $sugartype, $activitytype, $plotsugar_id)
    {
        $items = FactoryActivity::where('frammer_id', $frammerId)
            ->where('sugartype', $sugartype)
            ->where('activitytype', $activitytype)
            ->where('plotsugar_id', $plotsugar_id)
            ->orderBy('selectdate')
            ->get();

        $currentNo = 0;
        $currentDate = null;

        foreach ($items as $item) {
            $itemDate = Carbon::parse($item->selectdate)->startOfDay();

            if ($currentDate === null || $itemDate->gt($currentDate)) {
                $currentNo++;
                $currentDate = $item->selectdate;
            }

            $item->No = $currentNo;
            $item->save();
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
            'No' => $item->No,
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

            $items = FactoryActivity::where('trans_id', $request->trans_id)->get();

            if ($items->isEmpty()) {
                return $this->returnErrorData("ไม่พบข้อมูลที่ต้องการอัปเดต", 404);
            }

            $Item = FactoryActivity::find($id);

            //update itself
            $data = [];
            $date = Carbon::parse($Item->selectdate);

            $Item->selectdate = $request->selectdate ?? $Item->selectdate;
            $Item->image = $request->image ?? $Item->image;

            switch ($request->activitytype) {
                case '0':
                    if ($Item->id == $id) {
                        $Item->soilImprovement = $request->soil_improvement;
                        $Item->plowingtype = $request->plowingtype;
                        $Item->subtypeplowing = $request->subtypeplowing;
                    }

                    $Item->insecticidecost = $request->insecticidecost ;
                    $Item->equipmentrent = $request->equipmentrent ;
                    $Item->laborwages = $request->laborwages ;
                    $Item->fuelcost = $request->fuelcost ;

                    $data = array_merge($data, [
                        "insecticidecost" => $request->insecticidecost ,
                        "equipmentrent" => $request->equipmentrent ,
                        "laborwages" => $request->laborwages ,
                        "fuelcost" => $request->fuelcost
                    ]);

                    break;
                case '1':
                    if ($Item->id == $id) {
                        $Item->sugarcane = $request->sugarcane;
                        $Item->plantingsystem = $request->plantingsystem;
                        $Item->fertilizer = $request->fertilizer;
                        $Item->expenses = $request->expenses;
                    }

                    $Item->sugartypecost = $request->sugartypecost ;
                    $Item->sugarcaneplantingcost = $request->sugarcaneplantingcost;
                    $Item->fertilizercost = $request->fertilizercost ;
                    $Item->fuelcost = $request->fuelcost;

                    $data = array_merge($data, [
                        "sugartypecost" => $request->sugartypecost,
                        "sugarcaneplantingcost" => $request->sugarcaneplantingcost ,
                        "fertilizercost" => $request->fertilizercost ,
                        "fuelcost" => $request->fuelcost
                    ]);

                    break;
                case '2':
                    if ($Item->id == $id) {
                        $Item->wateringsystem = $request->wateringsystem;
                    }

                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;

                    $data = array_merge($data, [
                        "laborwages" => $request->laborwages ,
                        "fuelcost" => $request->fuelcost
                    ]);
                    break;
                case '3':
                    if ($Item->id == $id) {
                        $Item->fertilizerquantity = $request->fertilizerquantity;
                        $Item->otheringredients = $request->otheringredients;
                        $Item->amountureafertilizer = $request->amountureafertilizer;
                        $Item->herbicide = $request->herbicide;
                        $Item->othertypes = $request->othertypes;
                    }

                    $Item->otheringredientcosts = $request->otheringredientcosts ;
                    $Item->herbicidecost = $request->herbicidecost ;
                    $Item->fertilizer = $request->fertilizer;
                    $Item->laborwages = $request->laborwages ;
                    $Item->fuelcost = $request->fuelcost ;

                    $data = array_merge($data, [
                        "amountureafertilizer" => $request->amountureafertilizer,
                        "otheringredientcosts" => $request->otheringredientcosts ,
                        "herbicidecost" => $request->herbicidecost ,
                        "fertilizer" => $request->fertilizer,
                        "laborwages" => $request->laborwages ,
                        "fuelcost" => $request->fuelcost
                    ]);
                    break;
                case '4':
                    if ($Item->id == $id) {
                        $Item->weed = $request->weed;
                        $Item->plantdiseases = $request->plantdiseases;
                        $Item->pests = $request->pests;
                    }
                    $Item->pesticidecost = $request->pesticidecost ;
                    $Item->laborwages = $request->laborwages;
                    $Item->fuelcost = $request->fuelcost;

                    $data = array_merge($data, [
                        "pesticidecost" => $request->pesticidecost,
                        "laborwages" => $request->laborwages,
                        "fuelcost" => $request->fuelcost
                    ]);
                    break;
                case '5':
                    if ($Item->id == $id) {
                        $Item->fertilizertype = $request->fertilizertype;
                        $Item->fertilizerquantity = $request->fertilizerquantity;
                    }

                    $Item->fertilizer = $request->fertilizer;
                    $Item->laborwages = $request->laborwages ;
                    $Item->fuelcost = $request->fuelcost ;

                    $data = array_merge($data, [
                        "fertilizer" => $request->fertilizer,
                        "laborwages" => $request->laborwages ,
                        "fuelcost" => $request->fuelcost 
                    ]);
                    break;
                case '6':
                    if ($Item->id == $id) {
                        $Item->cuttingtype = $request->cuttingtype;
                        $Item->sugarcanetype = $request->sugarcanetype;
                    }

                    $Item->sugarcanecuttinglabor = $request->sugarcanecuttinglabor ;
                    $Item->fuelcost = $request->fuelcost;

                    $data = array_merge($data, [
                        "sugarcanecuttinglabor" => $request->sugarcanecuttinglabor ,
                        "fuelcost" => $request->fuelcost
                    ]);
                    break;
                case '7':
                    $Item->laborwages = $request->laborwages ;
                    $Item->fuelcost = $request->fuelcost ;

                    $data = array_merge($data, [
                        "laborwages" => $request->laborwages ,
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
                $deductTypeId = $this->getDeductTypeId($key);

                $deduct = DeductPaid::where('factory_activity_id', $Item->id)
                    ->where('deduct_type_id', $deductTypeId)
                    ->first();

                if ($deduct) {
                    $deduct->paid = $value ?? 0;
                    $deduct->month = $date->month;
                    $deduct->year = $date->year;
                    $deduct->save();
                } else {
                    $deduct = new DeductPaid();
                    $deduct->frammer_id = $Item->frammer_id;
                    $deduct->factory_activity_id = $Item->id;
                    $deduct->deduct_type_id = $deductTypeId;
                    $deduct->paid = $value ?? 0;
                    $deduct->month = $date->month;
                    $deduct->year = $date->year;
                    $deduct->save();
                }
            }
            $sumareaitem = $items->sum('areasize') ?? 0;

            $newratio = $sumareaitem / $Item->areasize;
            $afterupdate = [
                'insecticidecost' =>  round($Item->insecticidecost * $newratio, 2),
                'equipmentrent' => round($Item->equipmentrent * $newratio, 2),
                'fertilizercost' => round($Item->fertilizercost * $newratio, 2),
                'pesticidecost' => round($Item->pesticidecost * $newratio, 2),
                'sugartypecost' => round($Item->sugartypecost * $newratio, 2),
                'sugarcaneplantingcost' => round($Item->sugarcaneplantingcost * $newratio, 2),
                'amountureafertilizer' => round($Item->amountureafertilizer * $newratio, 2),
                'otheringredientcosts' => round($Item->otheringredientcosts * $newratio, 2),
                'herbicidecost' => round($Item->herbicidecost * $newratio, 2),
                'sugarcanecuttinglabor' => round($Item->sugarcanecuttinglabor * $newratio, 2),
                'laborwages' => round($Item->laborwages * $newratio, 2),
                'fuelcost' => round($Item->fuelcost * $newratio, 2),
            ];
            $total_area_size = collect($request->areasize)->sum() ?? 0;
            // dd($total_area_size);
            $areaSizes = $request->areasize;

            $updatedata = $items->filter(function ($item) use ($id) {
                return $item->id !== (int)$id;
            });

            // return $updatedata;
            $data = [];
            foreach ($updatedata as $index => $rest) {
                $date = Carbon::parse($rest->selectdate);
                $areaSize = $areaSizes[$index] ?? 0;
                $areaRatio = round($areaSize / $total_area_size, 2);

                switch ($request->activitytype) {
                    case '0':
                        if ($rest->id == $id) {
                            $rest->soilImprovement = $request->soil_improvement;
                            $rest->plowingtype = $request->plowingtype;
                            $rest->subtypeplowing = $request->subtypeplowing;
                        }

                        $rest->insecticidecost = round($afterupdate['insecticidecost'] * $areaRatio, 2);
                        $rest->equipmentrent = round($afterupdate['equipmentrent'] * $areaRatio, 2);
                        $rest->laborwages = round($afterupdate['laborwages'] * $areaRatio, 2);
                        $rest->fuelcost = round($afterupdate['fuelcost'] * $areaRatio, 2);

                        $data = array_merge($data, [
                            "insecticidecost" => round($afterupdate['insecticidecost'] * $areaRatio, 2),
                            "equipmentrent" => round($afterupdate['equipmentrent'] * $areaRatio, 2),
                            "laborwages" => round($afterupdate['laborwages'] * $areaRatio, 2),
                            "fuelcost" => round($afterupdate['fuelcost'] * $areaRatio, 2)
                        ]);

                        break;
                    case '1':
                        if ($rest->id == $id) {
                            $rest->sugarcane = $request->sugarcane;
                            $rest->plantingsystem = $request->plantingsystem;
                            $rest->fertilizer = $request->fertilizer;
                            $rest->expenses = $request->expenses;
                        }

                        $rest->sugartypecost = round($afterupdate['sugartypecost'] * $areaRatio, 2);
                        $rest->sugarcaneplantingcost = $afterupdate['sugarcaneplantingcost'] * $areaRatio;
                        $rest->fertilizercost = $afterupdate['fertilizercost'] * $areaRatio;
                        $rest->fuelcost = $afterupdate['fuelcost'] * $areaRatio;

                        $data = array_merge($data, [
                            "sugartypecost" => round($afterupdate['sugartypecost'] * $areaRatio, 2),
                            "sugarcaneplantingcost" => $afterupdate['sugarcaneplantingcost'] * $areaRatio,
                            "fertilizercost" => $afterupdate['fertilizercost'] * $areaRatio,
                            "fuelcost" => $afterupdate['fuelcost'] * $areaRatio
                        ]);

                        break;
                    case '2':
                        if ($rest->id == $id) {
                            $rest->wateringsystem = $request->wateringsystem;
                        }

                        $rest->laborwages = round($afterupdate['laborwages'] * $areaRatio, 2);
                        $rest->fuelcost = round($afterupdate['fuelcost'] * $areaRatio, 2);

                        $data = array_merge($data, [
                            "laborwages" => round($afterupdate['laborwages'] * $areaRatio, 2),
                            "fuelcost" => round($afterupdate['fuelcost']* $areaRatio, 2)
                        ]);
                        break;
                    case '3':
                        if ($rest->id == $id) {
                            $rest->fertilizerquantity = $request->fertilizerquantity;
                            $rest->otheringredients = $request->otheringredients;
                            $rest->amountureafertilizer = $request->amountureafertilizer;
                            $rest->herbicide = $request->herbicide;
                            $rest->othertypes = $request->othertypes;
                        }

                        $rest->otheringredientcosts = round($afterupdate['otheringredientcosts'] * $areaRatio, 2);
                        $rest->herbicidecost = round($afterupdate['herbicidecost'] * $areaRatio, 2);
                        // $rest->fertilizer = round($afterupdate['fertilizer'] * $areaRatio, 2);
                        $rest->laborwages = round($afterupdate['laborwages'] * $areaRatio, 2);
                        $rest->fuelcost = round($afterupdate['fuelcost'] * $areaRatio, 2);

                        $data = array_merge($data, [
                            "amountureafertilizer" => round($afterupdate['amountureafertilizer'] * $areaRatio, 2),
                            "otheringredientcosts" => round($afterupdate['otheringredientcosts'] * $areaRatio, 2),
                            "herbicidecost" => round($afterupdate['herbicidecost'] * $areaRatio, 2),
                            // "fertilizer" => round($afterupdate['fertilizer'] * $areaRatio, 2),
                            "laborwages" => round($afterupdate['laborwages'] * $areaRatio, 2),
                            "fuelcost" => round($afterupdate['fuelcost'] * $areaRatio, 2)
                        ]);
                        break;
                    case '4':
                        if ($rest->id == $id) {
                            $rest->weed = $request->weed;
                            $rest->plantdiseases = $request->plantdiseases;
                            $rest->pests = $request->pests;
                        }
                        $rest->pesticidecost = round($afterupdate['pesticidecost'] * $areaRatio, 2);
                        $rest->laborwages = round($afterupdate['laborwages'] * $areaRatio, 2);
                        $rest->fuelcost = round($afterupdate['fuelcost'] * $areaRatio, 2);

                        $data = array_merge($data, [
                            "pesticidecost" => round($afterupdate['pesticidecost'] * $areaRatio, 2),
                            "laborwages" => round($afterupdate['laborwages'] * $areaRatio, 2),
                            "fuelcost" => round($afterupdate['fuelcost'] * $areaRatio, 2)
                        ]);
                        break;
                    case '5':
                        if ($rest->id == $id) {
                            $rest->fertilizertype = $request->fertilizertype;
                            $rest->fertilizerquantity = $request->fertilizerquantity;
                        }

                        // $rest->fertilizer = round($request->fertilizer * $areaRatio, 2);
                        $rest->laborwages = round($afterupdate['laborwages'] * $areaRatio, 2);
                        $rest->fuelcost = round($afterupdate['fuelcost'] * $areaRatio, 2);

                        $data = array_merge($data, [
                            // "fertilizer" => round($request->fertilizer * $areaRatio, 2),
                            "laborwages" => round($afterupdate['laborwages'] * $areaRatio, 2),
                            "fuelcost" => round($afterupdate['fuelcost'] * $areaRatio, 2)
                        ]);
                        break;
                    case '6':
                        if ($rest->id == $id) {
                            $rest->cuttingtype = $request->cuttingtype;
                            $rest->sugarcanetype = $request->sugarcanetype;
                        }

                        $rest->sugarcanecuttinglabor = round($afterupdate['sugarcanecuttinglabor'] * $areaRatio, 2);
                        $rest->fuelcost = round($afterupdate['fuelcost']* $areaRatio, 2);

                        $data = array_merge($data, [
                            "sugarcanecuttinglabor" => round($afterupdate['sugarcanecuttinglabor'] * $areaRatio, 2),
                            "fuelcost" => round($afterupdate['fuelcost']* $areaRatio, 2)
                        ]);
                        break;
                    case '7':
                        $rest->laborwages = round($afterupdate['laborwages'] * $areaRatio, 2);
                        $rest->fuelcost = round($afterupdate['fuelcost'] * $areaRatio);

                        $data = array_merge($data, [
                            "laborwages" => round($afterupdate['laborwages']  * $areaRatio),
                            "fuelcost" => round($afterupdate['fuelcost']  * $areaRatio)
                        ]);
                        break;
                    default:
                        DB::rollBack();
                        return $this->returnErrorData("เกิดข้อผิดพลาด", 422);
                        break;
                }

                $rest->save();

                foreach ($data as $key => $value) {
                    $deductTypeId = $this->getDeductTypeId($key);

                    $deduct = DeductPaid::where('factory_activity_id', $rest->id)
                        ->where('deduct_type_id', $deductTypeId)
                        ->first();

                    if ($deduct) {
                        $deduct->paid = $value;
                        $deduct->month = $date->month;
                        $deduct->year = $date->year;
                        $deduct->save();
                    } else {
                        $deduct = new DeductPaid();
                        $deduct->frammer_id = $rest->frammer_id;
                        $deduct->factory_activity_id = $rest->id;
                        $deduct->deduct_type_id = $deductTypeId;
                        $deduct->paid = $value ?? 0;
                        $deduct->month = $date->month;
                        $deduct->year = $date->year;
                        $deduct->save();
                    }
                }
            }
            //

            $this->reorderNo($Item->frammer_id, $Item->sugartype, $Item->activitytype, $Item->plotsugar_id);
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

    private function getDeductTypeId($key)
    {
        switch ($key) {
            case 'fuelcost':
                return 1;
            case 'laborwages':
                return 2;
            case 'fertilizer':
                return 3;
            case 'fertilizercost':
                return 3;
            case 'pesticidecost':
                return 4;
            case 'equipmentrent':
                return 5;
            case 'sugartypecost':
                return 6;
            case 'sugarcaneplantingcost':
                return 7;
            case 'amountureafertilizer':
                return 8;
            case 'otheringredientcosts':
                return 9;
            case 'herbicidecost':
                return 10;
            case 'insecticidecost':
                return 11;
            case 'sugarcanecuttinglabor':
                return 12;
            default:
                return 1;
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
            $deductItem = DeductPaid::where('factory_activity_id', $Item->id)->get();
            foreach ($deductItem as $key => $value) {
                $value->delete();
            }
            // return $deductItem;
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

    public function newdestroy(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $item = FactoryActivity::find($request->id);
    
            if (empty($item->trans_id)) {
                return $this->returnErrorData('ไม่สามารถลบได้', 404);
            }
    
            $fullTrans = FactoryActivity::where('trans_id', $item->trans_id)->get();
    
            // Calculate the sum of costs
            $costSums = [
                'insecticidecost' => $fullTrans->sum('insecticidecost') ?? 0,
                'equipmentrent' => $fullTrans->sum('equipmentrent') ?? 0,
                'fertilizercost' => $fullTrans->sum('fertilizercost') ?? 0,
                'pesticidecost' => $fullTrans->sum('pesticidecost') ?? 0,
                'sugartypecost' => $fullTrans->sum('sugartypecost') ?? 0,
                'sugarcaneplantingcost' => $fullTrans->sum('sugarcaneplantingcost') ?? 0,
                'amountureafertilizer' => $fullTrans->sum('amountureafertilizer') ?? 0,
                'otheringredientcosts' => $fullTrans->sum('otheringredientcosts') ?? 0,
                'herbicidecost' => $fullTrans->sum('herbicidecost') ?? 0,
                'sugarcanecuttinglabor' => $fullTrans->sum('sugarcanecuttinglabor') ?? 0,
                'laborwages' => $fullTrans->sum('laborwages') ?? 0,
                'fuelcost' => $fullTrans->sum('fuelcost') ?? 0,
            ];
    
            DeductPaid::where('factory_activity_id', $item->id)->delete();
            $transId = $item->trans_id;
            $item->delete();
    
            $remainingItems = FactoryActivity::where('trans_id', $transId)->get();
    
            if ($remainingItems->isNotEmpty()) {
                $totalAreaSize = $remainingItems->sum('areasize');
                $data = [];
                foreach ($remainingItems as $remainingItem) {
                    $areaRatio = $totalAreaSize > 0 ? round($remainingItem->areasize / $totalAreaSize, 2) : 0;
                    $date = Carbon::parse($remainingItem->selectdate);
                    switch ($remainingItem->activitytype) {
                        case '0':
                            $remainingItem->insecticidecost = round($costSums['insecticidecost'] * $areaRatio, 2);
                            $remainingItem->equipmentrent = round($costSums['equipmentrent'] * $areaRatio, 2);
                            $remainingItem->laborwages = round($costSums['laborwages'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "insecticidecost" => round($costSums['insecticidecost'] * $areaRatio, 2),
                                "equipmentrent" => round($costSums['equipmentrent'] * $areaRatio, 2),
                                "laborwages" => round($costSums['laborwages'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2),
                            ]);
                            break;
                        case '1':
                            $remainingItem->sugartypecost = round($costSums['sugartypecost'] * $areaRatio, 2);
                            $remainingItem->sugarcaneplantingcost = round($costSums['sugarcaneplantingcost'] * $areaRatio, 2);
                            $remainingItem->fertilizercost = round($costSums['fertilizercost'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "sugartypecost" => round($costSums['sugartypecost'] * $areaRatio, 2),
                                "sugarcaneplantingcost" => round($costSums['sugarcaneplantingcost'] * $areaRatio, 2),
                                "fertilizercost" => round($costSums['fertilizercost'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2)
                            ]);
                            break;
                        case '2':
                            $remainingItem->laborwages = round($costSums['laborwages'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "laborwages" => round($costSums['laborwages'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2),
                            ]);
                            break;
                        case '3':
                            $remainingItem->amountureafertilizer = round($costSums['amountureafertilizer'] * $areaRatio, 2);
                            $remainingItem->otheringredientcosts = round($costSums['otheringredientcosts'] * $areaRatio, 2);
                            $remainingItem->herbicidecost = round($costSums['herbicidecost'] * $areaRatio, 2);
                            $remainingItem->laborwages = round($costSums['laborwages'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "amountureafertilizer" => round($costSums['amountureafertilizer'] * $areaRatio, 2),
                                "otheringredientcosts" => round($costSums['otheringredientcosts'] * $areaRatio, 2),
                                "herbicidecost" => round($costSums['herbicidecost'] * $areaRatio, 2),
                                "laborwages" => round($costSums['laborwages'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2),
                            ]);
                            break;
                        case '4':
                            $remainingItem->pesticidecost = round($costSums['pesticidecost'] * $areaRatio, 2);
                            $remainingItem->laborwages = round($costSums['laborwages'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "pesticidecost" => round($costSums['pesticidecost'] * $areaRatio, 2),
                                "laborwages" => round($costSums['laborwages'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2),
                            ]);
                            break;
                        case '5':
                            $remainingItem->laborwages = round($costSums['laborwages'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "laborwages" => round($costSums['laborwages'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2),
                            ]);
                            break;
                        case '6':
                            $remainingItem->sugarcanecuttinglabor = round($costSums['sugarcanecuttinglabor'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "sugarcanecuttinglabor" => round($costSums['sugarcanecuttinglabor'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2),
                            ]);
                            break;
                        case '7':
                            $remainingItem->laborwages = round($costSums['laborwages'] * $areaRatio, 2);
                            $remainingItem->fuelcost = round($costSums['fuelcost'] * $areaRatio, 2);
                            $data = array_merge($data, [
                                "laborwages" => round($costSums['laborwages'] * $areaRatio, 2),
                                "fuelcost" => round($costSums['fuelcost'] * $areaRatio, 2),
                            ]);
                            break;
                        default:
                            DB::rollBack();
                            return $this->returnErrorData("เกิดข้อผิดพลาด", 422);
                    }
    
                    $remainingItem->save();
                    foreach ($data as $key => $value) {
                        $deductTypeId = $this->getDeductTypeId($key);
    
                        $deduct = DeductPaid::where('factory_activity_id', $remainingItem->id)
                            ->where('deduct_type_id', $deductTypeId)
                            ->first();
    
                        if ($deduct) {
                            $deduct->paid = $value ?? 0;
                            $deduct->month = $date->month;
                            $deduct->year = $date->year;
                            $deduct->save();
                        } else {
                            $deduct = new DeductPaid();
                            $deduct->frammer_id = $remainingItem->frammer_id;
                            $deduct->factory_activity_id = $remainingItem->id;
                            $deduct->deduct_type_id = $deductTypeId;
                            $deduct->paid = $value ?? 0;
                            $deduct->month = $date->month;
                            $deduct->year = $date->year;
                            $deduct->save();
                        }
                    }
                }
    
                // Reorder remaining items
                $this->reorderNo(
                    $remainingItems->first()->frammer_id,
                    $remainingItems->first()->sugartype,
                    $remainingItems->first()->activitytype,
                    $remainingItems->first()->plotsugar_id
                );
            }
    
            // Log the operation
            $userId = "admin";
            $type = 'ลบผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            $this->Log($userId, $description, $type);
    
            DB::commit();
    
            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
    
}

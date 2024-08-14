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
                    $deduct->paid = $value;
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


            $total_area_size = collect($request->areasize)->sum() ?? 0;
            // dd($total_area_size);
            $areaSizes = $request->areasize;

            $data = [];
            foreach ($items as $index => $Item) {
                $date = Carbon::parse($Item->selectdate);
                $areaSize = $areaSizes[$index] ?? 0;
                $areaRatio = round($areaSize / $total_area_size, 2);


                $Item->selectdate = $request->selectdate ?? $Item->selectdate;
                $Item->image = $request->image ?? $Item->image;

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
                    $deductTypeId = $this->getDeductTypeId($key);

                    $deduct = DeductPaid::where('factory_activity_id', $Item->id)
                        ->where('deduct_type_id', $deductTypeId)
                        ->first();

                    if ($deduct) {
                        $deduct->paid = $value;
                        $deduct->month = $date->month;
                        $deduct->year = $date->year;
                        $deduct->save();
                    } else {
                        $deduct = new DeductPaid();
                        $deduct->frammer_id = $Item->frammer_id;
                        $deduct->factory_activity_id = $Item->id;
                        $deduct->deduct_type_id = $deductTypeId;
                        $deduct->paid = $value;
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

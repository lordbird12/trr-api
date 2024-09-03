<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\DeductPaid;
use App\Models\IncomePaid;

class DashboardController extends Controller
{

    public function groutpby_activitytype(Request $request)
    {
        $frammerId = $request->frammer_id;
        $selectedDate = $request->selectdate;
    
        $query = DeductPaid::query();
    
        if ($frammerId) {
            $query->where('frammer_id', $frammerId);
        }
    
        if ($selectedDate) {
            $query->whereDate('created_at', $selectedDate);
        }

        $result = $query->with('factory_activity')
            ->get()
            ->filter(function ($item) {
                return $item->factory_activity !== null;
            })
            ->groupBy(function ($item) {
                return $item->factory_activity ? $item->factory_activity->activitytype : 'Unknown';
            })
            ->map(function ($group, $activitytype) {
                return [
                    'activitytype' => $activitytype,
                    'total_paid' => $group->sum('paid'),
                ];
            })
            ->values();
    
        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $result);
    }

    public function groutpby_weekly(Request $request)
    {
        $frammerId = $request->frammer_id;
        date_default_timezone_set('Asia/Bangkok');

        $selectedDate = $request->has('selectdate')
            ? Carbon::parse($request->selectdate)
            : now();

        $startOfWeek = (clone $selectedDate)->startOfWeek();
        $endOfWeek = (clone $selectedDate)->endOfWeek();

        $query = DeductPaid::whereBetween('updated_at', [$startOfWeek, $endOfWeek]);
        $query2 = IncomePaid::whereBetween('updated_at', [$startOfWeek, $endOfWeek]);

        if (isset($frammerId)) {
            $query->where('frammer_id', $frammerId);
            $query2->where('frammer_id', $frammerId);
        }

        $result = $query->selectRaw('DATE(updated_at) as date, SUM(paid) as total_paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(function ($item) {
                return $item->total_paid;
            });

        $result2 = $query2->selectRaw('DATE(updated_at) as date, SUM(paid) as total_paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(function ($item) {
                return $item->total_paid;
            });

        $allDates["deduct"] = [];
        for ($date = clone $startOfWeek; $date <= $endOfWeek; $date->addDay()) {
            $dateString = $date->toDateString();
            $allDates["deduct"][] = [$dateString => $result[$dateString] ?? 0];
        }

        $allDates["income"] = [];
        for ($date = clone $startOfWeek; $date <= $endOfWeek; $date->addDay()) {
            $dateString = $date->toDateString();
            $allDates["income"][] = [$dateString => $result2[$dateString] ?? 0];
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $allDates);
    }

    public function incomededuct(Request $request)
    {
        $frammerId = $request->frammer_id;
        $selectedDate = $request->selectdate;
        $allData = [];

        $query1 = DeductPaid::query();

        if (isset($frammerId)) {
            $query1->where('frammer_id', $frammerId);
        }
        
        $query1 = $query1->selectRaw('SUM(paid) as total_paid')->get();
        

        $allData["Deduct"] = $query1->map(function ($item) {
            return $item->total_paid ?? 0;
        });

        $query2 = IncomePaid::query();

        if (isset($frammerId)) {
            $query2->where('frammer_id', $frammerId);
        }

        $query2 = $query2->selectRaw('SUM(paid) as total_paid')->get();

        $allData["Income"] = $query2->map(function ($item) {
            return $item->total_paid ?? 0;
        });

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $allData);
    }
}

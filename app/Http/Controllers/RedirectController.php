<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use App\Http\Requests\RedirectRequest as RequestsRedirectRequest;
use App\Models\RedirectLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Redirect as FacadesRedirect;

class RedirectController extends Controller
{
    public function index()
    {
        return Redirect::all();
    }
    public function store(Request $request)
    {
        $destinationUrl = $request->input('destination_url');
        $status = $request->input('status');

        $redirect = Redirect::create([
            'destination_url' => $destinationUrl,
            'status' => $status,
            'code' => '',
        ]);

        
        return response()->json($redirect, 201);
    }
    public function show($code, Request $request)
    {
        
        $redirect = Redirect::where('code', $code)->firstOrFail();

        RedirectLog::create([
            'redirect_id' => $redirect->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'referer' => $request->header('referer'),
            'query_params' => $request->query(),
            'accessed_at' => now(),
        ]);
        $redirectQueryParams = is_array($redirect->query_params) ? $redirect->query_params : [];

        $queryParams = array_merge(
            $redirectQueryParams,
            array_filter($request->query())
        );
    
        return FacadesRedirect::away($redirect->destination_url . '?' . http_build_query($queryParams));
    }
    public function logs($id)
    {
        $logs = RedirectLog::where('redirect_id', $id)->get();
    
        return response()->json($logs);
    }
    public function stats($id)
    {
        $logs = RedirectLog::where('redirect_id', $id)->get();

        if ($logs->isEmpty()) {
            return response()->json([
                'total_accesses' => 0,
                'unique_accesses' => 0,
                'top_referrers' => [],
                'accesses_last_10_days' => [],
            ]);
        }

        $totalAccesses = $logs->count();

        $uniqueAccesses = $logs->unique('ip_address')->count();

        $topReferrers = $logs->groupBy('referer')->sortByDesc(function ($group) {
            return $group->count();
        })->take(5)->keys()->toArray();

        $accessesLast10Days = $logs->groupBy(function ($log) {
            return Carbon::parse($log->accessed_at)->format('Y-m-d');
        })->sortByDesc('accessed_at')->take(10)->map(function ($group) {
            return [
                'date' => Carbon::parse($group->first()->accessed_at)->format('Y-m-d'),
                'total' => $group->count(),
                'unique' => $group->unique('ip_address')->count(),
            ];
        })->values()->toArray();

        return response()->json([
            'total_accesses' => $totalAccesses,
            'unique_accesses' => $uniqueAccesses,
            'top_referrers' => $topReferrers,
            'accesses_last_10_days' => $accessesLast10Days,
        ]);
    }
    public function update(RequestsRedirectRequest $request, $code)
    {
        $id = Hashids::decode($code)[0];
        $redirect = Redirect::findOrFail($id);
        $redirect->update($request->validated());
        return response()->json($redirect);
    }

    public function destroy($code)
    {
        $id = Hashids::decode($code)[0];
        $redirect = Redirect::findOrFail($id);
        $redirect->delete();
        return response()->json(null, 204);
    }

}

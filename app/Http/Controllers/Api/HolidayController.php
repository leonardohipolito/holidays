<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Http\Resources\HolidayResource;
use App\Http\Resources\HolidayResourceCollection;
use App\Models\Holiday;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Holiday::class);

        return new HolidayResourceCollection(Holiday::with('participants')->paginate());
    }

    public function store(StoreHolidayRequest $request)
    {
        $holiday = Holiday::create($request->validated());
        $this->syncParticipants($request, $holiday);

        return new HolidayResource($holiday);
    }

    public function show(Holiday $holiday)
    {
        Gate::authorize('view', $holiday);

        return new HolidayResource($holiday->load('participants'));
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    {
        $holiday->update($request->validated());
        $this->syncParticipants($request, $holiday);

        return new HolidayResource($holiday);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        Gate::authorize('delete', $holiday);
        $holiday->delete();

        return response()->noContent();
    }

    protected function syncParticipants(Request $request, Holiday $holiday)
    {
        $participants = collect($request->participants)
            ->map(function ($participant) {
                return Arr::only($participant, ['name']);
            })
            ->unique()
            ->map(fn ($participant) => Participant::firstOrCreate($participant))
            ->pluck('id');
        $holiday->participants()->sync($participants);
    }
}

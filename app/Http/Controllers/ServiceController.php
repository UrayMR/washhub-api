<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response as HttpResponse;


class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Service::class);

        $services = Service::all();

        return ApiResponse::success(
            'Services retrieved successfully.',
            ServiceResource::collection($services),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        Gate::authorize('create', Service::class);

        $service = Service::create($request->validated());

        return ApiResponse::success(
            'Service created.',
            new ServiceResource($service),
            HttpResponse::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        Gate::authorize('view', $service);

        return ApiResponse::success(
            'Service retrieved successfully.',
            new ServiceResource($service),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, Service $service)
    {
        Gate::authorize('update', $service);

        $service->update($request->validated());

        return ApiResponse::success(
            'Service updated.',
            new ServiceResource($service),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        Gate::authorize('delete', $service);

        $service->delete();

        return ApiResponse::success(
            'Service deleted.',
            null,
            HttpResponse::HTTP_OK
        );
    }
}

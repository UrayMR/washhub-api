<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\InvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Invoice::class);

        $invoices = Invoice::with(['order.customer', 'order.items.service'])->latest()->get();

        return ApiResponse::success(
            'Invoices retrieved successfully.',
            InvoiceResource::collection($invoices),
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
    public function store(InvoiceRequest $request)
    {
        Gate::authorize('create', Invoice::class);

        $validated = $request->validated();

        // Check if invoice for the order already exists
        if (Invoice::where('order_id', $validated['order_id'])->exists()) {
            return ApiResponse::error(
                'Invoice for this order already exists.',
                null,
                HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $order = Order::with('items.service')->findOrFail($validated['order_id']);
        $amount = $order->items->sum(function ($item) {
            return $item->quantity * $item->service->price;
        });

        // Buat invoice dalam transaksi
        $invoice = DB::transaction(function () use ($validated, $amount) {
            return Invoice::create([
                'order_id' => $validated['order_id'],
                'amount' => $amount,
                'issued_at' => $validated['issued_at'] ?? now(),
                'status' => $validated['status'],
            ]);
        });

        return ApiResponse::success(
            'Invoice created successfully.',
            new InvoiceResource(
                $invoice->load('order.customer', 'order.items.service')
            ),
            HttpResponse::HTTP_CREATED
        );
    }


    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['order.customer', 'order.items.service']);

        return ApiResponse::success(
            'Invoice retrieved.',
            new InvoiceResource($invoice)
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $invoice->update($request->validated());

        return ApiResponse::success(
            'Invoice updated.',
            new InvoiceResource($invoice->load(['order.customer', 'order.items.service']))
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        $invoice->delete();

        return ApiResponse::success(
            'Invoice deleted.',
            null,
            HttpResponse::HTTP_OK
        );
    }

    public function searchOrder(Request $request)
    {
        $request->validate(['query' => 'required|string']);

        $orders = Order::with('customer')
            ->where('order_number', 'like', '%' . $request->query('query') . '%')
            ->limit(10)
            ->get();

        return ApiResponse::success(
            'Search result.',
            $orders->map(fn($order) => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'customer'     => $order->customer->name ?? null,
            ])
        );
    }
}

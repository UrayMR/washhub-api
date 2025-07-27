<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Transaction::class);

        $transactions = Transaction::with('invoice.order.customer')->latest()->get();

        return ApiResponse::success(
            'Transactions retrieved successfully.',
            TransactionResource::collection($transactions)
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
    public function store(TransactionRequest $request)
    {
        Gate::authorize('create', Transaction::class);

        $validated = $request->validated();

        if (Transaction::where('invoice_id', $validated['invoice_id'])->exists()) {
            return ApiResponse::error(
                'Transaction for this invoice already exists.',
                null,
                422
            );
        }

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        $transaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'payment_method' => $validated['payment_method'],
            'paid_amount' => $invoice->amount,
            'paid_at' => $validated['paid_at'] ?? now(),
            'reference_number' => $validated['reference_number'] ?? null,
        ]);

        return ApiResponse::success(
            'Transaction created successfully.',
            new TransactionResource($transaction->load('invoice.order.customer')),
            201
        );
    }


    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        Gate::authorize('view', $transaction);
        $transaction->load('invoice.order.customer');
        return ApiResponse::success(
            'Transaction retrieved.',
            new TransactionResource($transaction)
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {
        Gate::authorize('update', $transaction);
        $transaction->update($request->validated());
        return ApiResponse::success(
            'Transaction updated.',
            new TransactionResource($transaction->load('invoice.order.customer'))
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return ApiResponse::success(
            'Transaction deleted.',
            null
        );
    }
}

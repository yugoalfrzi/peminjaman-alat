<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    /**
     * Membuat transaksi dan mendapatkan snap token dari Midtrans
     */
    public function process(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id'
        ]);

        $loan = Loan::with('tool')->findOrFail($request->loan_id);

        // Validasi: milik user, denda > 0, dan belum lunas
        if ($loan->user_id != auth()->id() || $loan->denda <= 0 || $loan->is_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid atau denda sudah lunas.'
            ], 422);
        }

        // Cek apakah sudah ada transaksi pending untuk loan ini
        $existingTransaction = Transaction::where('loan_id', $loan->id)
            ->whereIn('status', ['pending'])
            ->first();

        if ($existingTransaction && $existingTransaction->snap_token) {
            return response()->json([
                'success' => true,
                'snap_token' => $existingTransaction->snap_token
            ]);
        }

        // Buat order_id unik
        $order_id = 'DENDA-' . $loan->id . '-' . time();

        // Parameter untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $loan->denda,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'item_details' => [
                [
                    'id' => $loan->tool->id,
                    'price' => (int) $loan->denda,
                    'quantity' => 1,
                    'name' => 'Denda Keterlambatan - ' . $loan->tool->nama_alat,
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan transaksi ke database
            $transaction = Transaction::create([
                'loan_id' => $loan->id,
                'order_id' => $order_id,
                'snap_token' => $snapToken,
                'amount' => $loan->denda,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint notifikasi dari Midtrans (webhook)
     */
    public function notification(Request $request)
    {
        try {
            $notif = new Notification();

            $transaction = Transaction::where('order_id', $notif->order_id)->first();

            if (!$transaction) {
                Log::warning('Transaction not found: ' . $notif->order_id);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Simpan response mentah dari Midtrans
            $transaction->update([
                'midtrans_response' => json_decode(json_encode($notif), true)
            ]);

            $status = $this->translateStatus($notif->transaction_status, $notif->fraud_status ?? null);

            if ($status == 'success') {
                $transaction->update([
                    'status' => 'success',
                    'payment_method' => $notif->payment_type ?? null
                ]);

                // Update status pembayaran di tabel loans
                $loan = $transaction->loan;
                $loan->update([
                    'is_paid' => true,
                    'paid_at' => now(),
                    'payment_method' => $notif->payment_type ?? null
                ]);
            } elseif ($status == 'failure') {
                $transaction->update(['status' => 'failure']);
            } elseif ($status == 'expired') {
                $transaction->update(['status' => 'expired']);
            } else {
                $transaction->update(['status' => 'pending']);
            }

            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    private function translateStatus($transaction_status, $fraud_status = null)
    {
        if ($transaction_status == 'capture') {
            return ($fraud_status == 'accept') ? 'success' : 'failure';
        } elseif ($transaction_status == 'settlement') {
            return 'success';
        } elseif ($transaction_status == 'pending') {
            return 'pending';
        } elseif ($transaction_status == 'deny') {
            return 'failure';
        } elseif ($transaction_status == 'expire') {
            return 'expired';
        } elseif ($transaction_status == 'cancel') {
            return 'failure';
        }
        return 'unknown';
    }
}
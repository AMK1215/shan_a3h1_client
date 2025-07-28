<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User; // Assuming User model exists and has a wallet/balance
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config; // To access client's secret key
// use App\Models\ProcessedWagerCallback; // <-- You'll need to create this model and its migration for idempotency

class BalanceUpdateCallbackController extends Controller
{
    /**
     * Handle balance update callbacks from the game provider.
     * This endpoint is called by the provider site after a game transaction.
     */
    public function handleBalanceUpdate(Request $request)
    {
        Log::info('ClientSite: BalanceUpdateCallback received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        // --- 1. Validate Incoming Request ---
        // Ensure the payload structure matches what your provider site sends
        try {
            $validated = $request->validate([
                'wager_code' => 'required|string|max:255', // Unique ID for the game round
                'game_type_id' => 'nullable|integer', // Optional, if you send it
                'players' => 'required|array', // Array of player balance updates
                'players.*.player_id' => 'required|string|max:255',
                'players.*.balance' => 'required|numeric|min:0', // Player's NEW balance from provider
                'banker_balance' => 'nullable|numeric', // Banker's final balance (optional for client)
                'timestamp' => 'required|string', // ISO 8601 string
                'total_player_net' => 'nullable|numeric', // Total net change for players (optional)
                'banker_amount_change' => 'nullable|numeric', // Total net change for banker (optional)
                'signature' => 'required|string|max:255', // Signature for verification
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ClientSite: BalanceUpdateCallback validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_REQUEST_DATA',
                'message' => 'Invalid request data: ' . $e->getMessage(),
            ], 400); // HTTP 400 Bad Request
        }

        // --- 2. Idempotency Check (CRITICAL) ---
        // Prevent duplicate processing if the provider retries the callback.
        // You need a table (e.g., 'processed_wager_callbacks') to store processed wager_codes.
        // Create a migration for this table:
        // php artisan make:model ProcessedWagerCallback
        // php artisan make:migration create_processed_wager_callbacks_table
        // In the migration: $table->string('wager_code')->unique(); $table->timestamps();
        
        // Example Idempotency Check:
        // if (ProcessedWagerCallback::where('wager_code', $validated['wager_code'])->exists()) {
        //     Log::info('ClientSite: Duplicate wager_code received, skipping processing.', [
        //         'wager_code' => $validated['wager_code'],
        //     ]);
        //     return response()->json([
        //         'status' => 'success',
        //         'code' => 'ALREADY_PROCESSED',
        //         'message' => 'Wager already processed.',
        //     ], 200); // Always return 200 OK for already processed to prevent provider retries
        // }


        // --- 3. Retrieve Provider's Secret Key (on Client Site) ---
        // This is the secret key that your client site shares with the game provider.
        // It should be stored securely, e.g., in client-site/.env or config/seamless_key.php
        $providerSecretKey = Config::get('shan_key.secret_key'); // Example: from config/seamless_key.php
        // If you have multiple providers, you might need to identify the provider first
        // based on an 'operator_code' or similar field in the payload, then fetch its specific secret key.

        if (!$providerSecretKey) {
            Log::critical('ClientSite: Provider secret key not configured!');
            return response()->json([
                'status' => 'error',
                'code' => 'INTERNAL_ERROR',
                'message' => 'Provider secret key not configured on client site.',
            ], 500);
        }

        // --- 4. Verify Signature ---
        // Recreate the payload for signature verification, EXCLUDING the 'signature' field itself.
        $payloadForSignature = $request->except('signature');
        // Ensure the payload is sorted consistently before JSON encoding for HASH_HMAC
        ksort($payloadForSignature); // Sort by key
        
        $expectedSignature = hash_hmac('sha256', json_encode($payloadForSignature), $providerSecretKey);

        if (!hash_equals($expectedSignature, $validated['signature'])) { // Use hash_equals for secure comparison
            Log::warning('ClientSite: Invalid signature received', [
                'received_signature' => $validated['signature'],
                'expected_signature' => $expectedSignature,
                'payload' => $request->all(),
                'wager_code' => $validated['wager_code'],
            ]);
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_SIGNATURE',
                'message' => 'Signature verification failed.',
            ], 401); // HTTP 401 Unauthorized
        }

        // --- 5. Process Balance Updates (Transactional) ---
        try {
            DB::beginTransaction();

            foreach ($validated['players'] as $playerData) {
                $user = User::where('user_name', $playerData['player_id'])->first();

                if (!$user) {
                    Log::error('ClientSite: Player not found for balance update', [
                        'player_id' => $playerData['player_id'],
                        'wager_code' => $validated['wager_code'],
                    ]);
                    // If a player is not found, it's a critical error.
                    // You might want to rollback the whole transaction and notify.
                    throw new \RuntimeException("Player {$playerData['player_id']} not found on client site.");
                }

                // Update player's balance on the client site's database
                // This assumes your User model on the client site also uses a wallet system
                // or has a 'balance' column. Adjust this logic to fit the client's actual wallet system.
                // Example using a wallet package (like Bavix/Wallet):
                $user->wallet->updateBalance($playerData['balance']); // Set the new balance directly

                // Or if it's a simple 'balance' column:
                // $user->balance = $playerData['balance'];
                // $user->save();

                Log::info('ClientSite: Player balance updated', [
                    'player_id' => $user->user_name,
                    'old_balance' => $user->getOriginal('balance'), // If using simple balance column
                    'new_balance' => $playerData['balance'],
                    'wager_code' => $validated['wager_code'],
                ]);
            }

            // Optional: Record the processed wager_code to prevent duplicates
            // ProcessedWagerCallback::create(['wager_code' => $validated['wager_code']]);

            DB::commit(); // Commit the client-side database transaction

            Log::info('ClientSite: All balances updated successfully', [
                'wager_code' => $validated['wager_code'],
            ]);

            // --- 6. Return Success Response ---
            return response()->json([
                'status' => 'success',
                'code' => 'SUCCESS',
                'message' => 'Balances updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback client-side transaction if anything fails
            Log::error('ClientSite: Error processing balance update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'wager_code' => $request->input('wager_code'), // Use input() as $validated might not exist if it failed early
            ]);
            return response()->json([
                'status' => 'error',
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
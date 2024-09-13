<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeatureResource;
use App\Http\Resources\PackageResource;
use App\Models\Feature;
use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreditController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        $features = Feature::where('active', true)->get();
        return inertia("Credit/Index", [
            'packages' => PackageResource::collection($packages),
            'features' => FeatureResource::collection($features),
            'success' => session('success'),
            'error' => session('error'),
        ]);
    }

    public function buyCredits(Package $package)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        $checkout_session = $stripe->checkout->sessions->create([
            'line_items'=>[
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $package->name . ' - ' . 
                            $package->credits. ' credits',
                        ],
                        'unit_amount' => $package->price * 100,
                    ],
                    'quantity' => 1,
                ]
                ],
                'mode' => 'payment',
                'success_url' => route('credit.success', [], true),
                'cancel_url' => route('credit.cancel', [], true),
                ]);

                Transaction::create([
                    'status' => 'pending',
                    'price' => $package->price,
                    'credits' => $package->credits,
                    'session_id' => $checkout_session->id,
                    'user_id' => Auth::id(),
                    'package_id' => $package->id,
                ]);

                return redirect($checkout_session->url);
    }

    public function success()
    {
        return to_route('credit.index')
        ->with('success', 'You have successfully bought new credits.');
    }
    public function cancel()
    {
        return to_route('credit.index')
        ->with('error', 'An error occured please try again.');
    }
    public function webhook()
    {
        //THIS IS FOR STRIPE CLI WEBHOOKS SECRET FOR TESTING YOUR ENDPOINT LOCALLY.
        $endpoint_secret = env('STRIPE_WEBHOOK_KEY');
        // Log::info('test log');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
            //Log::info('test try section log');
        } catch (\UnexpectedValueException $e) {
            //para sa invalid payload
            return response('', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            //para sa invalid signature
            return response('', 400);
        }
        // Log::info('Stripe session object: {id}',['id' => $event->type]);
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $testpayload = 'asdfsadf';
                // Log::info('Stripe session object: {id}',['id' => $session]);
                $transaction = Transaction::where('session_id', $session->id)->first();
                //Log::info('tessttt {id}', (array) $transaction->credits);
                //I WANT TO CONSOLE LOG THIS TO SEE IF THE $TRANSACTION HAS VALUE
                if($transaction && $transaction->status === 'pending'){
                    $transaction->status = 'paid';
                    $transaction->save();
                    $transaction->user->available_credits += $transaction->credits;
                    $transaction->user->save();
                }break;
            default:
                echo 'Received unknown event type' . $event->type;
        }

        return response('');
    }
}

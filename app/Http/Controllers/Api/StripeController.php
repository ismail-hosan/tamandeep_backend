<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Payment;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;


class StripeController extends Controller
{
    public function handle(Request $request)
    {
        // Set the Stripe secret key for verification
        $stripeApiKey = config('services.stripe.secret');
        Stripe::setApiKey($stripeApiKey);
        $endpoint_secret = config('services.stripe.webhook_secret');

        $sig_header = $request->header('Stripe-Signature');
        $payload = $request->getContent();

        $event = null;

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            // Log error if signature verification fails
            \Log::error('Stripe Webhook Signature Verification Failed', [
                'error_message' => $e->getMessage(),
                'payload' => $payload,
                'signature' => $sig_header
            ]);

            // Return error response to Stripe
            return response()->json(['status' => 'error', 'message' => 'Webhook signature verification failed'], 400);
        }

        if ($event) {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $paymentIntent = $event->data->object; // Contains Stripe\PaymentIntent
                    $payment = Payment::find($paymentIntent->metadata->order_id);
                    $user = $paymentIntent->metadata->user_id;

                    if ($payment) {
                        $payment->status = 'success';  // Update the payment status to 'success'
                        $payment->save();

                        // Retrieve the order items from metadata
                        $items = json_decode($paymentIntent->metadata->items, true);  // Decode the items array stored as JSON

                        // Log the retrieved order items
                        \Log::info('Order items from metadata', ['items' => $items]);

                        // Create the Order record
                        $order = Order::create([
                            'user_id' => $payment->user_id,  // Assuming you saved the user_id in the payment model
                            'payment_id' => $payment->id,    // Use the payment ID for the order
                            // 'status' => 'completed',         // Set the order status as 'completed' or as per your flow
                        ]);

                        // Store order items in the order_items table
                        foreach ($items as $item) {
                            $product = Card::find($item['product_id']);
                            if ($product) {
                                // Create multiple order items based on the quantity
                                for ($i = 0; $i < $item['quantity']; $i++) {
                                    // Generate a unique ID for each order item
                                    $uniqueId = Str::uuid();  // Use UUID to generate a unique ID for each order item

                                    // Save each order item to the order_items table
                                    $orderItem = OrderItem::create([
                                        'order_id' => $order->id,           // Link the order item to the created order
                                        'card_id' => $product->id,
                                        'unique_code' => $uniqueId,
                                    ]);

                                    $qrCodeUrl = 'http://localhost:5173/' . $orderItem->unique_code;  // URL with the unique_code parameter
                                    $qrCode = new QrCode($qrCodeUrl);
                                    $writer = new PngWriter();
                                    $qrCodeImage = $writer->write($qrCode)->getString();

                                    // Save QR code image
                                    $qrCodeFileName = 'qr_code_' . $orderItem->id . '.png';
                                    Storage::disk('public')->put('qrcodes/' . $qrCodeFileName, $qrCodeImage);

                                    // Update the order_item with the QR code path
                                    $orderItem->qr_code = 'qrcodes/' . $qrCodeFileName;
                                    $orderItem->save();
                                }
                                $cartItems = Cart::where('user_id', $user)->with('items')->first();
                                foreach ($cartItems->items as $cartItem) {
                                    $cartItem->delete();  // Remove cart item from the cart
                                }
                            } else {
                                // Log if the product is not found
                                \Log::warning('Product not found', ['product_id' => $item['product_id']]);
                            }
                        }
                    } else {
                        // Log if the payment is not found
                        \Log::warning('Payment not found', ['order_id' => $paymentIntent->metadata->order_id]);
                    }
                    break;

                case 'checkout.session.expired':
                    $paymentIntent = $event->data->object; // Contains Stripe\PaymentIntent
                    $payment = Payment::find($paymentIntent->metadata->order_id);  // Retrieve the payment using the order ID

                    // Log payment information for expired session
                    \Log::info('Checkout session expired', ['payment_intent' => $paymentIntent, 'payment' => $payment]);

                    if ($payment) {
                        $payment->status = 'failed';  // Update the payment status to 'failed'
                        $payment->save();
                    }
                    break;

                // Add more case statements for other Stripe events as needed
                case 'customer.subscription.created':
                    dd('ok');
                    break;
                case 'customer.subscription.updated':
                    dd('ok');
                    break;
            }
        } else {
            // Log if event is null or malformed
            \Log::error('Received malformed event', ['event' => $event]);
            return response()->json(['status' => 'error', 'message' => 'Malformed event received'], 400);
        }

        // Return a success response to Stripe to acknowledge the webhook
        return response()->json(['status' => 'success']);
    }
}

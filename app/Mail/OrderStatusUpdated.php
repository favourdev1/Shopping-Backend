<?php

namespace App\Mail;

use App\Models\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;
    public $order;
    public $orderedItems;
    public $user;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order,$user,$OrderedItems, string $status)
    {
        $this->order = $order;
        $this->status = $status;
        $this->user = $user;
        // DB::enableQueryLog();
        $this->orderedItems = $OrderedItems;
        // $log = DB::getQueryLog();
        // \Log::info('Query Log:', ['queries' => $log]);;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order ' . $this->status,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $viewName = '';

        switch (strtolower($this->status)) {
            case 'shipped':
                $viewName = 'emails.orders.shipped';
                break;
            case 'delivered':
                $viewName = 'emails.orders.delivered';
                break;
            case 'pending':
                $viewName = 'emails.orders.recieved';
                break;
            case 'canceled':
                $viewName = 'emails.orders.cancelled';
                break;
        }

        return new Content(
            view: $viewName,
            with: [
                'order' => $this->order,
                'orderedItems' => $this->orderedItems,
                'user' => $this->user,
                'status' => $this->status,
                'response_email' => env('MAIL_FROM_ADDRESS', 'support@sosmart.com')
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
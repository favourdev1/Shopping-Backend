{{-- Intro Lines --}}
Your order has been shipped. Here are the details:

{{-- Order Details --}}
Order ID: {{ $order->number }}
Shipping Address: {{ $order->shipping_address }}

{{-- Order Items --}}
@if(count($orderItems) > 0)
  
            @foreach($orderItems as $item)
                <div>
                    <td>{{ $item->proudct_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->price }}</td>
                </div>
            @endforeach
       
@else
    No items in the order.
@endif


{{-- Button --}}
@component('mail::button', ['url' => 'https://example.com'])
    Track Order
@endcomponent

{{-- Outro Lines --}}
If you have any questions or need further assistance, please feel free to contact us.

Thanks,
{{ config('app.name') }}
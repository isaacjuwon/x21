<x-mail::message>
# Share Order Approved

Hi {{ $notifiable->name }},

Your **{{ $order->type->getLabel() }}** order has been **approved**.

<x-mail::panel>
**Order ID:** #{{ $order->id }}
**Type:** {{ $order->type->getLabel() }}
**Quantity:** {{ number_format($order->quantity) }} shares
**Price per Share:** {{ Number::currency($order->price_per_share) }}
**Total Amount:** {{ Number::currency($order->total_amount) }}
</x-mail::panel>

<x-mail::button :url="route('shares.index')">
View My Shares
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>

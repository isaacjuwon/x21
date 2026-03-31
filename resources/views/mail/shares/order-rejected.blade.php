<x-mail::message>
# Share Order Rejected

Hi {{ $notifiable->name }},

Your **{{ $order->type->getLabel() }}** order has been **rejected**.

<x-mail::panel>
**Order ID:** #{{ $order->id }}
**Type:** {{ $order->type->getLabel() }}
**Quantity:** {{ number_format($order->quantity) }} shares
**Total Amount:** {{ Number::currency($order->total_amount) }}
@if($order->rejection_reason)
**Reason:** {{ $order->rejection_reason }}
@endif
</x-mail::panel>

@if($order->type->value === 'buy')
Any funds held for this order have been refunded to your wallet.
@endif

<x-mail::button :url="route('shares.index')" color="red">
View My Shares
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>

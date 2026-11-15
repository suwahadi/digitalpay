<table role="presentation" class="rows" width="100%" cellpadding="0" cellspacing="0">
    @foreach($items as $item)
    <tr>
        <td class="label" style="color: #111827;">
            <span class="item-name">{{ $item['product_name'] }}</span><br>
            <span class="muted small">{{ $item['variant_name'] }}@if(($item['quantity'] ?? 1) > 1) &times; {{ $item['quantity'] }}@endif</span>
        </td>
        @if(isset($item['formatted_total']))
        <td class="value">{{ $item['formatted_total'] }}</td>
        @endif
    </tr>
    @endforeach
</table>

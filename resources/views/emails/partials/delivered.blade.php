@foreach($items as $item)
    @php($content = $item['delivery_content'] ?? $item['digital_code'] ?? null)
    <p style="margin: 0 0 2px;"><span class="item-name">{{ $item['product_name'] }}</span></p>
    <p class="muted small" style="margin: 0;">{{ $item['variant_name'] }}</p>
    @if($content && $content !== 'Akan dikirim terpisah')
    <div class="code">{{ $content }}</div>
    @else
    <p class="muted small" style="margin: 6px 0 18px;">Sedang disiapkan admin, akan dikirim terpisah.</p>
    @endif
    @if(!empty($item['instructions']))
    <p class="small" style="white-space: pre-wrap; margin: -8px 0 18px;">{{ $item['instructions'] }}</p>
    @endif
@endforeach

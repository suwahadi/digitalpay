<table role="presentation" class="rows" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 8px;">
    <tr>
        <td class="label">Subtotal</td>
        <td class="value">{{ $formatted_subtotal }}</td>
    </tr>
    @if(($discount_amount ?? 0) > 0)
    <tr>
        <td class="label">Diskon Voucher</td>
        <td class="value">-{{ $formatted_discount_amount }}</td>
    </tr>
    @endif
    @if(($fee_amount ?? 0) > 0)
    <tr>
        <td class="label">Biaya Layanan</td>
        <td class="value">{{ $formatted_fee_amount }}</td>
    </tr>
    @endif
    @if(($unique_code ?? 0) > 0)
    <tr>
        <td class="label">Kode Unik</td>
        <td class="value">{{ $formatted_unique_code }}</td>
    </tr>
    @endif
    <tr class="total">
        <td class="label" style="color: #111827;">{{ $total_label ?? 'Total' }}</td>
        <td class="value">{{ $formatted_total_amount }}</td>
    </tr>
</table>

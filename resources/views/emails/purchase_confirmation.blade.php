<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Konfirmasi Pembayaran</title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="20" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td style="text-align: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
                            <h2 style="color: #1a202c; margin: 0;">Terima Kasih atas Pesanan Anda!</h2>
                            <p style="color: #718096; margin-top: 10px;">ID Pesanan: {{ $order->id }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px;">
                            <p style="color: #4a5568; margin-bottom: 10px;">Halo {{ $order->user->name }},</p>
                            <p style="color: #4a5568;">Pembayaran Anda untuk pesanan berikut telah berhasil diverifikasi:</p>
                            
                            <table width="100%" cellpadding="10" cellspacing="0" border="0" style="margin-top: 20px; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="border-bottom: 2px solid #e2e8f0; text-align: left; color: #4a5568;">Buku</th>
                                        <th style="border-bottom: 2px solid #e2e8f0; text-align: right; color: #4a5568;">Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td style="border-bottom: 1px solid #e2e8f0; color: #4a5568;">{{ $item->book->title }}</td>
                                        <td style="border-bottom: 1px solid #e2e8f0; text-align: right; color: #4a5568;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th style="padding-top: 15px; text-align: right; color: #1a202c;">Total Pembayaran:</th>
                                        <th style="padding-top: 15px; text-align: right; color: #1a202c;">Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 30px; padding-bottom: 10px;">
                            <a href="{{ url('/my-library') }}" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px;">Buka Perpustakaan Saya</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px;">
                            <p style="color: #a0aec0; font-size: 12px; margin: 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

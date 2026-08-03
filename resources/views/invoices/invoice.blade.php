<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #FIIN-{{ $booking->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; padding: 30px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: 800; color: #2563eb; letter-spacing: -0.5px; }
        .invoice-details { text-align: right; font-size: 14px; color: #64748b; }
        .billing-table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .billing-table th { background: #f8fafc; text-align: left; padding: 12px; font-size: 12px; text-transform: uppercase; color: #475569; }
        .billing-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .total-box { margin-top: 20px; text-align: right; font-size: 18px; font-weight: 700; color: #0f172a; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">Print / Save PDF</button>
        </div>

        <div class="header">
            <div>
                <div class="logo">FIINWAY</div>
                <div style="font-size:12px; color:#64748b; margin-top:4px;">Official Service & Transaction Receipt</div>
            </div>
            <div class="invoice-details">
                <strong>INVOICE #:</strong> FIIN-{{ $booking->id }}<br>
                <strong>DATE:</strong> {{ \Carbon\Carbon::parse($booking->created_at ?? now())->format('d M Y, h:i A') }}<br>
                <strong>STATUS:</strong> <span style="color:#16a34a; font-weight:700;">PAID</span>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: space-between;">
            <div>
                <strong style="color:#475569; font-size:12px; text-transform:uppercase;">Billed To:</strong><br>
                <div style="font-size:15px; font-weight:600; margin-top:4px;">{{ $user->nom ?? '' }} {{ $user->prenom ?? 'Customer' }}</div>
                <div style="font-size:13px; color:#64748b;">Phone: {{ $user->phone ?? 'N/A' }}</div>
            </div>
            <div style="text-align:right;">
                <strong style="color:#475569; font-size:12px; text-transform:uppercase;">Service Provider:</strong><br>
                <div style="font-size:15px; font-weight:600; margin-top:4px;">Fiinway Verified Partner</div>
            </div>
        </div>

        <table class="billing-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Booking ID</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $booking->service_name ?? $booking->destination_name ?? 'Service Booking' }}</td>
                    <td>#{{ $booking->id }}</td>
                    <td style="text-align:right;">₹{{ number_format($booking->amount ?? $booking->montant ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-box">
            Total Paid: ₹{{ number_format($booking->amount ?? $booking->montant ?? 0, 2) }}
        </div>

        <div class="footer">
            Thank you for choosing Fiinway! For support or inquiries, visit https://fiinway.online
        </div>
    </div>
</body>
</html>

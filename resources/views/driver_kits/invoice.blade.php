<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1e293b; margin: 0; padding: 40px; background: #f8fafc; }
        .invoice-card { max-width: 800px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 40px; border: 1px solid #e2e8f0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 24px; margin-bottom: 24px; }
        .logo { font-size: 28px; font-weight: 800; color: #ff6b00; margin: 0; }
        .logo-tag { font-size: 13px; color: #64748b; margin: 4px 0 0; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { margin: 0; font-size: 22px; color: #0f172a; }
        .invoice-title p { margin: 4px 0 0; font-size: 13px; color: #64748b; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px; }
        .info-box h4 { margin: 0 0 8px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        .info-box p { margin: 0; font-size: 14px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f1f5f9; padding: 12px 16px; text-align: left; font-size: 13px; text-transform: uppercase; color: #475569; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
        td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .totals { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .totals-table { width: 300px; }
        .totals-table tr td:last-child { text-align: right; font-weight: bold; }
        .badge-paid { display: inline-block; padding: 4px 10px; background: #ecfdf5; color: #059669; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .footer { border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
        @media print {
            body { background: white; padding: 0; }
            .invoice-card { box-shadow: none; border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width: 800px; margin: 0 auto 20px; display: flex; justify-content: space-between;">
        <a href="javascript:history.back()" style="color: #64748b; text-decoration: none; font-size: 14px;">← Back</a>
        <button onclick="window.print()" style="background: #ff6b00; color: white; border: none; padding: 8px 18px; border-radius: 8px; font-weight: bold; cursor: pointer;">Print / Save PDF</button>
    </div>

    <div class="invoice-card">
        <div class="header">
            <div>
                <h1 class="logo">FIINWAY</h1>
                <p class="logo-tag">Drive. Serve. Grow. | Fiinway Technologies Pvt Ltd</p>
                <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">GSTIN: 07AAACF1234F1Z5 | Support: support@fiinway.com</p>
            </div>
            <div class="invoice-title">
                <h2>TAX INVOICE</h2>
                <p><strong>Invoice No:</strong> INV-{{ $order->order_number }}</p>
                <p><strong>Order No:</strong> {{ $order->order_number }}</p>
                <p><strong>Date:</strong> {{ $order->purchased_at ? $order->purchased_at->format('d M Y') : date('d M Y') }}</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h4>Billed To (Partner):</h4>
                <p><strong>{{ $order->receiver_name }}</strong> (Partner ID: #{{ $order->driver_id }})</p>
                <p>Phone: {{ $order->receiver_phone }}</p>
                <p>Shipping Address: {{ $order->shipping_address }} {{ $order->pincode ? '- ' . $order->pincode : '' }}</p>
            </div>
            <div class="info-box">
                <h4>Payment & Dispatch:</h4>
                <p>Payment Mode: <strong style="text-transform: uppercase;">{{ $order->payment_method }}</strong></p>
                <p>Payment Status: <span class="badge-paid">{{ strtoupper($order->payment_status) }}</span></p>
                <p>Courier Partner: <strong>{{ $order->courier_partner ?? 'Blue Dart Express' }}</strong></p>
                <p>AWB Tracking: <strong>{{ $order->tracking_code ?? $order->tracking_number ?? 'Pending' }}</strong></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Size / Variant</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $order->kit_title }}</strong><br>
                        <span style="font-size: 12px; color: #64748b;">Official Fiinway Partner Marketing & Safety Welcome Kit</span>
                    </td>
                    <td>{{ $order->selected_size ?? $order->tshirt_size ?? 'Free Size' }}</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">₹{{ number_format($order->amount / 1.18, 2) }}</td>
                    <td style="text-align: right;">₹{{ number_format($order->amount / 1.18, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td>Taxable Subtotal:</td>
                    <td>₹{{ number_format($order->amount / 1.18, 2) }}</td>
                </tr>
                <tr>
                    <td>IGST (18%):</td>
                    <td>₹{{ number_format($order->amount - ($order->amount / 1.18), 2) }}</td>
                </tr>
                <tr style="border-top: 2px solid #e2e8f0; font-size: 16px;">
                    <td><strong>Total Amount:</strong></td>
                    <td style="color: #ff6b00;"><strong>₹{{ number_format($order->amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>This is a computer-generated tax invoice and does not require a physical signature.</p>
            <p>Thank you for partnering with Fiinway Technologies!</p>
        </div>
    </div>
</body>
</html>

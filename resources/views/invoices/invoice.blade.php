<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Invoice {{ $invoiceNo ?? ('FIIN-' . $id) }}</title>
    <!-- html2pdf for direct PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 12px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Mobile Action Bar */
        .action-bar {
            max-width: 580px;
            margin: 0 auto 12px auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-back {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 11px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-download {
            flex: 1;
            background: #6AA720;
            color: #ffffff;
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(106, 167, 32, 0.3);
            transition: background 0.2s, transform 0.1s;
        }
        .btn-download:active {
            transform: scale(0.98);
        }

        /* Main Invoice Card */
        .invoice-card {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        /* Letterhead Top Header */
        .letterhead-top {
            background: linear-gradient(135deg, #6AA720 0%, #4D8010 100%);
            padding: 20px;
            color: #ffffff;
        }
        .letterhead-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .app-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #ffffff;
            padding: 2px;
            object-fit: contain;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .brand-text h1 {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .brand-text p {
            font-size: 11px;
            opacity: 0.9;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .invoice-badge-header {
            text-align: right;
        }
        .invoice-pill {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .invoice-num {
            font-size: 12px;
            font-weight: 700;
            margin-top: 4px;
            opacity: 0.95;
        }

        /* Invoice Body */
        .invoice-body {
            padding: 20px;
        }

        /* Amount Hero Tile */
        .amount-tile {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            margin-bottom: 18px;
        }
        .amount-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
        }
        .amount-value {
            font-size: 28px;
            font-weight: 900;
            color: #15803D;
            margin: 4px 0 6px 0;
            letter-spacing: -0.5px;
        }
        .status-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #dcfce7;
            color: #15803d;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        /* Detail List */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 14px;
            padding: 14px;
        }
        .info-item {
            font-size: 12px;
        }
        .info-label {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            margin-bottom: 3px;
        }
        .info-val {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
            word-break: break-word;
        }

        /* Transaction Particulars Table */
        .txn-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .txn-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .txn-table td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        .txn-table td:last-child {
            text-align: right;
            font-weight: 700;
        }

        /* Footer Stamp */
        .invoice-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
        }
        .seal-tag {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            color: #6AA720;
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .action-bar { display: none !important; }
            .invoice-card {
                box-shadow: none;
                border: 1px solid #cbd5e1;
                border-radius: 0;
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Screen Actions -->
    <div class="action-bar no-print">
        <a href="javascript:window.history.back()" class="btn-back">
            <span>← Back</span>
        </a>
        <button onclick="downloadPDF()" class="btn-download" id="dlBtn">
            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Download Invoice PDF</span>
        </button>
    </div>

    <!-- Printable & Downloadable Invoice Card -->
    <div class="invoice-card" id="invoice-card">
        <!-- Letterhead Header -->
        <div class="letterhead-top">
            <div class="letterhead-content">
                <div class="logo-wrap">
                    <img src="data:image/png;base64,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" alt="Fiinway" class="app-logo">
                    <div class="brand-text">
                        <h1>FIINWAY</h1>
                        <p>Smart Mobility & Services</p>
                    </div>
                </div>
                <div class="invoice-badge-header">
                    <span class="invoice-pill">Tax Invoice</span>
                    <div class="invoice-num">{{ $invoiceNo }}</div>
                </div>
            </div>
        </div>

        <div class="invoice-body">
            <!-- Amount Hero Tile -->
            <div class="amount-tile">
                <div class="amount-label">{{ $isDebit ? 'Total Paid Amount' : 'Total Credited Amount' }}</div>
                <div class="amount-value">{{ $currencySymbol }}{{ number_format($finalTotal ?? $amount, 2) }}</div>
                <div class="status-tag">✓ Payment Successful</div>
            </div>

            <!-- Details Info Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Paid From (Sender)</div>
                    <div class="info-val">{{ $paidFrom ?? $userName }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Paid To (Beneficiary)</div>
                    <div class="info-val">{{ $paidTo ?? 'Fiinway Services' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Service / Category</div>
                    <div class="info-val">{{ $title }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Channel</div>
                    <div class="info-val">{{ strtoupper($paymentMethod) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date & Time</div>
                    <div class="info-val">{{ \Carbon\Carbon::parse($date)->format('d M Y, h:i A') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Reference ID</div>
                    <div class="info-val">#{{ $id }}</div>
                </div>
            </div>

            <!-- Transaction Particulars Table -->
            <table class="txn-table">
                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong style="color:#0f172a; font-size:13.5px;">{{ $title }}</strong><br>
                            <span style="font-size:11.5px; color:#64748b;">Ref: #{{ $id }} &bull; Transaction Value</span>
                        </td>
                        <td>{{ $currencySymbol }}{{ number_format($baseAmount ?? $amount, 2) }}</td>
                    </tr>
                    @if(!empty($taxList) && count($taxList) > 0)
                        @foreach($taxList as $taxItem)
                            <tr>
                                <td style="color:#475569; font-size:12.5px;">
                                    <span>{{ $taxItem['label'] }}</span>
                                </td>
                                <td style="color:#15803D; font-size:12.5px;">+{{ $currencySymbol }}{{ number_format($taxItem['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    @elseif(($taxTotal ?? 0) > 0)
                        <tr>
                            <td style="color:#475569; font-size:12.5px;">Applicable Taxes & Charges</td>
                            <td style="color:#15803D; font-size:12.5px;">+{{ $currencySymbol }}{{ number_format($taxTotal, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td style="color:#64748b; font-size:12px;">Platform Fee / Taxes</td>
                            <td style="color:#15803D; font-size:12px;">₹0.00 (Inclusive/Exempt)</td>
                        </tr>
                    @endif
                    <tr style="background:#f8fafc; font-weight:800;">
                        <td><strong>Total Amount</strong></td>
                        <td style="color:#15803D; font-size:16px; font-weight:900;">{{ $currencySymbol }}{{ number_format($finalTotal ?? $amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="seal-tag">
                <span>●</span>
                <span>Fiinway Digital Verified Receipt</span>
            </div>
            <div>
                https://api.fiinway.com
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            var btn = document.getElementById("dlBtn");
            var originalText = btn.innerHTML;
            btn.innerHTML = "<span>Processing...</span>";

            // If running inside in-app Flutter WebView, trigger native save/share sheet
            if (window.AppBridge) {
                try {
                    window.AppBridge.postMessage(JSON.stringify({
                        action: "share",
                        text: window.location.href,
                        url: window.location.href
                    }));
                    btn.innerHTML = originalText;
                    return;
                } catch(e) {}
            }

            var element = document.getElementById("invoice-card");
            
            if (typeof html2pdf !== "undefined") {
                var opt = {
                    margin:       [6, 6, 6, 6],
                    filename:     "Fiinway_Invoice_{{ $invoiceNo ?? $id }}.pdf",
                    image:        { type: "jpeg", quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, logging: false },
                    jsPDF:        { unit: "mm", format: "a4", orientation: "portrait" }
                };

                html2pdf().set(opt).from(element).save().then(function() {
                    btn.innerHTML = originalText;
                }).catch(function(err) {
                    console.error("html2pdf error:", err);
                    btn.innerHTML = originalText;
                    window.print();
                });
            } else {
                btn.innerHTML = originalText;
                window.print();
            }
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Join Fiinway Partner Program - Claim Your Referral Rewards</title>
    <meta name="description" content="Use partner referral code {{ $referralCode }} to download Fiinway Customer and Driver apps and earn instant cashback & referral income.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#047857',
                        primaryDark: '#065f46',
                        primaryLight: '#ecfdf5',
                        accent: '#10b981',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            -webkit-tap-highlight-color: transparent;
        }
        .code-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 2px dashed #059669;
        }
        .glow-button {
            box-shadow: 0 4px 20px -2px rgba(4, 120, 87, 0.4);
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col justify-between selection:bg-emerald-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center font-extrabold text-xl shadow-xs">
                    F
                </div>
                <div>
                    <h1 class="font-extrabold text-lg text-slate-900 leading-none tracking-tight">Fiinway</h1>
                    <p class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wider mt-0.5">Partner Network</p>
                </div>
            </div>

            <a href="#download-apps" class="text-xs font-bold text-primary bg-primaryLight border border-emerald-200 px-3.5 py-2 rounded-xl hover:bg-emerald-100 transition-colors">
                Download Apps
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-xl mx-auto px-4 py-6 space-y-6 w-full pb-28">

        <!-- Invitation Hero Card -->
        <div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-800 text-white rounded-3xl p-6 shadow-md relative overflow-hidden text-center space-y-3">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-teal-400/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-900/60 border border-emerald-500/30 text-emerald-200 text-[11px] font-bold uppercase tracking-wider">
                🎁 Partner Invitation
            </div>

            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                You've Been Invited to Join Fiinway!
            </h2>
            <p class="text-xs sm:text-sm text-emerald-100 max-w-md mx-auto leading-relaxed">
                Use this verified partner referral code to unlock instant signup bonuses, wallet cashback, and lifelong referral earnings.
            </p>
        </div>

        <!-- Referral Code Hero Box -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4 text-center">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Your Exclusive Referral Code</span>
            
            <div class="code-box rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-center sm:text-left">
                    <p class="text-xs font-medium text-emerald-800">Tap to copy code</p>
                    <span id="refCodeText" class="text-3xl sm:text-4xl font-extrabold text-emerald-950 font-mono tracking-widest block select-all">
                        {{ $referralCode }}
                    </span>
                </div>

                <button onclick="copyReferralCode()" class="w-full sm:w-auto bg-primary hover:bg-primaryDark text-white font-bold text-sm px-6 py-3.5 rounded-2xl glow-button flex items-center justify-center gap-2 transition-all active:scale-95 shrink-0">
                    <svg id="copyIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <span id="copyBtnText">Copy Code</span>
                </button>
            </div>

            <p class="text-[11px] text-slate-400 font-medium">
                ✨ Apply this code during registration in the Fiinway App
            </p>
        </div>

        <!-- How to Enter Code (Step-by-Step Guide) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-5">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                    💡
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 leading-none">How to Enter Code & Claim Benefits</h3>
                    <p class="text-[11px] font-medium text-slate-500 mt-0.5">Follow these 4 simple steps to claim your reward</p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Step 1 -->
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                        1
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-xs font-bold text-slate-900">Download the Fiinway App</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Choose either the <span class="font-semibold text-slate-700">Fiinway Customer App</span> (for rides & food) or <span class="font-semibold text-slate-700">Driver Partner App</span> below and install from Google Play Store.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                        2
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-xs font-bold text-slate-900">Sign Up with Mobile Number</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Open the app and enter your 10-digit mobile number. Enter the OTP sent via SMS to verify your account.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                        3
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-xs font-bold text-slate-900">Enter Referral Code <span class="font-mono text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">{{ $referralCode }}</span></h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            On the registration screen, enter or paste <strong class="text-slate-800 font-mono">{{ $referralCode }}</strong> in the <span class="font-semibold text-slate-700">"Referral Code / Partner Code"</span> input box.
                        </p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                        4
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-xs font-bold text-slate-900">Enjoy Instant Rewards & Cashback</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Your welcome reward is instantly credited to your Fiinway wallet. You will also earn cashback on every transaction!
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Download App Cards Section -->
        <div id="download-apps" class="space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider px-1">
                Choose Your App to Download
            </h3>

            <!-- App 1: Fiinway Customer App -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm hover:border-emerald-300 transition-all space-y-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center font-black text-2xl shadow-inner shrink-0">
                        🚕
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-extrabold text-slate-900">Fiinway: Rides & Services</h4>
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">User App</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Book rides, order food & earn wallet cashback on all orders.</p>
                        <div class="flex items-center gap-2 mt-1.5 text-[11px] font-semibold text-slate-600">
                            <span class="text-amber-500 font-bold">★ 4.8</span>
                            <span>•</span>
                            <span>Instant Booking</span>
                        </div>
                    </div>
                </div>

                <a href="https://play.google.com/store/apps/details?id=com.fiinway" target="_blank" rel="noopener noreferrer" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs py-3.5 px-4 rounded-2xl flex items-center justify-center gap-2.5 transition-all active:scale-[0.99] shadow-xs">
                    <svg class="w-4 h-4 text-emerald-400 fill-current" viewBox="0 0 24 24">
                        <path d="M3.609 1.814L13.792 12 3.61 22.186a2.38 2.38 0 0 1-.61-.976V2.79c.14-.37.354-.707.61-.976zm11.238 11.241l2.428-2.428-11.83-6.83 9.402 9.258zm2.428 1.89l-2.428-2.428-9.402 9.258 11.83-6.83zm1.096-1.097l2.846-1.643a1.417 1.417 0 0 0 0-2.41l-2.846-1.644-2.146 2.849 2.146 2.848z"/>
                    </svg>
                    <span>Download Customer App on Google Play</span>
                </a>
            </div>

            <!-- App 2: Fiinway Driver & Partner App -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm hover:border-emerald-300 transition-all space-y-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-600 to-emerald-800 text-white flex items-center justify-center font-black text-2xl shadow-inner shrink-0">
                        🚗
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-extrabold text-slate-900">Fiinway Driver Partner</h4>
                            <span class="text-[10px] font-bold text-teal-800 bg-teal-100 px-2 py-0.5 rounded-full">Driver App</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Drive, earn daily payouts & build a lifelong referral income network.</p>
                        <div class="flex items-center gap-2 mt-1.5 text-[11px] font-semibold text-slate-600">
                            <span class="text-emerald-700 font-bold">0% Commission</span>
                            <span>•</span>
                            <span>Daily Payouts</span>
                        </div>
                    </div>
                </div>

                <a href="https://play.google.com/store/apps/details?id=com.fiinway.driver" target="_blank" rel="noopener noreferrer" class="w-full bg-primary hover:bg-primaryDark text-white font-bold text-xs py-3.5 px-4 rounded-2xl flex items-center justify-center gap-2.5 transition-all active:scale-[0.99] shadow-xs">
                    <svg class="w-4 h-4 text-emerald-300 fill-current" viewBox="0 0 24 24">
                        <path d="M3.609 1.814L13.792 12 3.61 22.186a2.38 2.38 0 0 1-.61-.976V2.79c.14-.37.354-.707.61-.976zm11.238 11.241l2.428-2.428-11.83-6.83 9.402 9.258zm2.428 1.89l-2.428-2.428-9.402 9.258 11.83-6.83zm1.096-1.097l2.846-1.643a1.417 1.417 0 0 0 0-2.41l-2.846-1.644-2.146 2.849 2.146 2.848z"/>
                    </svg>
                    <span>Download Driver App on Google Play</span>
                </a>
            </div>
        </div>

    </main>

    <!-- Sticky Mobile Bottom Bar -->
    <div class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 z-50 shadow-lg">
        <div class="max-w-xl mx-auto flex items-center gap-3">
            <div class="flex-1 bg-slate-100 border border-slate-200 rounded-xl px-3 py-1.5 flex items-center justify-between">
                <div>
                    <span class="text-[9px] font-bold text-slate-500 uppercase block">Code</span>
                    <span class="text-sm font-extrabold text-slate-900 font-mono tracking-wider">{{ $referralCode }}</span>
                </div>
                <button onclick="copyReferralCode()" class="text-xs font-bold text-primary hover:underline px-2 py-1">
                    Copy
                </button>
            </div>

            <a href="#download-apps" class="bg-primary hover:bg-primaryDark text-white font-bold text-xs px-5 py-3 rounded-xl shrink-0 shadow-xs active:scale-95 transition-all">
                Download Apps
            </a>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs font-bold px-5 py-3 rounded-full shadow-2xl z-50 opacity-0 pointer-events-none transition-all duration-300 transform -translate-y-4">
        Partner code copied to clipboard!
    </div>

    <script>
        const refCode = "{{ $referralCode }}";

        function copyReferralCode() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(refCode).then(showToast);
            } else {
                const tempInput = document.createElement("input");
                tempInput.value = refCode;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                showToast();
            }
        }

        function showToast() {
            const toast = document.getElementById("toast");
            const copyBtnText = document.getElementById("copyBtnText");
            if (copyBtnText) copyBtnText.innerText = "Copied!";

            toast.classList.remove("opacity-0", "pointer-events-none", "-translate-y-4");
            toast.classList.add("opacity-100", "translate-y-0");

            setTimeout(() => {
                toast.classList.add("opacity-0", "pointer-events-none", "-translate-y-4");
                toast.classList.remove("opacity-100", "translate-y-0");
                if (copyBtnText) copyBtnText.innerText = "Copy Code";
            }, 2500);
        }
    </script>
</body>
</html>
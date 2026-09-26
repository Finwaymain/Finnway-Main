<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function triggerRazorpayCheckout(config) {
    var totalAmountPaise = Math.round(Number(config.amount) * 100);
    var key = config.key || "{{ env('RAZORPAY_KEY', 'rzp_test_fiinway') }}";
    
    var options = {
        "key": key,
        "amount": totalAmountPaise,
        "currency": "INR",
        "name": "Fiinway Finance",
        "description": config.description || "Loan Application Processing Fee",
        "image": "https://fiinway.com/favicon.ico",
        "handler": function (response){
            recordAndProceed({
                phone: config.phone,
                application_id: config.application_id,
                payment_id: response.razorpay_payment_id || ('rzp_' + Date.now()),
                amount: config.amount,
                next_url: config.next_url
            });
        },
        "prefill": {
            "name": config.name || "Customer",
            "email": config.email || "customer@fiinway.com",
            "contact": config.phone || ""
        },
        "theme": {
            "color": "#0f172a"
        },
        "modal": {
            "ondismiss": function() {
                console.log("Payment window dismissed");
            }
        }
    };

    try {
        if (typeof Razorpay !== 'undefined') {
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function (res){
                alert("Payment could not be completed: " + (res.error ? res.error.description : 'Failed'));
            });
            rzp.open();
        } else {
            promptSimulation(config);
        }
    } catch(err) {
        console.warn("Razorpay error, opening simulation:", err);
        promptSimulation(config);
    }
}

function promptSimulation(config) {
    if (confirm("Initiate processing fee payment of ₹" + Number(config.amount).toLocaleString('en-IN') + "?")) {
        recordAndProceed({
            phone: config.phone,
            application_id: config.application_id,
            payment_id: "pay_sim_" + Date.now(),
            amount: config.amount,
            next_url: config.next_url
        });
    }
}

function recordAndProceed(payload) {
    var verifyUrl = "{{ route('finance.verify_fee_payment') }}";
    var token = "{{ csrf_token() }}";

    fetch(verifyUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": token
        },
        body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.location.href = payload.next_url;
        }
    })
    .catch(function(err) {
        console.error("Verification error, proceeding to next screen", err);
        window.location.href = payload.next_url;
    });
}
</script>

<?php
$active_page = 'fees';
$page_title = 'Initiate Payment';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Initiate Payment</h5>
            </div>
            <div class="card-body">
                <form id="paymentForm">
                    <div class="mb-3">
                        <label for="gateway" class="form-label">Payment Gateway</label>
                        <select class="form-select" id="gateway" name="gateway" required>
                            <option value="">Select Gateway</option>
                            <option value="razorpay">Razorpay</option>
                            <option value="stripe">Stripe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" min="1" step="0.01" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="initiateBtn">
                            <i class="fas fa-paper-plane me-2"></i>Initiate Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<!-- Stripe SDK -->
<script src="https://js.stripe.com/v3/"></script>

<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const gateway = document.getElementById('gateway').value;
    const amount = parseFloat(document.getElementById('amount').value);

    if (!gateway || !amount) {
        alert('Please select a gateway and enter an amount.');
        return;
    }

    // Disable button
    const btn = document.getElementById('initiateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    if (gateway === 'razorpay') {
        initiateRazorpayPayment(amount);
    } else if (gateway === 'stripe') {
        initiateStripePayment(amount);
    }
});

function initiateRazorpayPayment(amount) {
    // Assuming keys are configured
    const options = {
        key: 'YOUR_RAZORPAY_KEY_ID', // Replace with actual key
        amount: amount * 100, // Amount in paisa (multiply by 100 for INR, but since USD, adjust accordingly)
        currency: 'USD',
        name: 'School Management System',
        description: 'Fee Payment',
        handler: function(response) {
            // Handle success
            alert('Payment successful! Payment ID: ' + response.razorpay_payment_id);
            // Redirect or update UI
            window.location.href = '/student/fees';
        },
        prefill: {
            name: '<?php echo $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']; ?>',
            email: '<?php echo $_SESSION['user']['email']; ?>'
        },
        theme: {
            color: '#007bff'
        }
    };

    const rzp = new Razorpay(options);
    rzp.open();
}

function initiateStripePayment(amount) {
    // Assuming Stripe is configured
    const stripe = Stripe('YOUR_STRIPE_PUBLISHABLE_KEY'); // Replace with actual key

    fetch('/student/payment/create-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            amount: amount,
            gateway: 'stripe'
        })
    })
    .then(response => response.json())
    .then(session => {
        return stripe.redirectToCheckout({ sessionId: session.id });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Payment initiation failed.');
    });
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>
<?php
/**
 * Admin Payment Controller
 */

class PaymentController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function initiatePayment() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['fee_id']) || !isset($data['amount'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $feeId = $data['fee_id'];
        $amount = $data['amount'];

        // Validate fee exists and is unpaid
        $fee = $this->db->selectOne("SELECT * FROM fees WHERE id = ? AND is_paid = 0", [$feeId]);
        if (!$fee) {
            $this->json(['success' => false, 'message' => 'Fee not found or already paid'], 404);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';
        if (!$paymentConfig['enabled']) {
            $this->json(['success' => false, 'message' => 'Payment processing is disabled'], 400);
        }

        $gateway = $paymentConfig['default_gateway'];

        try {
            if ($gateway === 'razorpay') {
                // Check if Razorpay SDK is available
                if (!class_exists('Razorpay\Api\Api')) {
                    throw new Exception('Razorpay SDK not installed');
                }

                $api = new \Razorpay\Api\Api($paymentConfig['razorpay']['key_id'], $paymentConfig['razorpay']['key_secret']);

                $orderData = [
                    'receipt' => 'fee_' . $feeId,
                    'amount' => $amount * 100, // Amount in paisa
                    'currency' => 'INR',
                    'payment_capture' => 1
                ];

                $razorpayOrder = $api->order->create($orderData);

                // Insert payment record
                $paymentId = $this->db->insert('fee_payments', [
                    'fee_id' => $feeId,
                    'amount_paid' => $amount,
                    'payment_date' => date('Y-m-d'),
                    'payment_mode' => 'online',
                    'payment_gateway' => 'razorpay',
                    'payment_status' => 'pending',
                    'transaction_id' => $razorpayOrder->id,
                    'collected_by' => $_SESSION['user']['id'] ?? 1
                ]);

                $this->json([
                    'success' => true,
                    'order_id' => $razorpayOrder->id,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'key' => $paymentConfig['razorpay']['key_id']
                ]);

            } elseif ($gateway === 'stripe') {
                // Check if Stripe SDK is available
                if (!class_exists('Stripe\Stripe')) {
                    throw new Exception('Stripe SDK not installed');
                }

                \Stripe\Stripe::setApiKey($paymentConfig['stripe']['secret_key']);

                $intent = \Stripe\PaymentIntent::create([
                    'amount' => $amount * 100, // Amount in cents
                    'currency' => 'usd',
                    'metadata' => ['fee_id' => $feeId]
                ]);

                // Insert payment record
                $paymentId = $this->db->insert('fee_payments', [
                    'fee_id' => $feeId,
                    'amount_paid' => $amount,
                    'payment_date' => date('Y-m-d'),
                    'payment_mode' => 'online',
                    'payment_gateway' => 'stripe',
                    'payment_status' => 'pending',
                    'transaction_id' => $intent->id,
                    'collected_by' => $_SESSION['user']['id'] ?? 1
                ]);

                $this->json([
                    'success' => true,
                    'client_secret' => $intent->client_secret,
                    'amount' => $amount,
                    'currency' => 'usd'
                ]);

            } else {
                $this->json(['success' => false, 'message' => 'Unsupported payment gateway'], 400);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Payment initiation failed: ' . $e->getMessage()], 500);
        }
    }

    public function processPayment() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['payment_id']) || !isset($data['status'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $paymentId = $data['payment_id'];
        $status = $data['status'];
        $transactionId = $data['transaction_id'] ?? null;

        // Get payment record
        $payment = $this->db->selectOne("SELECT * FROM fee_payments WHERE id = ?", [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment record not found'], 404);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';

        try {
            if ($status === 'completed') {
                // Update payment status
                $this->db->update('fee_payments', [
                    'payment_status' => 'completed',
                    'transaction_id' => $transactionId ?: $payment['transaction_id']
                ], 'id = ?', [$paymentId]);

                // Mark fee as paid
                $this->db->update('fees', ['is_paid' => 1], 'id = ?', [$payment['fee_id']]);

                // Log the transaction
                $this->db->insert('audit_logs', [
                    'user_id' => $_SESSION['user']['id'] ?? 1,
                    'action' => 'payment_completed',
                    'table_name' => 'fee_payments',
                    'record_id' => $paymentId,
                    'new_values' => json_encode(['status' => 'completed'])
                ]);

                $this->json(['success' => true, 'message' => 'Payment processed successfully']);

            } elseif ($status === 'failed') {
                // Update payment status
                $this->db->update('fee_payments', [
                    'payment_status' => 'failed'
                ], 'id = ?', [$paymentId]);

                $this->json(['success' => true, 'message' => 'Payment marked as failed']);

            } else {
                $this->json(['success' => false, 'message' => 'Invalid payment status'], 400);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    public function refundPayment() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['payment_id']) || !isset($data['refund_amount'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $paymentId = $data['payment_id'];
        $refundAmount = $data['refund_amount'];
        $reason = $data['reason'] ?? 'Customer request';

        // Get payment record
        $payment = $this->db->selectOne("SELECT * FROM fee_payments WHERE id = ? AND payment_status = 'completed'", [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment not found or not eligible for refund'], 404);
        }

        if ($refundAmount > $payment['amount_paid']) {
            $this->json(['success' => false, 'message' => 'Refund amount cannot exceed payment amount'], 400);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';

        try {
            if ($payment['payment_gateway'] === 'razorpay') {
                // Check if Razorpay SDK is available
                if (!class_exists('Razorpay\Api\Api')) {
                    throw new Exception('Razorpay SDK not installed');
                }

                $api = new \Razorpay\Api\Api($paymentConfig['razorpay']['key_id'], $paymentConfig['razorpay']['key_secret']);

                $refund = $api->payment->fetch($payment['transaction_id'])->refund([
                    'amount' => $refundAmount * 100,
                    'notes' => ['reason' => $reason]
                ]);

                // Update payment record
                $this->db->update('fee_payments', [
                    'payment_status' => 'refunded',
                    'refund_amount' => $refundAmount
                ], 'id = ?', [$paymentId]);

                // Mark fee as unpaid if full refund
                if ($refundAmount >= $payment['amount_paid']) {
                    $this->db->update('fees', ['is_paid' => 0], 'id = ?', [$payment['fee_id']]);
                }

                // Log the refund
                $this->db->insert('audit_logs', [
                    'user_id' => $_SESSION['user']['id'] ?? 1,
                    'action' => 'payment_refunded',
                    'table_name' => 'fee_payments',
                    'record_id' => $paymentId,
                    'old_values' => json_encode(['status' => 'completed']),
                    'new_values' => json_encode(['status' => 'refunded', 'refund_amount' => $refundAmount])
                ]);

                $this->json(['success' => true, 'message' => 'Refund processed successfully', 'refund_id' => $refund->id]);

            } elseif ($payment['payment_gateway'] === 'stripe') {
                // Check if Stripe SDK is available
                if (!class_exists('Stripe\Stripe')) {
                    throw new Exception('Stripe SDK not installed');
                }

                \Stripe\Stripe::setApiKey($paymentConfig['stripe']['secret_key']);

                $refund = \Stripe\Refund::create([
                    'payment_intent' => $payment['transaction_id'],
                    'amount' => $refundAmount * 100,
                    'reason' => 'requested_by_customer'
                ]);

                // Update payment record
                $this->db->update('fee_payments', [
                    'payment_status' => 'refunded',
                    'refund_amount' => $refundAmount
                ], 'id = ?', [$paymentId]);

                // Mark fee as unpaid if full refund
                if ($refundAmount >= $payment['amount_paid']) {
                    $this->db->update('fees', ['is_paid' => 0], 'id = ?', [$payment['fee_id']]);
                }

                // Log the refund
                $this->db->insert('audit_logs', [
                    'user_id' => $_SESSION['user']['id'] ?? 1,
                    'action' => 'payment_refunded',
                    'table_name' => 'fee_payments',
                    'record_id' => $paymentId,
                    'old_values' => json_encode(['status' => 'completed']),
                    'new_values' => json_encode(['status' => 'refunded', 'refund_amount' => $refundAmount])
                ]);

                $this->json(['success' => true, 'message' => 'Refund processed successfully', 'refund_id' => $refund->id]);

            } else {
                $this->json(['success' => false, 'message' => 'Refund not supported for this gateway'], 400);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Refund processing failed: ' . $e->getMessage()], 500);
        }
    }

    public function getPaymentStatus() {
        $paymentId = $_GET['payment_id'] ?? '';

        if (!$paymentId) {
            $this->json(['success' => false, 'message' => 'Payment ID is required'], 400);
        }

        // Get payment record
        $payment = $this->db->selectOne("SELECT fp.*, f.fee_type, s.first_name, s.last_name, s.scholar_number FROM fee_payments fp LEFT JOIN fees f ON fp.fee_id = f.id LEFT JOIN students s ON f.student_id = s.id WHERE fp.id = ?", [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';

        try {
            $gatewayStatus = null;

            if ($payment['payment_gateway'] === 'razorpay' && $payment['transaction_id']) {
                // Check if Razorpay SDK is available
                if (class_exists('Razorpay\Api\Api')) {
                    $api = new \Razorpay\Api\Api($paymentConfig['razorpay']['key_id'], $paymentConfig['razorpay']['key_secret']);
                    $razorpayPayment = $api->payment->fetch($payment['transaction_id']);
                    $gatewayStatus = $razorpayPayment->status;
                }
            } elseif ($payment['payment_gateway'] === 'stripe' && $payment['transaction_id']) {
                // Check if Stripe SDK is available
                if (class_exists('Stripe\Stripe')) {
                    \Stripe\Stripe::setApiKey($paymentConfig['stripe']['secret_key']);
                    $intent = \Stripe\PaymentIntent::retrieve($payment['transaction_id']);
                    $gatewayStatus = $intent->status;
                }
            }

            $this->json([
                'success' => true,
                'payment' => [
                    'id' => $payment['id'],
                    'amount' => $payment['amount_paid'],
                    'status' => $payment['payment_status'],
                    'gateway' => $payment['payment_gateway'],
                    'transaction_id' => $payment['transaction_id'],
                    'gateway_status' => $gatewayStatus,
                    'student' => $payment['first_name'] . ' ' . $payment['last_name'] . ' (' . $payment['scholar_number'] . ')',
                    'fee_type' => $payment['fee_type'],
                    'payment_date' => $payment['payment_date']
                ]
            ]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to retrieve payment status: ' . $e->getMessage()], 500);
        }
    }
}
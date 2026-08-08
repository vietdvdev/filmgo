<?php

namespace App\Services;

class PaymentService
{
    /**
     * Tạo URL thanh toán VNPay
     */
    public function createVnPayUrl($bookingCode, $totalAmount, $bankCode = null)
    {
        // WARN-01 FIX: Dùng config() thay vì env() để đảm bảo hoạt động
        // sau khi chạy `php artisan config:cache` trên production.
        $vnp_Url        = $this->getVnPayUrl();
        $vnp_Returnurl  = $this->getVnPayReturnUrl();
        $vnp_TmnCode    = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');

        $vnp_TxnRef    = $bookingCode;
        $vnp_OrderInfo = "Thanh toan ve xem phim FilmGo - Ma HD: " . $bookingCode;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount    = $totalAmount * 100; // VNPay yêu cầu nhân 100 lần số tiền thực tế
        $vnp_Locale    = 'vi';
        $vnp_IpAddr    = request()->ip();

        $inputData = [
            "vnp_Version"   => "2.1.0",
            "vnp_TmnCode"   => $vnp_TmnCode,
            "vnp_Amount"    => $vnp_Amount,
            "vnp_Command"   => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"  => "VND",
            "vnp_IpAddr"    => $vnp_IpAddr,
            "vnp_Locale"    => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef"    => $vnp_TxnRef,
        ];

        if (!empty($bankCode)) {
            $inputData['vnp_BankCode'] = $bankCode;
        }

        // Sắp xếp dữ liệu theo alphabet (Bắt buộc đối với VNPay)
        ksort($inputData);

        $query    = "";
        $i        = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Loại bỏ dấu & ở cuối query string
        $query    = rtrim($query, '&');
        $vnp_Url  = $vnp_Url . "?" . $query;

        if (isset($vnp_HashSecret)) {
            // Sử dụng trực tiếp chuỗi $hashdata chuẩn để băm SHA512
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    protected function getVnPayUrl()
    {
        $mode = config('services.vnpay.env', 'sandbox');
        if ($mode === 'production') {
            return config('services.vnpay.production_url');
        }
        return config('services.vnpay.sandbox_url');
    }

    protected function getVnPayReturnUrl()
    {
        $mode = config('services.vnpay.env', 'sandbox');
        if ($mode === 'production') {
            return config('services.vnpay.production_return_url');
        }
        return config('services.vnpay.sandbox_return_url');
    }

    /**
     * Tạo URL thanh toán MoMo (Phương thức Capture Wallet)
     */
    public function createMoMoUrl($bookingCode, $amount)
    {
        // 1. WARN-01 FIX: Lấy cấu hình từ config() thay vì env() trực tiếp
        $partnerCode = config('services.momo.partner_code');
        $accessKey   = config('services.momo.access_key');
        $secretKey   = config('services.momo.secret_key');
        $endpoint    = $this->getMoMoEndpoint();
        $redirectUrl = $this->getMoMoRedirectUrl();
        $ipnUrl      = $this->getMoMoIpnUrl();

        // Ép kiểu số tiền về dạng Integer thuần để đồng nhất dữ liệu
        $amount = (int)$amount;

        // 2. Định dạng lại chuỗi Order ID & Request ID sạch sẽ
        // Chỉ giữ ký tự phù hợp với regex của MoMo để tránh lỗi format request
        $cleanBookingCode = preg_replace('/[^A-Za-z0-9-]/', '', (string) $bookingCode);
        $cleanBookingCode = trim($cleanBookingCode, '-');
        $orderId   = ($cleanBookingCode ?: 'filmgo') . '-' . time();
        $requestId = $orderId;

        // orderInfo nên dùng ASCII để tránh lỗi ký tự đặc biệt
        $orderInfo   = 'FilmGo-booking-' . ($cleanBookingCode ?: 'order');
        $requestType = config('services.momo.request_type', 'captureWallet');
        $extraData   = "";

        // 3. Xây dựng chuỗi Raw Hash theo ĐÚNG THỨ TỰ quy định của MoMo v2
        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $ipnUrl .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $partnerCode .
            "&redirectUrl=" . $redirectUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        // 4. Tạo chữ ký điện tử
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        // 5. Gom mảng dữ liệu gửi đi sang MoMo (Giữ nguyên định dạng dữ liệu giống hệt chuỗi hash)
        $data = [
            'partnerCode' => $partnerCode,
            'accessKey'   => $accessKey,
            'requestId'   => $requestId,
            'amount'      => $amount, // Đồng nhất kiểu int với chuỗi hash ở trên
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature,
            'lang'        => 'vi',
        ];

        // 6. Thực hiện bắn cURL sang MoMo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

        // Bỏ qua xác thực SSL khi chạy môi trường local
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);

        if ($result === false) {
            $curlError = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL Error: " . $curlError);
        }

        curl_close($ch);
        $response = json_decode($result, true);

        // Kiểm tra kết quả trả về từ MoMo
        if (isset($response['resultCode']) && $response['resultCode'] == 0) {
            return $response['payUrl']; // Link dẫn tới trang quét mã QR MoMo
        }

        // Nếu lỗi, ném ra thông báo chi tiết của MoMo
        $errorMessage = $response['message'] ?? 'Unknown Error';
        $rawResponse  = is_string($result) ? $result : json_encode($result);
        throw new \Exception("Momo Gateway Error: " . $errorMessage . " (Code: " . ($response['resultCode'] ?? 'None') . "). Raw: " . $rawResponse);
    }

    protected function getMoMoEndpoint()
    {
        $mode = config('services.momo.env', 'sandbox');
        if ($mode === 'production') {
            return config('services.momo.production_endpoint');
        }
        return config('services.momo.sandbox_endpoint');
    }

    protected function getMoMoRedirectUrl()
    {
        $mode = config('services.momo.env', 'sandbox');
        if ($mode === 'production') {
            return config('services.momo.production_redirect_url');
        }
        return config('services.momo.sandbox_redirect_url');
    }

    protected function getMoMoIpnUrl()
    {
        $mode = config('services.momo.env', 'sandbox');
        if ($mode === 'production') {
            return config('services.momo.production_ipn_url');
        }
        return config('services.momo.sandbox_ipn_url');
    }
}

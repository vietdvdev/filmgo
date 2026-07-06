<?php

namespace App\Services;

class PaymentService
{
    /**
     * Tạo URL thanh toán VNPay
     */
    public function createVnPayUrl($bookingCode, $totalAmount, $bankCode = null)
    {
        $vnp_Url = $this->getVnPayUrl();
        $vnp_Returnurl = $this->getVnPayReturnUrl();
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');

        $vnp_TxnRef = $bookingCode; 
        $vnp_OrderInfo = "Thanh toan ve xem phim FilmGo - Ma HD: " . $bookingCode;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $totalAmount * 100; // VNPay yêu cầu nhân 100 lần số tiền thực tế
        $vnp_Locale = 'vi';
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_SecureHashType" => "SHA512",
        ];

        if (!empty($bankCode)) {
            $inputData['vnp_BankCode'] = $bankCode;
        }

        // Sắp xếp dữ liệu theo alphabet (Bắt buộc đối với VNPay)
        ksort($inputData);
        
        $query = "";
        $i = 0;
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
        $query = rtrim($query, '&');
        
        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            // Sử dụng trực tiếp chuỗi $hashdata chuẩn để băm SHA512
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    protected function getVnPayUrl()
    {
        $mode = env('VNP_ENV', 'sandbox');
        if ($mode === 'production') {
            return env('VNP_PRODUCTION_URL', env('VNP_URL'));
        }
        return env('VNP_SANDBOX_URL', env('VNP_URL'));
    }

    protected function getVnPayReturnUrl()
    {
        $mode = env('VNP_ENV', 'sandbox');
        if ($mode === 'production') {
            return env('VNP_PRODUCTION_RETURN_URL', env('VNP_RETURN_URL'));
        }
        return env('VNP_SANDBOX_RETURN_URL', env('VNP_RETURN_URL'));
    }

    /**
     * Tạo URL thanh toán MoMo (Phương thức Capture Wallet)
     */
    /**
     * Tạo URL thanh toán MoMo (Phương thức Capture Wallet)
     */
    public function createMoMoUrl($bookingCode, $amount)
    {
        // 1. Lấy cấu hình từ config/services hoặc trực tiếp từ env
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $endpoint = $this->getMoMoEndpoint();
        $redirectUrl = $this->getMoMoRedirectUrl();
        $ipnUrl = $this->getMoMoIpnUrl();

        // Ép kiểu số tiền về dạng Integer thuần để đồng nhất dữ liệu
        $amount = (int)$amount;

        // 2. Định dạng lại chuỗi Order ID & Request ID sạch sẽ
        // Chỉ giữ ký tự phù hợp với regex của MoMo để tránh lỗi format request
        $cleanBookingCode = preg_replace('/[^A-Za-z0-9-]/', '', (string) $bookingCode);
        $cleanBookingCode = trim($cleanBookingCode, '-');
        $orderId = ($cleanBookingCode ?: 'filmgo') . '-' . time();
        $requestId = $orderId;

        // orderInfo nên dùng ASCII để tránh lỗi ký tự đặc biệt
        $orderInfo = 'FilmGo-booking-' . ($cleanBookingCode ?: 'order');
        $requestType = env('MOMO_REQUEST_TYPE', 'captureWallet');
        $extraData = "";

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
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount, // Đồng nhất kiểu int với chuỗi hash ở trên
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
            'lang' => 'vi'
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
        $rawResponse = is_string($result) ? $result : json_encode($result);
        throw new \Exception("Momo Gateway Error: " . $errorMessage . " (Code: " . ($response['resultCode'] ?? 'None') . "). Raw: " . $rawResponse);
    }

    protected function getMoMoEndpoint()
    {
        $mode = env('MOMO_ENV', 'sandbox');
        if ($mode === 'production') {
            return env('MOMO_PRODUCTION_API_ENDPOINT', env('MOMO_API_ENDPOINT'));
        }

        return env('MOMO_SANDBOX_API_ENDPOINT', env('MOMO_API_ENDPOINT'));
    }

    protected function getMoMoRedirectUrl()
    {
        $mode = env('MOMO_ENV', 'sandbox');
        if ($mode === 'production') {
            return env('MOMO_PRODUCTION_REDIRECT_URL', env('MOMO_REDIRECT_URL'));
        }

        return env('MOMO_SANDBOX_REDIRECT_URL', env('MOMO_REDIRECT_URL'));
    }

    protected function getMoMoIpnUrl()
    {
        $mode = env('MOMO_ENV', 'sandbox');
        if ($mode === 'production') {
            return env('MOMO_PRODUCTION_IPN_URL', env('MOMO_IPN_URL'));
        }

        return env('MOMO_SANDBOX_IPN_URL', env('MOMO_IPN_URL'));
    }
}

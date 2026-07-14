<?php

namespace App\Services;

use chillerlan\phpqrcode\QRCode;
use chillerlan\phpqrcode\QROptions;
use Illuminate\Support\Str;
use RuntimeException;

class TicketQrCodeService
{
    /**
     * Khóa bí mật dùng để ký và giải mã dữ liệu vé.
     * Nên được đặt trong biến môi trường và không commit vào mã nguồn.
     */
    private string $secretKey;

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? env('TICKET_QR_SECRET', Str::random(32));
    }

    /**
     * Mã hóa payload vé thành chuỗi an toàn để nhúng vào QR.
     * Dữ liệu được mã hóa bằng AES-256-CBC rồi base64 để có thể lưu/truyền dễ dàng.
     */
    public function encryptPayload(array $payload): string
    {
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $iv = random_bytes(16);
        $cipherText = openssl_encrypt(
            $payloadJson,
            'AES-256-CBC',
            hash('sha256', $this->secretKey, true),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($cipherText === false) {
            throw new RuntimeException('Không thể mã hóa payload vé.');
        }

        return base64_encode($iv . $cipherText);
    }

    /**
     * Giải mã payload vé từ chuỗi đã lưu trong QR.
     */
    public function decryptPayload(string $token): array
    {
        $decoded = base64_decode($token, true);
        if ($decoded === false || strlen($decoded) < 16) {
            throw new RuntimeException('Token QR không hợp lệ.');
        }

        $iv = substr($decoded, 0, 16);
        $cipherText = substr($decoded, 16);
        $plainText = openssl_decrypt(
            $cipherText,
            'AES-256-CBC',
            hash('sha256', $this->secretKey, true),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plainText === false) {
            throw new RuntimeException('Không thể giải mã payload vé.');
        }

        return json_decode($plainText, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Tạo chuỗi dữ liệu QR từ payload vé và chuyển sang ảnh QR dạng Base64.
     * Đây là hàm mục tiêu để gọi sau khi thanh toán thành công.
     */
    public function generateQrBase64(array $payload): string
    {
        $token = $this->encryptPayload($payload);

        $options = new QROptions([
            'eccLevel' => QRCode::ECC_L,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => true,
        ]);

        $qrCode = new QRCode($options);

        return $qrCode->render($token);
    }

    /**
     * Tạo nội dung QR và lưu trực tiếp vào trường qr_code của vé.
     * Hàm này tách riêng để việc lưu trữ không bị lẫn với logic sinh ảnh.
     */
    public function generateAndStoreQrForTicket($ticket, array $payload): string
    {
        $qrBase64 = $this->generateQrBase64($payload);
        $ticket->update([
            'qr_code' => $qrBase64,
        ]);

        return $qrBase64;
    }
}

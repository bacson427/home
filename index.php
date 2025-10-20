<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener - NguyenBacSon.io.vn</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 500px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        .domain {
            color: #667eea;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 30px;
        }
        .url-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input[type="url"], input[type="text"], select {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            width: 100%;
        }
        input[type="url"]:focus, input[type="text"]:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .result {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            display: none;
        }
        .short-url {
            color: #667eea;
            font-weight: bold;
            word-break: break-all;
            font-size: 18px;
            margin-bottom: 10px;
            display: block;
        }
        .expiry-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .form-group {
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        .custom-slug-container {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .slug-preview {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .available { color: #28a745; }
        .taken { color: #dc3545; }
        
        /* QR Code Styles */
        .qr-section {
            margin-top: 20px;
            display: none;
        }
        .qr-code-container {
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 10px;
            display: inline-block;
        }
        #qrcode {
            display: inline-block;
            padding: 10px;
            background: white;
        }
        .qr-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-secondary {
            background: #6c757d;
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .btn-success {
            background: #28a745;
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .loading {
            color: #666;
            font-style: italic;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        .success {
            color: #155724;
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 Rút gọn URL + QR Code</h1>
        <div class="domain">nguyenbacson.io.vn</div>
        
        <form class="url-form" id="urlForm">
            <!-- URL gốc -->
            <div class="form-group">
                <label for="long_url">URL cần rút gọn:</label>
                <input type="url" id="long_url" name="long_url" placeholder="https://example.com/đường-dẫn-rất-dài..." required>
            </div>
            
            <!-- Custom Slug -->
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="use_custom_slug" name="use_custom_slug">
                    <label for="use_custom_slug">Tùy chọn ký tự riêng</label>
                </div>
                <div class="custom-slug-container" id="customSlugContainer">
                    <input type="text" id="custom_slug" name="custom_slug" placeholder="ví-du: my-link" pattern="[a-zA-Z0-9\-_]+" maxlength="30">
                    <div class="slug-preview" id="slugPreview"></div>
                </div>
            </div>
            
            <!-- Thời gian hết hạn -->
            <div class="form-group">
                <label for="expiry_time">Thời gian hết hạn:</label>
                <select name="expiry_time" id="expiry_time" required>
                    <option value="1">1 giờ</option>
                    <option value="3">3 giờ</option>
                    <option value="6">6 giờ</option>
                    <option value="12">12 giờ</option>
                    <option value="24" selected>1 ngày</option>
                    <option value="168">1 tuần</option>
                    <option value="720">1 tháng</option>
                    <option value="8760">1 năm</option>
                    <option value="forever">Vĩnh viễn</option>
                </select>
            </div>
            
            <button type="submit">Rút gọn ngay!</button>
        </form>
        
        <!-- Thông báo lỗi -->
        <div class="error" id="errorMessage"></div>
        
        <!-- Kết quả -->
        <div class="result" id="result">
            <p>✅ URL đã được rút gọn:</p>
            <a class="short-url" id="shortUrl" target="_blank"></a>
            <div class="expiry-info" id="expiryInfo"></div>
            
            <!-- QR Code Section -->
            <div class="qr-section" id="qrSection">
                <hr>
                <h3>📱 Mã QR Code</h3>
                <div class="qr-code-container">
                    <div id="qrcode"></div>
                </div>
                <div class="qr-actions">
                    <button class="btn-secondary" onclick="downloadQR()">Tải QR Code</button>
                    <button class="btn-success" onclick="shareQR()">Chia sẻ</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle custom slug
        document.getElementById('use_custom_slug').addEventListener('change', function() {
            const container = document.getElementById('customSlugContainer');
            container.style.display = this.checked ? 'block' : 'none';
        });

        // Preview custom slug
        document.getElementById('custom_slug').addEventListener('input', function() {
            const preview = document.getElementById('slugPreview');
            if (this.value) {
                preview.innerHTML = `🔗 https://nguyenbacson.io.vn/<span class="available">${this.value}</span>`;
            } else {
                preview.innerHTML = '';
            }
        });

        // Hiển thị thông báo lỗi
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        // Xử lý form
        document.getElementById('urlForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            
            // Hiệu ứng loading
            button.textContent = 'Đang xử lý...';
            button.disabled = true;
            
            try {
                const response = await fetch('#', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Hiển thị URL rút gọn
                    document.getElementById('shortUrl').href = result.short_url;
                    document.getElementById('shortUrl').textContent = result.short_url;
                    document.getElementById('expiryInfo').textContent = result.expiry_text;
                    document.getElementById('result').style.display = 'block';
                    
                    // Tạo QR Code
                    generateQRCode(result.short_url);
                    
                    // Reset form
                    this.reset();
                    document.getElementById('customSlugContainer').style.display = 'none';
                    
                } else {
                    showError('Lỗi: ' + result.error);
                }
            } catch (error) {
                showError('Lỗi kết nối: ' + error.message);
            } finally {
                button.textContent = originalText;
                button.disabled = false;
            }
        });

        // Tạo QR Code
        function generateQRCode(url) {
            const qrContainer = document.getElementById('qrcode');
            qrContainer.innerHTML = '<div class="loading">Đang tạo QR Code...</div>';
            
            setTimeout(() => {
                QRCode.toCanvas(qrContainer, url, {
                    width: 200,
                    height: 200,
                    margin: 1,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                }, function(error) {
                    if (error) {
                        qrContainer.innerHTML = '<div style="color: red;">Lỗi tạo QR Code</div>';
                    } else {
                        document.getElementById('qrSection').style.display = 'block';
                    }
                });
            }, 500);
        }

        // Tải QR Code
        function downloadQR() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'qrcode-nguyenbacson.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }
        }

        // Chia sẻ QR Code
        function shareQR() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas && navigator.share) {
                canvas.toBlob(function(blob) {
                    const file = new File([blob], 'qrcode-nguyenbacson.png', { type: 'image/png' });
                    navigator.share({
                        files: [file],
                        title: 'QR Code - NguyenBacSon',
                        text: 'Quét mã QR để truy cập URL'
                    });
                });
            } else {
                // Fallback: tải xuống
                downloadQR();
            }
        }
    </script>

    <?php
    // PHP Xử lý form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Hàm kiểm tra slug có tồn tại chưa
        function slug_exists($slug) {
            if (!file_exists('urls.json')) return false;
            
            $urls = file('urls.json', FILE_IGNORE_NEW_LINES);
            foreach ($urls as $line) {
                $data = json_decode($line, true);
                if (isset($data[$slug])) {
                    return true;
                }
            }
            return false;
        }

        // Hàm tính thời gian hết hạn
        function calculate_expiry($hours) {
            if ($hours === 'forever') {
                return null;
            }
            return time() + ($hours * 3600);
        }

        // Hàm định dạng thời gian hiển thị
        function format_expiry_text($expiry_time) {
            if ($expiry_time === null) {
                return '⏳ Không bao giờ hết hạn';
            }
            $formatted_date = date('d/m/Y H:i', $expiry_time);
            return '⏰ Hết hạn: ' . $formatted_date;
        }

        header('Content-Type: application/json');
        
        $long_url = filter_var($_POST['long_url'], FILTER_VALIDATE_URL);
        $expiry_hours = $_POST['expiry_time'];
        $use_custom_slug = isset($_POST['use_custom_slug']);
        $custom_slug = $use_custom_slug ? $_POST['custom_slug'] : '';
        
        // Validation
        if (!$long_url) {
            echo json_encode(['success' => false, 'error' => 'URL không hợp lệ']);
            exit;
        }
        
        if (empty($expiry_hours)) {
            echo json_encode(['success' => false, 'error' => 'Vui lòng chọn thời gian hết hạn']);
            exit;
        }
        
        // Xử lý custom slug
        if ($use_custom_slug) {
            if (empty($custom_slug)) {
                echo json_encode(['success' => false, 'error' => 'Vui lòng nhập ký tự tùy chọn']);
                exit;
            }
            
            // Kiểm tra ký tự hợp lệ
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $custom_slug)) {
                echo json_encode(['success' => false, 'error' => 'Chỉ được dùng chữ cái, số, gạch ngang và gạch dưới']);
                exit;
            }
            
            // Kiểm tra slug đã tồn tại
            if (slug_exists($custom_slug)) {
                echo json_encode(['success' => false, 'error' => 'Ký tự này đã có người sử dụng']);
                exit;
            }
            
            $code = $custom_slug;
        } else {
            // Tạo mã ngẫu nhiên
            do {
                $code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
            } while (slug_exists($code));
        }
        
        // Tính thời gian hết hạn
        $expiry_time = calculate_expiry($expiry_hours);
        
        // Dữ liệu cần lưu
        $url_data = [
            'long_url' => $long_url,
            'created_at' => time(),
            'expiry_time' => $expiry_time,
            'clicks' => 0
        ];
        
        // Lưu vào file
        $data_line = json_encode([$code => $url_data]) . "\n";
        file_put_contents('urls.json', $data_line, FILE_APPEND);
        
        // Trả về kết quả
        $short_url = "https://nguyenbacson.io.vn/{$code}";
        
        echo json_encode([
            'success' => true,
            'short_url' => $short_url,
            'expiry_text' => format_expiry_text($expiry_time)
        ]);
        exit;
    }
    ?>
</body>
</html>
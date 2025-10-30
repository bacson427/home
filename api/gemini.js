const { GoogleGenerativeAI } = require("@google/generative-ai");

module.exports = async (req, res) => {
  // Cấu hình CORS - cho phép truy cập từ mọi nơi
  res.setHeader('Access-Control-Allow-Credentials', true);
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,PATCH,DELETE,POST,PUT');
  res.setHeader('Access-Control-Allow-Headers', 'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version');

  // Xử lý preflight request
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Chỉ cho phép POST request
  if (req.method !== 'POST') {
    return res.status(405).json({ 
      success: false,
      error: 'Chỉ cho phép POST method' 
    });
  }

  try {
    const { prompt } = req.body;

    // Kiểm tra có prompt không
    if (!prompt) {
      return res.status(400).json({ 
        success: false,
        error: 'Thiếu prompt. Vui lòng cung cấp nội dung cần hỏi.' 
      });
    }

    // Kiểm tra API key
    if (!process.env.GEMINI_API_KEY) {
      return res.status(500).json({ 
        success: false,
        error: 'API Key chưa được cấu hình' 
      });
    }

    // Khởi tạo Gemini AI
    const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
    const model = genAI.getGenerativeModel({ model: "gemini-pro" });

    // Gọi API Gemini
    const result = await model.generateContent(prompt);
    const response = await result.response;
    const text = response.text();

    // Trả về kết quả
    res.status(200).json({ 
      success: true, 
      response: text 
    });
    
  } catch (error) {
    console.error('Lỗi Gemini API:', error);
    
    // Xử lý các lỗi cụ thể
    let errorMessage = 'Lỗi server nội bộ';
    if (error.message.includes('API_KEY_INVALID')) {
      errorMessage = 'API Key không hợp lệ';
    } else if (error.message.includes('QUOTA_EXCEEDED')) {
      errorMessage = 'Đã hết hạn ngạc API';
    }

    res.status(500).json({ 
      success: false, 
      error: errorMessage
    });
  }
};
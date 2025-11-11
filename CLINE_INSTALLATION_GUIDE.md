# Hướng dẫn cài đặt Cline Extension trong Cursor

## Cách 1: Cài đặt từ Extension Marketplace (Khuyến nghị)

### Các bước thực hiện:

1. **Mở Extensions trong Cursor**
   - Nhấn tổ hợp phím: `Ctrl + Shift + X`
   - Hoặc vào Menu: View → Extensions

2. **Tìm kiếm Cline**
   - Trong ô tìm kiếm, gõ: `Cline`
   - Tìm extension có tên "Cline" của tác giả "saoudrizwan"

3. **Cài đặt**
   - Click vào nút "Install"
   - Đợi quá trình cài đặt hoàn tất

4. **Khởi động lại Cursor** (nếu cần)
   - Có thể cần khởi động lại để extension hoạt động

## Cách 2: Cài đặt từ VS Code Marketplace (Nếu cách 1 không hoạt động)

1. Truy cập: https://marketplace.visualstudio.com/items?itemName=saoudrizwan.claude-dev
2. Click nút "Install"
3. Nó sẽ tự động mở Cursor và cài đặt

## Cách 3: Cài đặt từ file VSIX

Nếu bạn muốn cài đặt thủ công từ file:

1. Tải file .vsix từ: https://github.com/cline/cline/releases
2. Trong Cursor, mở Command Palette (`Ctrl + Shift + P`)
3. Gõ: "Extensions: Install from VSIX"
4. Chọn file .vsix đã tải

## Sau khi cài đặt

1. Extension Cline sẽ xuất hiện trên thanh sidebar bên trái
2. Click vào biểu tượng Cline để mở
3. Cấu hình API key (nếu cần)
4. Bắt đầu sử dụng!

## Lưu ý quan trọng

- **Cline là VSCode/Cursor extension**, KHÔNG phải npm package
- Không thể cài qua `npm install`
- Phải cài qua Extensions marketplace của Cursor
- Hiện tại bạn đang dùng AI assistant (có thể là Cline hoặc Cursor AI built-in)

## Kiểm tra xem Cline đã được cài chưa

1. Mở Extensions (`Ctrl + Shift + X`)
2. Tìm "Cline" trong danh sách installed extensions
3. Nếu đã có → Bạn đã cài đặt thành công rồi!

---

**English Version Below:**

# Cline Extension Installation Guide for Cursor

## Method 1: Install from Extension Marketplace (Recommended)

### Steps:

1. **Open Extensions in Cursor**
   - Press: `Ctrl + Shift + X`
   - Or go to: View → Extensions

2. **Search for Cline**
   - In the search box, type: `Cline`
   - Find the extension named "Cline" by "saoudrizwan"

3. **Install**
   - Click the "Install" button
   - Wait for installation to complete

4. **Restart Cursor** (if needed)
   - May need to restart for the extension to work

## Method 2: Install from VS Code Marketplace

1. Visit: https://marketplace.visualstudio.com/items?itemName=saoudrizwan.claude-dev
2. Click "Install" button
3. It will automatically open Cursor and install

## Method 3: Install from VSIX file

If you want to install manually:

1. Download .vsix file from: https://github.com/cline/cline/releases
2. In Cursor, open Command Palette (`Ctrl + Shift + P`)
3. Type: "Extensions: Install from VSIX"
4. Select the downloaded .vsix file

## After Installation

1. Cline extension will appear in the left sidebar
2. Click the Cline icon to open it
3. Configure API key (if needed)
4. Start using!

## Important Notes

- **Cline is a VSCode/Cursor extension**, NOT an npm package
- Cannot be installed via `npm install`
- Must be installed through Cursor's Extensions marketplace
- You're currently using an AI assistant (possibly Cline or Cursor's built-in AI)

## Check if Cline is Already Installed

1. Open Extensions (`Ctrl + Shift + X`)
2. Look for "Cline" in the installed extensions list
3. If it's there → You've already installed it successfully!

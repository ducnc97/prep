# Giải pháp phòng chống chia sẻ tài khoản và gian lận

## 1. Các hình thức chia sẻ tài khoản phổ biến:

- **Nhiều người sử dụng cùng một thông tin đăng nhập** (tên người dùng/mật khẩu).
- **Chia sẻ cookie phiên hoặc access_token**.

## 2. Giải pháp và ưu, nhược điểm

### 2.1. Giới hạn thiết bị đăng nhập cùng lúc

- **Ưu điểm**: Dễ dàng kiểm soát thông qua access_token.
- **Nhược điểm**: Giảm trải nghiệm người dùng.

**Luồng hoạt động**:
- Giả sử chỉ cho phép **2 thiết bị đăng nhập cùng lúc**.
- Đăng nhập -> lưu **access token** và **thời gian hết hạn** vào **cache**.
- Khi truy cập, kiểm tra **access token** tồn tại trong cache.
- Nếu thiết bị thứ 3 đăng nhập:
  - Hiển thị thông báo yêu cầu **đăng xuất thiết bị khác**.
  - Nếu đồng ý, sẽ **logout** thiết bị khác.

### 2.2. Đặt thời gian sống (TTL) ngắn cho access_token

- **Ưu điểm**: Tránh được việc sử dụng chung access_token.
- **Nhược điểm**: Tăng thời gian request để lấy lại access_token.

**Luồng hoạt động**:
- Giả sử đăng nhập -> sao chép **cookie** sang thiết bị thứ 3.
- Sau một khoảng thời gian ngắn, **access token** sẽ hết hạn.
- Thiết bị sẽ gọi **refresh token** và **một trong hai thiết bị** sẽ không thể sử dụng được nữa.

### 2.3. Định danh địa lý

- **Ưu điểm**: Phát hiện nhanh khi có các lượt truy cập từ vị trí xa nhau trong khoảng thời gian ngắn.
- **Nhược điểm**: Có thể bị lừa bởi VPN, cần có các cảnh báo cho khách hàng.

**Luồng hoạt động**:
- Giả sử đăng nhập -> sao chép **cookie** sang thiết bị thứ 3.
- Có thể truyền **location** từ frontend hoặc xác định qua **IP**.
- Kiểm tra **location** request hiện tại với **location** trước đó:
  - Nếu khoảng cách (dung sai 5m) / thời gian > vận tốc máy bay -> có thể gian lận.
  - Ghi log tất cả request -> đầu ngày hôm sau chạy batch kiểm tra **location** từ request theo user.

### 2.4. Dựa trên đặc điểm thiết bị

- **Ưu điểm**: Nhận diện dựa trên hệ điều hành, trình duyệt, màn hình.
- **Nhược điểm**: Cần cẩn thận tránh vi phạm quyền riêng tư, nhầm lẫn với các phần mềm virus.

**Luồng hoạt động**:
- Giả sử đăng nhập -> sao chép **cookie** sang thiết bị thứ 3.
- Kiểm tra đặc điểm thiết bị như:
  - **Browser** (loại, phiên bản, ...),
  - **Kích thước màn hình thiết bị**, ...
  - Cẩn thận tránh vi phạm quyền riêng tư.
- Kiểm tra cùng một **access token** request ở những đặc điểm thiết bị khác nhau -> có thể gian lận.

### 2.5. Khi phát hiện khả nghi, cảnh báo người dùng nếu phát hiện gian lận và sẽ cấm tài khoản

- **Ưu điểm**: Hạn chế gian lận với những người tâm lý sợ bị mất tài khoản.
- **Nhược điểm**: 

### 2.6. Sau khi phát hiện có thể gian lận, đếm nếu lớn hơn 10 lần

- Bắt đăng nhập lại -> reset số lần gian lận.
- Kiểm tra log, nếu gian lận nhiều lần, liên hệ người dùng để điều tra.

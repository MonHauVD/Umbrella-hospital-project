# MODULE 9

## CHỨC NĂNG XEM CHUYÊN KHOA

| Conditions                  | Rule 1 | Rule 2 | Rule 3 | Rule 4 | Rule 5 |
| --------------------------- | ------ | ------ | ------ | ------ | ------ |
| Đăng nhập                   | Yes    | Yes    | Yes    | Yes    | No     |
| Vai trò Admin               | Yes    | Yes    | Yes    | No     | -      |
| Trạng thái "Đang hoạt động" | Yes    | Yes    | No     | -      | -      |
| Có dữ liệu                  | Yes    | No     | -      | -      | -      |
| **Actions**                 |        |        |        |        |        |
| Xem danh sách chuyên khoa   | 1      | 1      | 0      | 0      | 0      |

1: Xem đuợc danh sách chuyên khoa

0: Không xem đuợc
### Xemchuyenkhoa_001
Issue 3:
<video controls src="2025-04-23 21-35-21.mp4" title="Title"></video>

Issue 10: 
![alt text](image-1.png)

Issue 16:
<video controls src="2025-04-23 16-03-15.mp4" title="Title"></video>

Issue 17: Không có tooltip

Issue 21:
<video controls src="2025-04-23 16-07-07.mp4" title="Title"></video>

Issue 22: 
<video controls src="2025-04-23 16-09-11.mp4" title="Title"></video>


Issue 35: 
![alt text](image-3.png)

Issue 36:
<video controls src="2025-04-23 16-53-12.mp4" title="Title"></video>


## Xemchuyenkhoa_004
Issue 83:
![alt text](image-4.png)

## Xemchuyenkhoa_006
Issue 83:
<video controls src="2025-04-23 21-09-17.mp4" title="Title"></video>

## CHỨC NĂNG TẠO MỚI CHUYÊN KHOA

| Điều kiện                               | R1  | R2  | R3  | R4  | R5  | R6  |
| --------------------------------------- | --- | --- | --- | --- | --- | --- |
| Người dùng đã đăng nhập                 | Y   | Y   | Y   | Y   | N   | Y   |
| Vai trò là bác sĩ hoặc ADMIN            | Y   | Y   | N   | Y   | -   | Y   |
| Tài khoản đang hoạt động                | Y   | N   | -   | Y   | -   | Y   |
| Tên chuyên khoa không tồn tại           | Y   | -   | -   | N   | -   | -   |
| Nhập đầy đủ thông tin                   | Y   | -   | -   | -   | -   | N   |
| **Hành động**                           |     |     |     |     |     |     |
| Hệ thống hiển thị form tạo mới          | Y   | N   | N   | Y   | N   | Y   |
| Hệ thống lưu chuyên khoa mới            | Y   | N   | N   | N   | N   | N   |
| Hệ thống báo lỗi đăng nhập              | N   | N   | N   | N   | Y   | N   |
| Hệ thống báo lỗi tài khoản bị khóa      | N   | Y   | N   | N   | N   | N   |
| Hệ thống báo lỗi không có quyền         | N   | N   | Y   | N   | N   | N   |
| Hệ thống báo lỗi chuyên khoa đã tồn tại | N   | N   | N   | Y   | N   | N   |
| Hệ thống báo lỗi thông tin không đầy đủ | N   | N   | N   | N   | N   | Y   |

## Taochuyenkhoa_001
Issue 1: Hiển thị sai tên form
![alt text](image-5.png)


Issue 22: 
<video controls src="2025-04-23 21-25-41.mp4" title="Title"></video>

Issue 26:
![alt text](image-2.png)

Issue 35:
<video controls src="2025-04-23 21-30-28.mp4" title="Title"></video>

Issue 36:
<video controls src="2025-04-23 21-55-09.mp4" title="Title"></video>


## Taochuyenkhoa_003
Issue 83:
![alt text](image-4.png)


## Taochuyenkhoa_005
Issue 83:
![alt text](image-7.png)


## Taochuyenkhoa_006
Issue 83:
<video controls src="2025-04-23 22-06-38.mp4" title="Title"></video>

## Taochuyenkhoa_008
Issue 83:
![alt text](image-8.png)

## Taochuyenkhoa_009
Issue 83:
![alt text](image-9.png)

## Taochuyenkhoa_011
Issue 83:
![alt text](image-10.png)

## CHỨC NĂNG XOÁ CHUYÊN KHOA

| Xóa chuyên khoa                                          |        |        |        |        |        |        |
| -------------------------------------------------------- | ------ | ------ | ------ | ------ | ------ | ------ |
| **Điều kiện**                                            | **R1** | **R2** | **R3** | **R4** | **R5** | **R6** |
| Người dùng đã đăng nhập                                  | Y      | Y      | Y      | Y      | N      | Y      |
| Vai trò là ADMIN                                         | Y      | Y      | N      | Y      | -      | Y      |
| Tài khoản đang hoạt động                                 | Y      | N      | -      | Y      | -      | Y      |
| Chuyên khoa tồn tại                                      | Y      | -      | -      | Y      | -      | N      |
| Chuyên khoa không có bác sĩ đang làm việc                | Y      | -      | -      | N      | -      | -      |
| **Hành động**                                            |        |        |        |        |        |        |
| Hệ thống hiển thị form xác nhận xóa                      | Y      | N      | N      | Y      | N      | Y      |
| Hệ thống xóa chuyên khoa thành công                      | Y      | N      | N      | N      | N      | N      |
| Hệ thống báo lỗi đăng nhập                               | N      | N      | N      | N      | Y      | N      |
| Hệ thống báo lỗi tài khoản bị khóa                       | N      | Y      | N      | N      | N      | N      |
| Hệ thống báo lỗi không có quyền                          | N      | N      | Y      | N      | N      | N      |
| Hệ thống báo lỗi không thể xóa (có bác sĩ đang làm việc) | N      | N      | N      | Y      | N      | N      |
| Hệ thống báo lỗi chuyên khoa không tồn tại               | N      | N      | N      | N      | N      | Y      |
## Xoachuyenkhoa_002
Issue 13:
![alt text](image-11.png)

Issue 83:
![alt text](image-12.png)

## Xoachuyenkhoa_003:
Issue 83:
![alt text](image-14.png)

## Xoachuyenkhoa_004:
Issue 83:
![alt text](image-4.png)

## Xoachuyenkhoa_006
Issue 83:
<video controls src="2025-04-23 22-25-11.mp4" title="Title"></video>

## Xoachuyenkhoa_007
Issue 83:
<video controls src="2025-04-23 22-27-27.mp4" title="Title"></video>

## CHỨC NĂNG TÌM KIẾM CHUYÊN KHOA
## Timchuyenkhoa_004
Issue 87:
<video controls src="2025-04-23 22-54-11.mp4" title="Title"></video>

## Timchuyenkhoa_005
Issue 87, 88:
<video controls src="2025-04-23 22-56-09.mp4" title="Title"></video>

## Timchuyenkhoa_006
Issue 107:
<video controls src="2025-04-23 23-33-47.mp4" title="Title"></video>

* Database:
![alt text](image-13.png)


## Timchuyenkhoa_007
Issue 87, 88:
<video controls src="2025-04-23 23-38-10.mp4" title="Title"></video>

## Timchuyenkhoa_008
Issue 87:
<video controls src="2025-04-23 23-41-22.mp4" title="Title"></video>

## Timchuyenkhoa_010
Issue 87:
<video controls src="2025-04-23 23-42-56.mp4" title="Title"></video>

## Timchuyenkhoa_013, Timkiemchuyenkhoa_014
Issue 108:
<video controls src="2025-04-23 23-48-40.mp4" title="Title"></video>


## Chức năng Cập nhật chuyên khoa
| **Điều kiện**                                                  | R1  | R2  | R3  | R4  | R5  | R6  | R7  |
| -------------------------------------------------------------- | --- | --- | --- | --- | --- | --- | --- |
| Người dùng đã đăng nhập                                        | Y   | Y   | Y   | Y   | Y   | N   | Y   |
| Vai trò là admin                                               | Y   | Y   | Y   | Y   | N   | -   | -   |
| Tài khoản đang hoạt động                                       | Y   | Y   | Y   | N   | -   | -   | -   |
| Chuyên khoa tồn tại                                            | Y   | Y   | Y   | -   | -   | -   | N   |
| Tên chuyên khoa mới không trùng (trả về description) trùng tên | Y   | Y   | N   | -   | -   | -   | -   |
| **Hành động**                                                  |     |     |     |     |     |     |     |
| Hiển thị cập nhật chuyên khoa thành công                       | Y   | N   | N   | N   | N   | N   | N   |
| Hiển thị thông báo lỗi không có quyền                          | N   | N   | Y   | N   | Y   | N   | N   |
| Hiển thị thông báo chuyên khoa không tồn tại                   | N   | N   | N   | Y   | N   | N   | Y   |
| Hiển thị thông báo yêu cầu nhập tên                            | N   | Y   | N   | N   | N   | N   | N   |
| Hiển thị thông báo yêu cầu nhập mô tả                          | N   | N   | N   | N   | N   | Y   | N   |

## Capnhatchuyenkhoa_001
Issue 6:
<video controls src="2025-04-23 23-53-49.mp4" title="Title"></video>

## Capnhatchuyenkhoa_003
Issue 83:
![alt text](image-15.png)

## Capnhatchuyenkhoa_004
Issue 83:
![alt text](image-16.png)


## Capnhatchuyenkhoa_005
Issue 83:
<video controls src="2025-04-24 00-03-11.mp4" title="Title"></video>

## Capnhatchuyenkhoa_006
Issue 83:
![alt text](image-4.png)

## Capnhatchuyenkhoa_008
Issue 87:
<video controls src="2025-04-23 22-25-11.mp4" title="Title"></video>

## Capnhatchuyenkhoa_011
Issue 83, 84:
![alt text](image-17.png)

## Capnhatchuyenkhoa_012
Issue 104:
<video controls src="2025-04-24 00-17-05.mp4" title="Title"></video>


| stt | ten  | ngay |
| --- | ---- | ---- |
| 1   | Ngat | 123  |
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
Issue 3: (Bấm vào dể xem video)

[<img src="https://img.youtube.com/vi/U0wBUiiCmVQ/0.jpg" width="50%">](https://www.youtube.com/watch?v=U0wBUiiCmVQ)

Issue 10: 
![alt text](image-1.png)

Issue 16: (Bấm dể xem video)

[<img src="https://img.youtube.com/vi/buD2eapz1Xw/0.jpg" width="50%">](https://www.youtube.com/watch?v=buD2eapz1Xw)

Issue 17: Không có tooltip

Issue 21: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/69miz9luI2k/0.jpg" width="50%">](https://www.youtube.com/watch?v=69miz9luI2k)

Issue 22: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/ihsX3EuaO-w/0.jpg" width="50%">](https://www.youtube.com/watch?v=ihsX3EuaO-w)


Issue 35: 
![alt text](image-3.png)

Issue 36: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/9KM97xUKGWg/0.jpg" width="50%">](https://www.youtube.com/watch?v=9KM97xUKGWg)


## Xemchuyenkhoa_004
Issue 83:
![alt text](image-4.png)

## Xemchuyenkhoa_006
Issue 83: (Bâm để xem video)

[<img src="https://img.youtube.com/vi/u5n-92jiGnQ/0.jpg" width="50%">](https://www.youtube.com/watch?v=u5n-92jiGnQ)


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
Issue 1:

![alt text](image-5.png)


Issue 22: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/J4pC6ZjPshI/0.jpg" width="50%">](https://www.youtube.com/watch?v=J4pC6ZjPshI)

Issue 26:
![alt text](image-2.png)

Issue 35: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/2JnEtKf6eJ0/0.jpg" width="50%">](https://www.youtube.com/watch?v=2JnEtKf6eJ0)

Issue 36: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/zQwzVwJRILo/0.jpg" width="50%">](https://www.youtube.com/watch?v=zQwzVwJRILo)

## Taochuyenkhoa_003
Issue 83:
![alt text](image-4.png)


## Taochuyenkhoa_005
Issue 83:
![alt text](image-7.png)


## Taochuyenkhoa_006
Issue 83: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/haRugvxIWkM/0.jpg" width="50%">](https://www.youtube.com/watch?v=haRugvxIWkM)

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
Issue 83: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/BKdlyUwnHwU/0.jpg" width="50%">](https://www.youtube.com/watch?v=BKdlyUwnHwU)

## Xoachuyenkhoa_007
Issue 83: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/-nQXPk5Inrw/0.jpg" width="50%">](https://www.youtube.com/watch?v=-nQXPk5Inrw)

## CHỨC NĂNG TÌM KIẾM CHUYÊN KHOA
## Timchuyenkhoa_004
Issue 87: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/PdHZNPY200o/0.jpg" width="50%">](https://www.youtube.com/watch?v=PdHZNPY200o)

## Timchuyenkhoa_005
Issue 87, 88: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/s2XbYyaqoQo/0.jpg" width="50%">](https://www.youtube.com/watch?v=s2XbYyaqoQo)

## Timchuyenkhoa_006
Issue 107: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/oCYctJanw0k/0.jpg" width="50%">](https://www.youtube.com/watch?v=oCYctJanw0k)

* Database:
![alt text](image-13.png)


## Timchuyenkhoa_007
Issue 87, 88: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/XTeCI1qzbwM/0.jpg" width="50%">](https://www.youtube.com/watch?v=XTeCI1qzbwM)

## Timchuyenkhoa_008
Issue 87: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/5oLzXn1-ncA/0.jpg" width="50%">](https://www.youtube.com/watch?v=5oLzXn1-ncA)

## Timchuyenkhoa_010
Issue 87: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/84KoOoxU8ok/0.jpg" width="50%">](https://www.youtube.com/watch?v=84KoOoxU8ok)

## Timchuyenkhoa_013, Timkiemchuyenkhoa_014
Issue 108: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/PVOaoIovud8/0.jpg" width="50%">](https://www.youtube.com/watch?v=PVOaoIovud8)


## Chức năng Cập nhật chuyên khoa
| Cập nhật chuyên khoa                            |        |        |        |        |        |        |        |
| ----------------------------------------------- | ------ | ------ | ------ | ------ | ------ | ------ | ------ |
| **Điều kiện**                                   | **R1** | **R2** | **R3** | **R4** | **R5** | **R6** | **R7** |
| Người dùng đã đăng nhập                         | Y      | Y      | Y      | Y      | Y      | Y      | N      |
| Vai trò là admin                                | Y      | Y      | Y      | Y      | Y      | N      | -      |
| Tài khoản đang hoạt động                        | Y      | Y      | Y      | Y      | N      | -      | -      |
| Chuyên khoa tồn tại                             | Y      | Y      | Y      | N      | -      | -      | -      |
| Tên chuyên khoa mới không trùng                 | Y      | Y      | N      | -      | -      | -      | -      |
| Mô tả (description) không rỗng                  | Y      | N      | -      | -      | -      | -      | -      |
| **Hành động**                                   |        |        |        |        |        |        |        |
| Cho phép cập nhật chuyên khoa thành công        | Y      | N      | N      | N      | N      | N      | N      |
| Hiển thị thông báo lỗi đăng nhập/không có quyền | N      | N      | N      | N      | Y      | Y      | Y      |
| Hiển thị thông báo chuyên khoa không tồn tại    | N      | N      | N      | Y      | N      | N      | N      |
| Hiển thị thông báo yêu cầu nhập tên             | N      | N      | Y      | N      | N      | N      | N      |
| Hiển thị thông báo yêu cầu nhập mô tả           | N      | Y      | N      | N      | N      | N      | N      |
## Capnhatchuyenkhoa_001
Issue 6: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/djIPU8nPaOE/0.jpg" width="50%">](https://www.youtube.com/watch?v=djIPU8nPaOE)


## Capnhatchuyenkhoa_003
Issue 83:
![alt text](image-15.png)

## Capnhatchuyenkhoa_004
Issue 83:
![alt text](image-16.png)


## Capnhatchuyenkhoa_005
Issue 83: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/8Csl--mNEAY/0.jpg" width="50%">](https://www.youtube.com/watch?v=8Csl--mNEAY)

## Capnhatchuyenkhoa_006
Issue 83:
![alt text](image-4.png)

## Capnhatchuyenkhoa_008
Issue 87: (Bấm để xem video)

[<img src="https://img.youtube.com/vi/BKdlyUwnHwU/0.jpg" width="50%">](https://www.youtube.com/watch?v=BKdlyUwnHwU)

## Capnhatchuyenkhoa_011
Issue 83, 84:
![alt text](image-17.png)

## Capnhatchuyenkhoa_012
Issue 104:(Bấm để xem video)

[<img src="https://img.youtube.com/vi/4YfUvXVtHmI/0.jpg" width="50%">](https://www.youtube.com/watch?v=4YfUvXVtHmI)
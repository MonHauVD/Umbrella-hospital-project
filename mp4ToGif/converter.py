# Cài đặt thư viện moviepy nếu chưa có
# .venv/Scripts/activate
# pip install moviepy
# py mp4ToGif/converter.py

from moviepy import VideoFileClip

# Đường dẫn đến video MP4
input_video_path = "D:/Quay màn hình/Video cần up/2025-04-24 01-22-32.mp4"
# Đường dẫn nơi bạn muốn lưu GIF
output_gif_path = "mp4ToGif/output.gif"

# Mở video và chuyển đổi thành GIF
clip = VideoFileClip(input_video_path)

# clip = clip.subclip(0, 10)  # Chọn phần đầu 10 giây của video (tuỳ chọn)
# clip = clip.resize(width=480)  # hoặc height=360 để giảm kích thước

clip.write_gif(output_gif_path, fps=clip.fps) # Ghi ra file GIF (tự động giữ tốc độ gốc)


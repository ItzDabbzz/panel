<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhook',

    'sections' => [
        'details' => 'Chi tiết',
        'details_description' => 'Webhook báo cho một dịch vụ khác ngay khi có điều gì đó xảy ra trên bảng điều khiển.',
        'delivery' => 'Gửi đi',
        'delivery_description' => 'Sự kiện được gửi đến đâu và bên nhận có thể tin cậy chúng như thế nào.',
        'message' => 'Tin nhắn Discord',
        'message_description' => 'Nội dung được đăng lên kênh cho mỗi sự kiện.',
        'events' => 'Sự kiện',
        'events_description' => 'Chọn những gì webhook này được thông báo. Cần ít nhất một sự kiện.',
    ],

    'fields' => [
        'name' => 'Tên',
        'description' => 'Mô tả',
        'enabled' => 'Đã bật',
        'endpoint' => 'URL điểm cuối',
        'type' => 'Loại',
        'secret' => 'Khóa ký',
        'headers' => 'Header tùy chỉnh',
        'header_name' => 'Header',
        'header_value' => 'Giá trị',
        'message_username' => 'Tên hiển thị',
        'message_avatar' => 'URL ảnh đại diện',
        'message_content' => 'Tin nhắn',
        'message_title' => 'Tiêu đề embed',
        'message_description' => 'Mô tả embed',
        'message_color' => 'Màu embed',
        'message_footer' => 'Chân embed',
        'message_timestamp' => 'Hiển thị thời điểm sự kiện xảy ra',
    ],

    'placeholders' => [
        'name' => 'Đồng bộ thanh toán',
        'description' => 'Webhook này dùng để làm gì.',
    ],

    'helpers' => [
        'secret' => 'Khi được đặt, mỗi yêu cầu mang một header X-Webhook-Signature chứa "sha256=" theo sau là HMAC của nội dung yêu cầu được tạo bằng khóa này.',
        'message_variables' => 'Dùng {{label}} cho tên sự kiện, {{summary}} cho một câu mô tả sự kiện, {{event}}, {{timestamp}}, {{panel}} và {{panel_url}}. Mọi thứ trong sự kiện cũng dùng được, ví dụ {{data.name}} hoặc {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Tiêu chuẩn',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Bản ghi',
        'auth' => 'Hoạt động đăng nhập',
        'account' => 'Hoạt động tài khoản',
        'server_power' => 'Nguồn và bảng điều khiển máy chủ',
        'server_files' => 'Tệp máy chủ',
        'server_backups' => 'Bản sao lưu máy chủ',
        'server_databases' => 'Cơ sở dữ liệu máy chủ',
        'server_schedules' => 'Lịch trình máy chủ',
        'server_settings' => 'Cài đặt máy chủ',
        'admin' => 'Quản trị',
    ],

    'resources' => [
        'server' => 'Máy chủ',
        'user' => 'Người dùng',
        'subuser' => 'Người dùng phụ',
        'node' => 'Nút',
        'location' => 'Địa điểm',
        'nest' => 'Nest',
        'egg' => 'Egg',
        'mount' => 'Điểm gắn kết',
        'database_host' => 'Máy chủ cơ sở dữ liệu',
        'database' => 'Cơ sở dữ liệu',
        'backup' => 'Bản sao lưu',
        'schedule' => 'Lịch trình',
        'role' => 'Vai trò',
        'alert' => 'Cảnh báo',
    ],

    'model_actions' => [
        'created' => 'được tạo',
        'updated' => 'được cập nhật',
        'deleted' => 'bị xóa',
    ],

    'events' => [
        'model' => ':resource :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'Hệ thống',
    ],

    'columns' => [
        'name' => 'Tên',
        'type' => 'Loại',
        'endpoint' => 'Gửi đến',
        'events' => 'Sự kiện',
        'last_delivery' => 'Lần gửi gần nhất',
        'enabled' => 'Đã bật',
    ],

    'statuses' => [
        'delivered' => 'Đã gửi',
        'failed' => 'Thất bại',
        'never' => 'Chưa gửi gì',
    ],

    'deliveries' => [
        'title' => 'Lượt gửi',
        'label' => 'lượt gửi',
        'plural_label' => 'lượt gửi',
        'description' => 'Mọi thứ đã gửi đến webhook này trong :days ngày qua.',
        'empty' => 'Chưa gửi gì',
        'empty_description' => 'Các lượt gửi xuất hiện ở đây ngay khi một trong các sự kiện đã chọn xảy ra. Dùng Gửi thử để thử ngay.',
        'no_response' => 'Không có phản hồi',
        'no_body' => 'Điểm cuối không gửi lại gì.',
        'disabled' => 'Bật webhook để gửi lại mục này.',
        'columns' => [
            'id' => 'ID lượt gửi',
            'status' => 'Đã gửi',
            'event' => 'Sự kiện',
            'response' => 'Phản hồi',
            'attempts' => 'Số lần thử',
            'duration' => 'Thời gian',
            'sent' => 'Đã gửi',
            'payload' => 'Nội dung',
            'body' => 'Nội dung phản hồi',
        ],
    ],

    'actions' => [
        'test' => 'Gửi thử',
        'duplicate' => 'Nhân bản',
        'redeliver' => 'Gửi lại',
        'add_header' => 'Thêm header',
        'generate_secret' => 'Tạo',
        'select_all' => 'Chọn tất cả',
        'deselect_all' => 'Bỏ chọn tất cả',
    ],

    'notices' => [
        'delivered' => 'Đã gửi thử thành công.',
        'failed' => 'Không thể gửi thử.',
        'responded' => 'Điểm cuối trả lời :status trong :ms ms.',
        'duplicated' => 'Webhook đã được nhân bản và để ở trạng thái tắt.',
        'redelivery_queued' => 'Sự kiện đang được gửi lại.',
    ],

    'validation' => [
        'events' => 'Chọn ít nhất một sự kiện.',
        'header_name' => '":name" không phải là tên header hợp lệ.',
        'header_reserved' => 'Header ":name" do bảng điều khiển đặt và không thể thay đổi.',
    ],

    'errors' => [
        'invalid_endpoint' => 'Điểm cuối không phải là địa chỉ http hoặc https hợp lệ.',
        'unresolvable' => 'Không tìm thấy địa chỉ của :host.',
        'private_endpoint' => ':host trỏ đến một địa chỉ riêng tư hoặc dành riêng. Đặt APP_WEBHOOK_ALLOW_PRIVATE=true để cho phép gửi trong mạng nội bộ của bạn.',
        'unreachable' => 'Không thể kết nối đến :host.',
        'curl_required' => 'Gửi đến một tên máy chủ cần tiện ích mở rộng PHP cURL, nhưng tiện ích này chưa được cài đặt.',
    ],

    'copy_suffix' => ':name (bản sao)',

];

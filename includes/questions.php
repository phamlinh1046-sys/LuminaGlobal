<?php
// ============================================================
//  QUESTIONS — Quick Scan (15 Q) + Deep Discovery (35 Q)
// ============================================================

function get_questions(string $type): array {
    $quick = [
        // ── IDENTITY ────────────────────────────────────────
        [
            'key' => 'identity_role',
            'section' => 'Nhận Diện Bản Thân',
            'type' => 'choice',
            'text' => 'Trong công việc và cuộc sống, bạn thấy mình phù hợp nhất với vai trò nào?',
            'options' => [
                'a' => 'Người kiến tạo — xây dựng hệ thống và chiến lược',
                'b' => 'Người truyền cảm hứng — dẫn dắt và thay đổi người khác',
                'c' => 'Người thực thi — biến ý tưởng thành kết quả cụ thể',
                'd' => 'Người khám phá — liên tục học hỏi và tìm tòi điều mới',
            ],
        ],
        [
            'key' => 'identity_alive',
            'section' => 'Nhận Diện Bản Thân',
            'type' => 'choice',
            'text' => 'Điều gì khiến bạn cảm thấy ALIVE nhất — tràn đầy năng lượng và ý nghĩa?',
            'options' => [
                'a' => 'Giải quyết một bài toán phức tạp mà người khác bỏ cuộc',
                'b' => 'Thấy người khác phát triển nhờ sự giúp đỡ của mình',
                'c' => 'Hoàn thành một dự án đòi hỏi kỷ luật và nỗ lực lớn',
                'd' => 'Khám phá một ý tưởng hoặc lĩnh vực hoàn toàn mới',
            ],
        ],
        [
            'key' => 'identity_achievement',
            'section' => 'Nhận Diện Bản Thân',
            'type' => 'choice',
            'text' => 'Khi nhìn lại những thành tựu đáng tự hào, yếu tố nào xuất hiện nhiều nhất?',
            'options' => [
                'a' => 'Tôi đã nghĩ ra một cách tiếp cận độc đáo, sáng tạo',
                'b' => 'Tôi đã kết nối và truyền cảm hứng cho người khác',
                'c' => 'Tôi đã kiên trì và không từ bỏ dù khó khăn',
                'd' => 'Tôi đã học được kỹ năng mới và áp dụng thành công',
            ],
        ],
        // ── STRENGTHS ───────────────────────────────────────
        [
            'key' => 'strength_asked',
            'section' => 'Điểm Mạnh Cốt Lõi',
            'type' => 'choice',
            'text' => 'Người xung quanh thường tìm đến bạn khi họ cần...?',
            'options' => [
                'a' => 'Lời khuyên chiến lược hoặc phân tích sâu',
                'b' => 'Sự hỗ trợ cảm xúc hoặc động lực',
                'c' => 'Giải quyết vấn đề cụ thể, thực tiễn',
                'd' => 'Thông tin, kiến thức hoặc góc nhìn mới',
            ],
        ],
        [
            'key' => 'strength_fast',
            'section' => 'Điểm Mạnh Cốt Lõi',
            'type' => 'choice',
            'text' => 'Kỹ năng nào bạn học nhanh hơn và tự nhiên giỏi hơn người khác?',
            'options' => [
                'a' => 'Tư duy hệ thống, lập kế hoạch và tổ chức',
                'b' => 'Giao tiếp, thuyết phục và xây dựng quan hệ',
                'c' => 'Triển khai, thực thi và quản lý chi tiết',
                'd' => 'Nghiên cứu, phân tích dữ liệu và học hỏi',
            ],
        ],
        [
            'key' => 'strength_hidden',
            'section' => 'Điểm Mạnh Cốt Lõi',
            'type' => 'choice',
            'text' => 'Điểm mạnh nào bạn thường "coi thường" vì nó quá dễ dàng với bạn?',
            'options' => [
                'a' => 'Nhìn thấy pattern và kết nối các điểm rời rạc',
                'b' => 'Cảm nhận trạng thái cảm xúc của người khác',
                'c' => 'Chuyển ý tưởng mơ hồ thành kế hoạch cụ thể',
                'd' => 'Hấp thụ và tổng hợp thông tin phức tạp nhanh chóng',
            ],
        ],
        // ── BLIND SPOTS & MOTIVATION ─────────────────────────
        [
            'key' => 'blindspot_block',
            'section' => 'Điểm Mù & Rào Cản',
            'type' => 'choice',
            'text' => 'Điều gì thường cản trở bạn nhiều nhất dù bạn đã biết nó tồn tại?',
            'options' => [
                'a' => 'Sợ thất bại hoặc bị đánh giá — nên trì hoãn bắt đầu',
                'b' => 'Quá nhiều ý tưởng — khó tập trung vào một thứ',
                'c' => 'Khó nói không — nhận quá nhiều việc rồi kiệt sức',
                'd' => 'Cần mọi thứ hoàn hảo — mất nhiều thời gian hơn cần thiết',
            ],
        ],
        [
            'key' => 'motivation_core',
            'section' => 'Động Lực Sâu',
            'type' => 'choice',
            'text' => 'Trong lòng, điều gì thực sự thúc đẩy bạn hành động?',
            'options' => [
                'a' => 'Mong muốn được công nhận và có địa vị',
                'b' => 'Khát khao tạo ra tác động thực sự cho người khác',
                'c' => 'Nhu cầu tự do, độc lập và làm chủ cuộc đời',
                'd' => 'Đam mê phát triển và trở thành phiên bản tốt nhất',
            ],
        ],
        [
            'key' => 'motivation_incongruent',
            'section' => 'Động Lực Sâu',
            'type' => 'choice',
            'text' => 'Khi nào bạn cảm thấy chưa sống đúng với bản thân nhất?',
            'options' => [
                'a' => 'Khi làm việc chỉ vì tiền mà không có ý nghĩa',
                'b' => 'Khi phải giả vờ đồng ý dù trong lòng không đồng thuận',
                'c' => 'Khi bị kiểm soát quá mức và không có tự chủ',
                'd' => 'Khi bị mắc kẹt trong lặp lại, không có gì mới để học',
            ],
        ],
        // ── LIFE WHEEL ──────────────────────────────────────
        [
            'key' => 'wheel_career',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng hiện tại với SỰ NGHIỆP & CÔNG VIỆC?',
            'label' => 'Sự Nghiệp',
            'min' => 1, 'max' => 10,
        ],
        [
            'key' => 'wheel_health',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng với SỨC KHỎE & NĂNG LƯỢNG?',
            'label' => 'Sức Khỏe',
            'min' => 1, 'max' => 10,
        ],
        [
            'key' => 'wheel_relationships',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng với MỐI QUAN HỆ & GIA ĐÌNH?',
            'label' => 'Mối Quan Hệ',
            'min' => 1, 'max' => 10,
        ],
        [
            'key' => 'wheel_finance',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng với TÀI CHÍNH & VẬT CHẤT?',
            'label' => 'Tài Chính',
            'min' => 1, 'max' => 10,
        ],
        [
            'key' => 'wheel_growth',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng với HỌC TẬP & PHÁT TRIỂN BẢN THÂN?',
            'label' => 'Phát Triển',
            'min' => 1, 'max' => 10,
        ],
        // ── GROWTH DIRECTION ────────────────────────────────
        [
            'key' => 'growth_vision',
            'section' => 'Hướng Phát Triển',
            'type' => 'text',
            'text' => 'Nếu không có nỗi sợ nào cản trở, điều đầu tiên bạn sẽ làm hoặc trở thành là gì?',
            'placeholder' => 'Viết tự do, không có câu trả lời đúng hay sai...',
        ],
    ];

    if ($type === 'quick') return $quick;

    // Deep Discovery = Quick Scan + additional deep questions
    $deep_extra = [
        // ── IDENTITY DEEP ───────────────────────────────────
        [
            'key' => 'identity_values',
            'section' => 'Nhận Diện Bản Thân',
            'type' => 'multi',
            'text' => 'Chọn 3 giá trị cốt lõi quan trọng nhất với bạn:',
            'options' => [
                'a' => 'Tự do & Tự chủ',
                'b' => 'Sự xuất sắc & Tiêu chuẩn cao',
                'c' => 'Kết nối & Thuộc về',
                'd' => 'Tác động & Để lại di sản',
                'e' => 'Trung thực & Chính trực',
                'f' => 'An toàn & Ổn định',
            ],
            'max_select' => 3,
        ],
        [
            'key' => 'identity_wound',
            'section' => 'Nhận Diện Bản Thân',
            'type' => 'choice',
            'text' => 'Trải nghiệm nào trong quá khứ ảnh hưởng lớn nhất đến cách bạn ra quyết định hiện tại?',
            'options' => [
                'a' => 'Từng bị phê phán hoặc từ chối khi thử điều mới',
                'b' => 'Từng thất bại nặng nề dù đã rất cố gắng',
                'c' => 'Từng bị người tin tưởng phản bội',
                'd' => 'Từng bị bỏ qua hoặc không được công nhận',
            ],
        ],
        [
            'key' => 'identity_shadow',
            'section' => 'Nhận Diện Bản Thân',
            'type' => 'choice',
            'text' => 'Điều gì ở người khác khiến bạn khó chịu nhất — thường là gương phản chiếu bản thân?',
            'options' => [
                'a' => 'Sự thiếu trách nhiệm và lười biếng',
                'b' => 'Thái độ khoe khoang và tự cao',
                'c' => 'Sự yếu đuối và dựa dẫm',
                'd' => 'Sự cứng nhắc và thiếu sáng tạo',
            ],
        ],
        // ── STRENGTHS DEEP ──────────────────────────────────
        [
            'key' => 'strength_overused',
            'section' => 'Điểm Mạnh Cốt Lõi',
            'type' => 'choice',
            'text' => 'Điểm mạnh nào của bạn đôi khi trở thành điểm yếu khi dùng quá mức?',
            'options' => [
                'a' => 'Tư duy chiến lược → Phân tích quá nhiều, không hành động',
                'b' => 'Đồng cảm → Gánh vác vấn đề của người khác, bỏ quên bản thân',
                'c' => 'Tập trung kết quả → Bỏ qua cảm xúc và quan hệ',
                'd' => 'Tò mò học hỏi → Bắt đầu nhiều thứ nhưng không hoàn thành',
            ],
        ],
        [
            'key' => 'strength_energy',
            'section' => 'Điểm Mạnh Cốt Lõi',
            'type' => 'choice',
            'text' => 'Loại công việc nào khiến bạn "quên thời gian" — đây là clue về sức mạnh thiên phú:',
            'options' => [
                'a' => 'Thiết kế, lên kế hoạch, xây dựng framework',
                'b' => 'Coaching, mentoring, chia sẻ với người khác',
                'c' => 'Triển khai, vận hành, tối ưu quy trình',
                'd' => 'Nghiên cứu, viết, phân tích dữ liệu',
            ],
        ],
        // ── BLIND SPOTS DEEP ────────────────────────────────
        [
            'key' => 'blind_trigger',
            'section' => 'Điểm Mù & Rào Cản',
            'type' => 'choice',
            'text' => 'Tình huống nào kích hoạt phản ứng cảm xúc mạnh nhất ở bạn?',
            'options' => [
                'a' => 'Bị phê phán trước mặt người khác',
                'b' => 'Dự án bị hủy hoặc công sức không được ghi nhận',
                'c' => 'Cảm thấy mất kiểm soát hoặc bị thao túng',
                'd' => 'Bị buộc phải làm theo cách mà bạn biết là sai',
            ],
        ],
        [
            'key' => 'blind_sabotage',
            'section' => 'Điểm Mù & Rào Cản',
            'type' => 'choice',
            'text' => 'Bạn tự phá hoại bản thân như thế nào khi gần đến thành công?',
            'options' => [
                'a' => 'Tự đặt tiêu chuẩn cao hơn → trì hoãn launch',
                'b' => 'Bắt đầu dự án mới ngay khi cái cũ gần xong',
                'c' => 'Thu mình lại khi spotlight hướng về phía mình',
                'd' => 'Tìm lý do để không cam kết hoàn toàn',
            ],
        ],
        [
            'key' => 'blind_feedback',
            'section' => 'Điểm Mù & Rào Cản',
            'type' => 'choice',
            'text' => 'Khi nhận phản hồi tiêu cực, phản ứng đầu tiên của bạn thường là...?',
            'options' => [
                'a' => 'Giải thích và bảo vệ quan điểm của mình',
                'b' => 'Im lặng bên ngoài nhưng tổn thương bên trong',
                'c' => 'Đồng ý ngay để tránh xung đột',
                'd' => 'Phân tích xem phản hồi đó có hợp lý không',
            ],
        ],
        // ── MOTIVATION DEEP ─────────────────────────────────
        [
            'key' => 'motivation_fear',
            'section' => 'Động Lực Sâu',
            'type' => 'choice',
            'text' => 'Nỗi sợ nào ngầm định đang ảnh hưởng đến các quyết định của bạn?',
            'options' => [
                'a' => 'Sợ không đủ giỏi / không xứng đáng',
                'b' => 'Sợ bị bỏ lại một mình / mất đi kết nối',
                'c' => 'Sợ mất kiểm soát hoặc bị phụ thuộc',
                'd' => 'Sợ tẻ nhạt / không có ý nghĩa',
            ],
        ],
        [
            'key' => 'motivation_legacy',
            'section' => 'Động Lực Sâu',
            'type' => 'text',
            'text' => 'Bạn muốn được nhớ đến với điều gì sau 20 năm nữa?',
            'placeholder' => 'Di sản, tác động, hoặc điều bạn tạo ra cho thế giới...',
        ],
        // ── LIFE WHEEL DEEP ─────────────────────────────────
        [
            'key' => 'wheel_fun',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng với NIỀM VUI & GIẢI TRÍ?',
            'label' => 'Niềm Vui',
            'min' => 1, 'max' => 10,
        ],
        [
            'key' => 'wheel_purpose',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng với MỤC ĐÍCH & TÂM LINH?',
            'label' => 'Mục Đích',
            'min' => 1, 'max' => 10,
        ],
        [
            'key' => 'wheel_environment',
            'section' => 'Bánh Xe Cuộc Sống',
            'type' => 'slider',
            'text' => 'Mức độ hài lòng với MÔI TRƯỜNG SỐNG & LÀM VIỆC?',
            'label' => 'Môi Trường',
            'min' => 1, 'max' => 10,
        ],
        // ── INTERNAL CONFLICT ───────────────────────────────
        [
            'key' => 'conflict_values',
            'section' => 'Xung Đột Nội Tâm',
            'type' => 'choice',
            'text' => 'Cặp giá trị nào thường tạo ra căng thẳng nhất trong bạn?',
            'options' => [
                'a' => 'Tự do vs An toàn — muốn phiêu lưu nhưng sợ bất ổn',
                'b' => 'Tham vọng vs Điều độ — muốn thành công nhưng sợ kiệt sức',
                'c' => 'Cho đi vs Nhận lại — chăm sóc người khác nhưng quên bản thân',
                'd' => 'Cầu toàn vs Hành động — muốn hoàn hảo nhưng cần tốc độ',
            ],
        ],
        [
            'key' => 'conflict_identity',
            'section' => 'Xung Đột Nội Tâm',
            'type' => 'choice',
            'text' => 'Khoảng cách lớn nhất giữa "con người bạn đang là" và "con người bạn muốn là" là gì?',
            'options' => [
                'a' => 'Tôi muốn tự tin hơn / ít nghi ngờ bản thân hơn',
                'b' => 'Tôi muốn kỷ luật hơn / hành động nhất quán hơn',
                'c' => 'Tôi muốn can đảm hơn / sẵn sàng chấp nhận rủi ro',
                'd' => 'Tôi muốn bình an hơn / ít bị cuốn vào lo âu',
            ],
        ],
        [
            'key' => 'conflict_unresolved',
            'section' => 'Xung Đột Nội Tâm',
            'type' => 'text',
            'text' => 'Điều gì bạn biết mình cần thay đổi nhưng vẫn chưa làm — và lý do thực sự là gì?',
            'placeholder' => 'Hãy thành thật với bản thân...',
        ],
        // ── GROWTH DIRECTION DEEP ───────────────────────────
        [
            'key' => 'growth_obstacles',
            'section' => 'Hướng Phát Triển',
            'type' => 'choice',
            'text' => 'Rào cản thực sự lớn nhất ngăn bạn đạt đến mục tiêu đó là gì?',
            'options' => [
                'a' => 'Thiếu kỹ năng hoặc kiến thức cụ thể',
                'b' => 'Thiếu hệ thống, kế hoạch và kỷ luật',
                'c' => 'Thiếu kết nối đúng người hoặc môi trường hỗ trợ',
                'd' => 'Thiếu dũng khí để cam kết và chịu rủi ro',
            ],
        ],
        [
            'key' => 'growth_commitment',
            'section' => 'Hướng Phát Triển',
            'type' => 'choice',
            'text' => 'Trong 90 ngày tới, bạn sẵn sàng đầu tư gì nhất để phá vỡ rào cản đó?',
            'options' => [
                'a' => 'Học một kỹ năng hoặc kiến thức mới mỗi tuần',
                'b' => 'Xây dựng một thói quen hàng ngày và giữ 90 ngày',
                'c' => 'Chủ động kết nối với 3 người có thể giúp tôi tiến xa hơn',
                'd' => 'Cam kết một quyết định táo bạo mà tôi đã trì hoãn',
            ],
        ],
        [
            'key' => 'growth_support',
            'section' => 'Hướng Phát Triển',
            'type' => 'text',
            'text' => 'Loại hỗ trợ nào bạn nghĩ mình cần nhất lúc này?',
            'placeholder' => 'Mentoring, cộng đồng, công cụ, kiến thức, hay điều gì khác...',
        ],
    ];

    return array_merge($quick, $deep_extra);
}

// Chibi Animal Derby - Animal Definitions and SVG Generator

const ANIMAL_SPECIES = [
  { id: 1, name: "Thỏ Bông", type: "rabbit", color: "#FFD1DC", accent: "#FFB7C5", text: "#6b424a" },
  { id: 2, name: "Mèo Mun", type: "cat", color: "#F0E6D2", accent: "#E1C699", text: "#4a3c31" },
  { id: 3, name: "Cún Ú", type: "dog", color: "#FFE5B4", accent: "#F3D299", text: "#5c4033" },
  { id: 4, name: "Gấu Trúc", type: "panda", color: "#FFFFFF", accent: "#3D3D3D", text: "#1a1a1a" },
  { id: 5, name: "Gấu Nâu", type: "bear", color: "#E8C39E", accent: "#C69C72", text: "#4a3525" },
  { id: 6, name: "Koala Xám", type: "koala", color: "#D1D5DB", accent: "#9CA3AF", text: "#374151" },
  { id: 7, name: "Heo Hồng", type: "pig", color: "#FFC6FF", accent: "#FF99FF", text: "#660066" },
  { id: 8, name: "Ếch Cốm", type: "frog", color: "#CAFFBF", accent: "#A3E49E", text: "#1b4314" },
  { id: 9, name: "Gà Chíp", type: "chick", color: "#FDFFB6", accent: "#FFD43B", text: "#6e5200" },
  { id: 10, name: "Cáo Lửa", type: "fox", color: "#FFADAD", accent: "#FF8B8B", text: "#7a2a2a" },
  { id: 11, name: "Cánh Cụt", type: "penguin", color: "#BDE0FE", accent: "#A2D2FF", text: "#1e3d59" },
  { id: 12, name: "Hổ Con", type: "tiger", color: "#FFD8A8", accent: "#FFA94D", text: "#5c3100" },
  { id: 13, name: "Khỉ Nâu", type: "monkey", color: "#E3C1B4", accent: "#C19A8C", text: "#4c3228" },
  { id: 14, name: "Cừu Bông", type: "sheep", color: "#F8F9FA", accent: "#E9ECEF", text: "#495057" },
  { id: 15, name: "Bò Sữa", type: "cow", color: "#FFF9DB", accent: "#FFE066", text: "#5c4d00" },
  { id: 16, name: "Voi Đỏ", type: "elephant", color: "#D8F3DC", accent: "#B7E4C7", text: "#1b4332" },
  { id: 17, name: "Sư Tử", type: "lion", color: "#FFE3E3", accent: "#FFA8A8", text: "#6b2d2d" },
  { id: 18, name: "Cú Vọ", type: "owl", color: "#E8E8E8", accent: "#D6D6D6", text: "#3a3a3a" },
  { id: 19, name: "Khủng Long", type: "dino", color: "#D8F3DC", accent: "#95D5A4", text: "#081c15" },
  { id: 20, name: "Kỳ Lân", type: "unicorn", color: "#F3E8FF", accent: "#E9D5FF", text: "#581c87" },
  { id: 21, name: "Nai Tơ", type: "deer", color: "#F7D6C8", accent: "#E5A995", text: "#4e2416" },
  { id: 22, name: "Hà Mã", type: "hippo", color: "#E0C3FC", accent: "#C77DFF", text: "#3c096c" },
  { id: 23, name: "Chuột Nhắt", type: "mouse", color: "#E9ECEF", accent: "#CED4DA", text: "#343a40" },
  { id: 24, name: "Gấu Trộm", type: "raccoon", color: "#CED4DA", accent: "#6C757D", text: "#212529" },
  { id: 25, name: "Sóc Đỏ", type: "squirrel", color: "#FFD8A8", accent: "#FFA94D", text: "#5c3100" },
  { id: 26, name: "Ong Vàng", type: "bee", color: "#FFF3BF", accent: "#FFD43B", text: "#5c4d00" },
  { id: 27, name: "Bọ Rùa", type: "ladybug", color: "#FFC9C9", accent: "#FA5252", text: "#5c0000" },
  { id: 28, name: "Rùa Xanh", type: "turtle", color: "#D8F3DC", accent: "#74C0FC", text: "#0b3d59" },
  { id: 29, name: "Cua Đỏ", type: "crab", color: "#FFE3E3", accent: "#FF8787", text: "#660000" },
  { id: 30, name: "Bạch Tuộc", type: "octopus", color: "#F3F0FF", accent: "#D0BFFF", text: "#4c0519" },
  { id: 31, name: "Cá Voi", type: "whale", color: "#E2F0FF", accent: "#90CAF9", text: "#0d47a1" },
  { id: 32, name: "Cá Mập", type: "shark", color: "#E3F2FD", accent: "#90CAF9", text: "#0d47a1" },
  { id: 33, name: "Rái Cá", type: "otter", color: "#ECE4DB", accent: "#D5C7B7", text: "#4d3a29" },
  { id: 34, name: "Gấu Đỏ", type: "redpanda", color: "#FCE8E6", accent: "#F28B82", text: "#611a15" },
  { id: 35, name: "Thú Mỏ Vịt", type: "platypus", color: "#E0F2F1", accent: "#26A69A", text: "#004d40" },
  { id: 36, name: "Lạc Đà", type: "alpaca", color: "#FFF9DB", accent: "#F5F5F5", text: "#424242" },
  { id: 37, name: "Cáo Tuyết", type: "arcticfox", color: "#F8F9FA", accent: "#E3F2FD", text: "#1a365d" },
  { id: 38, name: "Mèo Tam Thể", type: "calicocat", color: "#FFF4E6", accent: "#FFA8A8", text: "#5c2a18" },
  { id: 39, name: "Husky Ngáo", type: "husky", color: "#ECEFF1", accent: "#B0BEC5", text: "#263238" },
  { id: 40, name: "Khủng Long Hồng", type: "axolotl", color: "#FFE3EC", accent: "#FF85A1", text: "#600020" }
];

// Stats lists to make cosmetic profiles
const FAVORITE_FOODS = [
  "Cà rốt ngọt", "Cá hồi tươi", "Xương gặm", "Trúc non", "Mật ong rừng", "Lá bạch đàn",
  "Táo đỏ", "Ruồi béo", "Thóc vàng", "Dâu tây", "Tép khô", "Thịt bò tơ", "Chuối chín",
  "Cỏ non", "Cỏ tươi", "Dưa hấu", "Thịt tươi", "Hạt dẻ", "Lá cây", "Kẹo ngọt", "Quả mọng",
  "Rong biển", "Phô mai", "Cá thu", "Hạt sồi", "Phấn hoa", "Rệp cây", "Sứa biển", "Nghêu sò",
  "Tôm nhỏ", "Sinh vật phù du", "Cá mồi", "Cua đá", "Trái cây rừng", "Ốc sên", "Cỏ khô",
  "Tuyết đá", "Cá ngừ", "Bánh thưởng", "Sâu béo"
];

const ANIMAL_BIOS = [
  "Thích chạy nhảy tung tăng khắp đồng cỏ.",
  "Chuyên gia ngủ nướng nhưng khi chạy thì cực nhanh.",
  "Trung thành, năng động, thích đuổi bắt bóng tròn.",
  "Lười ăn lười làm nhưng cực kỳ đáng yêu.",
  "Trông to béo vậy thôi chứ bứt tốc đáng nể lắm đó.",
  "Bám chặt vào mục tiêu và không bao giờ bỏ cuộc.",
  "Vừa chạy vừa nghĩ về bữa ăn tối thịnh soạn.",
  "Nhảy cóc liên tục, siêu năng lực ẩn giấu.",
  "Nhỏ bé nhưng tinh ranh, bước chạy líu ríu nhanh nhẹn.",
  "Nhanh như một tia chớp, thông minh lém lỉnh.",
  "Lướt bằng bụng là vũ khí bí mật trên đường đua.",
  "Chúa tể đường đua tương lai, gầm rú dũng mãnh.",
  "Leo trèo nhào lộn, quậy phá tung trời đất.",
  "Nhẹ nhàng và mềm mại, lướt đi như một đám mây.",
  "Sức mạnh bền bỉ từ sữa tươi nguyên chất.",
  "Bền bỉ vô song, mỗi bước chân rung chuyển mặt đất.",
  "Uy nghiêm bước ra đường chạy với bờm tóc bồng bềnh.",
  "Tầm nhìn đêm tuyệt hảo, bay là là mặt đất.",
  "Thừa hưởng gen chạy của khủng long bạo chúa.",
  "Sức mạnh ma thuật từ sừng vàng may mắn.",
  "Nhẹ nhàng uyển chuyển nhảy qua mọi chướng ngại vật.",
  "Trông nặng nề thế thôi chứ bơi và chạy siêu đỉnh.",
  "Nhỏ con dễ lách qua khe hẹp đường đua.",
  "Đeo mặt nạ ngầu lòi, chiến thuật gia tài ba.",
  "Tích trữ hạt dẻ lấy năng lượng bứt phá.",
  "Vừa bay vừa vo ve tạo áp lực cho đối thủ.",
  "Mang lại may mắn cho bất kỳ ai cổ vũ.",
  "Chậm mà chắc, bền bỉ lội ngược dòng.",
  "Đi ngang nhưng luôn tiến thẳng về đích.",
  "Tám vòi bơi cực nhanh, uyển chuyển luồn lách.",
  "Đại dương xanh vẫy gọi, thổi nước phun trào.",
  "Hung thần tốc độ, hàm răng cười siêu thân thiện.",
  "Cực kỳ ham chơi, trượt nước điêu luyện.",
  "Chú gấu đỏ lông xù, chuyên gia leo trèo đáng yêu.",
  "Mỏ vịt đuôi dẹt, đi lạch bạch vô cùng giải trí.",
  "Fluffy vô đối, kiêu hãnh ngẩng cao đầu chạy.",
  "Bộ lông trắng tinh khôi hòa vào tuyết trắng.",
  "Mèo ba màu may mắn, bước chạy linh hoạt.",
  "Ngáo ngơ nghịch ngợm nhưng thể lực vô biên.",
  "Quái vật hồng dễ thương đến từ hành tinh khác."
];

// Combine profiles (dynamically generate 200 animal participants)
const ANIMALS = [];
for (let i = 1; i <= 200; i++) {
  const template = ANIMAL_SPECIES[(i - 1) % ANIMAL_SPECIES.length];
  
  // Generate repeatable pseudo-random stats based on ID
  const speed = (i % 3) + 3; // 3 to 5
  const accel = ((i + 2) % 3) + 3; // 3 to 5
  const luck = ((i * 7) % 3) + 3; // 3 to 5
  
  const bioIndex = (i - 1) % ANIMAL_BIOS.length;
  const foodIndex = (i - 1) % FAVORITE_FOODS.length;
  
  ANIMALS.push({
    id: i,
    name: `${template.name} #${i}`,
    type: template.type,
    color: template.color,
    accent: template.accent,
    text: template.text,
    bio: ANIMAL_BIOS[bioIndex] || "Một đấu thủ đáng gờm trên đường đua.",
    favFood: FAVORITE_FOODS[foodIndex] || "Đồ ăn ngon",
    stats: { speed, accel, luck }
  });
}

function getAnimal(id) {
  return ANIMALS.find(a => a.id === parseInt(id)) || ANIMALS[0];
}

// Procedural SVG Renderer
// state: 'idle', 'running', 'sleepy', 'boosting', 'winner'
// frame: dynamic number for animation cycles
function renderAnimalSVG(id, state = 'idle', frame = 0) {
  const animal = getAnimal(id);
  const color = animal.color;
  const accent = animal.accent;
  
  // Animation variables
  let bounceY = 0;
  let legAngleL = 0;
  let legAngleR = 0;
  let eyeScaleY = 1; // For blinking
  let wingWiggle = 0;
  let tailWiggle = 0;
  
  if (state === 'running') {
    bounceY = Math.abs(Math.sin(frame * 0.4)) * -5;
    legAngleL = Math.sin(frame * 0.4) * 25;
    legAngleR = -Math.sin(frame * 0.4) * 25;
    tailWiggle = Math.sin(frame * 0.6) * 10;
  } else if (state === 'boosting') {
    bounceY = Math.abs(Math.sin(frame * 0.7)) * -8;
    legAngleL = Math.sin(frame * 0.7) * 35;
    legAngleR = -Math.sin(frame * 0.7) * 35;
    tailWiggle = Math.sin(frame * 1.2) * 15;
  } else if (state === 'winner') {
    bounceY = Math.sin(frame * 0.2) * 6 - 4; // Bouncing up and down in excitement
    legAngleL = Math.sin(frame * 0.3) * 15;
    legAngleR = -Math.sin(frame * 0.3) * 15;
  } else if (state === 'sleepy') {
    bounceY = Math.sin(frame * 0.05) * 1.5; // Slow breathing bounce
    legAngleL = 5;
    legAngleR = -5;
  } else {
    // Idle blinking
    if (frame % 60 < 4) {
      eyeScaleY = 0.1; // Blink
    }
  }

  // Wing animation for birds/insects
  if (animal.type === 'chick' || animal.type === 'penguin' || animal.type === 'owl' || animal.type === 'bee') {
    wingWiggle = Math.sin(frame * 0.5) * 15;
  }

  // Draw Specific Features Based on Type
  let earHTML = '';
  let snoutHTML = '';
  let tailHTML = '';
  let specialFeaturesHTML = '';
  
  const mainColorHex = color;
  const accentColorHex = accent;

  switch (animal.type) {
    case 'rabbit':
      // Long ears
      earHTML = `
        <rect x="22" y="10" width="10" height="24" rx="5" fill="${mainColorHex}" transform="translate(${bounceY * 0.2}, ${bounceY * 0.5}) rotate(-5, 27, 22)"/>
        <rect x="25" y="14" width="4" height="16" rx="2" fill="${accentColorHex}" transform="translate(${bounceY * 0.2}, ${bounceY * 0.5}) rotate(-5, 27, 22)"/>
        <rect x="48" y="10" width="10" height="24" rx="5" fill="${mainColorHex}" transform="translate(${-bounceY * 0.2}, ${bounceY * 0.5}) rotate(5, 53, 22)"/>
        <rect x="51" y="14" width="4" height="16" rx="2" fill="${accentColorHex}" transform="translate(${-bounceY * 0.2}, ${bounceY * 0.5}) rotate(5, 53, 22)"/>
      `;
      // Small round fluffy tail
      tailHTML = `<circle cx="15" cy="58" r="5" fill="#FFFFFF" transform="rotate(${tailWiggle}, 15, 58)"/>`;
      break;
      
    case 'cat':
    case 'calicocat':
      // Pointy ears
      earHTML = `
        <polygon points="20,32 15,12 32,25" fill="${mainColorHex}"/>
        <polygon points="22,29 18,16 30,24" fill="${accentColorHex}"/>
        <polygon points="60,32 65,12 48,25" fill="${mainColorHex}"/>
        <polygon points="58,29 62,16 50,24" fill="${accentColorHex}"/>
      `;
      // Whiskers
      specialFeaturesHTML = `
        <line x1="12" y1="46" x2="3" y2="44" stroke="#4a3c31" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="12" y1="49" x2="2" y2="49" stroke="#4a3c31" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="12" y1="52" x2="4" y2="54" stroke="#4a3c31" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="68" y1="46" x2="77" y2="44" stroke="#4a3c31" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="68" y1="49" x2="78" y2="49" stroke="#4a3c31" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="68" y1="52" x2="76" y2="54" stroke="#4a3c31" stroke-width="1.5" stroke-linecap="round"/>
      `;
      // Long tail
      tailHTML = `<path d="M 16,56 Q 8,56 6,42" fill="none" stroke="${mainColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      if (animal.type === 'calicocat') {
        // Orange and black patches
        specialFeaturesHTML += `
          <circle cx="28" cy="40" r="10" fill="#FFA94D" opacity="0.8"/>
          <circle cx="56" cy="48" r="8" fill="#4A4A4A" opacity="0.8"/>
        `;
      }
      break;

    case 'dog':
    case 'husky':
      if (animal.type === 'dog') {
        // Floppy ears
        earHTML = `
          <rect x="15" y="24" width="8" height="20" rx="4" fill="${accentColorHex}" transform="rotate(15, 19, 24)"/>
          <rect x="57" y="24" width="8" height="20" rx="4" fill="${accentColorHex}" transform="rotate(-15, 61, 24)"/>
        `;
        tailHTML = `<path d="M 16,56 C 10,54 8,46 10,40" fill="none" stroke="${mainColorHex}" stroke-width="4" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      } else { // husky
        // Husky ears
        earHTML = `
          <polygon points="18,32 12,12 30,25" fill="#78909C"/>
          <polygon points="20,29 15,16 28,24" fill="#FFFFFF"/>
          <polygon points="62,32 68,12 50,25" fill="#78909C"/>
          <polygon points="60,29 65,16 52,24" fill="#FFFFFF"/>
        `;
        // Husky facial mask
        specialFeaturesHTML = `
          <path d="M 22,35 C 22,25 35,28 40,36 C 45,28 58,25 58,35 C 58,45 54,54 40,54 C 26,54 22,45 22,35 Z" fill="#FFFFFF" opacity="0.95"/>
        `;
        tailHTML = `<path d="M 16,56 Q 8,50 12,42" fill="none" stroke="#78909C" stroke-width="5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      }
      break;

    case 'panda':
      // Black ears, eye patches
      earHTML = `
        <circle cx="24" cy="22" r="8" fill="${accentColorHex}"/>
        <circle cx="56" cy="22" r="8" fill="${accentColorHex}"/>
      `;
      specialFeaturesHTML = `
        <ellipse cx="32" cy="42" rx="7" ry="5" fill="${accentColorHex}" transform="rotate(-15, 32, 42)"/>
        <ellipse cx="48" cy="42" rx="7" ry="5" fill="${accentColorHex}" transform="rotate(15, 48, 42)"/>
        <circle cx="34" cy="41" r="2" fill="#FFFFFF"/>
        <circle cx="46" cy="41" r="2" fill="#FFFFFF"/>
      `;
      break;

    case 'bear':
    case 'koala':
      if (animal.type === 'bear') {
        earHTML = `
          <circle cx="24" cy="22" r="7.5" fill="${mainColorHex}"/>
          <circle cx="24" cy="22" r="4" fill="${accentColorHex}"/>
          <circle cx="56" cy="22" r="7.5" fill="${mainColorHex}"/>
          <circle cx="56" cy="22" r="4" fill="${accentColorHex}"/>
        `;
      } else { // koala
        // Big fluffy ears
        earHTML = `
          <circle cx="20" cy="24" r="11" fill="${mainColorHex}"/>
          <circle cx="20" cy="24" r="7" fill="#ECEFF1"/>
          <circle cx="60" cy="24" r="11" fill="${mainColorHex}"/>
          <circle cx="60" cy="24" r="7" fill="#ECEFF1"/>
        `;
        // Big dark nose
        snoutHTML = `<ellipse cx="40" cy="45" rx="5" ry="8" fill="#374151"/>`;
      }
      break;

    case 'pig':
      earHTML = `
        <polygon points="22,26 15,18 28,20" fill="${mainColorHex}"/>
        <polygon points="58,26 65,18 52,20" fill="${mainColorHex}"/>
      `;
      snoutHTML = `
        <rect x="34" y="44" width="12" height="8" rx="4" fill="${accentColorHex}"/>
        <circle cx="37" cy="48" r="1.5" fill="#800080" opacity="0.6"/>
        <circle cx="43" cy="48" r="1.5" fill="#800080" opacity="0.6"/>
      `;
      tailHTML = `<path d="M 17,56 Q 12,58 14,52 T 11,50" fill="none" stroke="${accentColorHex}" stroke-width="2.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 17, 56)"/>`;
      break;

    case 'frog':
      // Big top eyes
      earHTML = `
        <circle cx="28" cy="26" r="8.5" fill="${mainColorHex}"/>
        <circle cx="52" cy="26" r="8.5" fill="${mainColorHex}"/>
        <circle cx="28" cy="26" r="5" fill="#FFFFFF"/>
        <circle cx="52" cy="26" r="5" fill="#FFFFFF"/>
        <circle cx="28" cy="26" r="2.5" fill="#000000"/>
        <circle cx="52" cy="26" r="2.5" fill="#000000"/>
      `;
      // Wide mouth
      specialFeaturesHTML = `<path d="M 33,48 Q 40,54 47,48" fill="none" stroke="#1b4314" stroke-width="2" stroke-linecap="round"/>`;
      break;

    case 'chick':
    case 'penguin':
    case 'owl':
      // Chick / Penguin / Owl (Birds)
      if (animal.type === 'chick') {
        // Little sprout/feather on top
        earHTML = `<path d="M 40,24 Q 40,15 44,14 Q 42,22 40,24" fill="#FFA94D"/>`;
        snoutHTML = `<polygon points="36,44 44,44 40,51" fill="#FF922B"/>`; // Orange beak
      } else if (animal.type === 'penguin') {
        // Black mask/face split
        specialFeaturesHTML = `
          <ellipse cx="40" cy="42" rx="19" ry="17" fill="#FFFFFF"/>
        `;
        snoutHTML = `<polygon points="37,43 43,43 40,49" fill="#FF922B"/>`;
      } else if (animal.type === 'owl') {
        // Big round eyes mask
        specialFeaturesHTML = `
          <circle cx="31" cy="40" r="9" fill="#FFFFFF" opacity="0.9"/>
          <circle cx="49" cy="40" r="9" fill="#FFFFFF" opacity="0.9"/>
          <path d="M 36,49 C 38,47 42,47 44,49" fill="none" stroke="#3a3a3a" stroke-width="1.5" stroke-linecap="round"/>
        `;
        snoutHTML = `<polygon points="38,42 42,42 40,47" fill="#E67E22"/>`;
        // Horns/tufts
        earHTML = `
          <polygon points="20,28 17,16 28,26" fill="${mainColorHex}"/>
          <polygon points="60,28 63,16 52,26" fill="${mainColorHex}"/>
        `;
      }
      break;

    case 'fox':
    case 'arcticfox':
      // Fox ears & tail & muzzle
      earHTML = `
        <polygon points="20,30 14,8 30,22" fill="${mainColorHex}"/>
        <polygon points="21,27 17,13 28,21" fill="${accentColorHex}"/>
        <polygon points="60,30 66,8 50,22" fill="${mainColorHex}"/>
        <polygon points="59,27 63,13 52,21" fill="${accentColorHex}"/>
      `;
      // White muzzle
      specialFeaturesHTML = `
        <path d="M 23,45 C 23,38 30,42 40,42 C 50,42 57,38 57,45 C 57,51 49,53 40,53 C 31,53 23,51 23,45 Z" fill="#FFFFFF" opacity="0.9"/>
      `;
      snoutHTML = `<polygon points="38,44 42,44 40,47" fill="#2E2E2E"/>`;
      // Huge bushy tail
      tailHTML = `<path d="M 16,54 C 6,52 -2,40 2,32 C 6,36 10,46 18,48" fill="${mainColorHex}" transform="rotate(${tailWiggle}, 16, 54)"/>`;
      break;

    case 'tiger':
      // Ears
      earHTML = `
        <circle cx="24" cy="22" r="7.5" fill="${mainColorHex}"/>
        <circle cx="24" cy="22" r="4.5" fill="${accentColorHex}"/>
        <circle cx="56" cy="22" r="7.5" fill="${mainColorHex}"/>
        <circle cx="56" cy="22" r="4.5" fill="${accentColorHex}"/>
      `;
      // Stripes
      specialFeaturesHTML = `
        <!-- Side stripes -->
        <path d="M 16,36 L 24,38 L 16,40 Z" fill="#3a3a3a"/>
        <path d="M 16,46 L 23,47 L 16,49 Z" fill="#3a3a3a"/>
        <path d="M 64,36 L 56,38 L 64,40 Z" fill="#3a3a3a"/>
        <path d="M 64,46 L 57,47 L 64,49 Z" fill="#3a3a3a"/>
        <!-- Forehead stripes -->
        <path d="M 38,26 L 40,32 L 42,26 Z" fill="#3a3a3a"/>
        <path d="M 34,26 L 36,30 L 38,26 Z" fill="#3a3a3a"/>
        <path d="M 46,26 L 44,30 L 42,26 Z" fill="#3a3a3a"/>
      `;
      tailHTML = `<path d="M 16,56 Q 8,62 10,48" fill="none" stroke="${mainColorHex}" stroke-width="4" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      break;

    case 'monkey':
      // Big ears, peach round face mask
      earHTML = `
        <circle cx="16" cy="38" r="9" fill="${mainColorHex}"/>
        <circle cx="16" cy="38" r="5" fill="${accentColorHex}"/>
        <circle cx="64" cy="38" r="9" fill="${mainColorHex}"/>
        <circle cx="64" cy="38" r="5" fill="${accentColorHex}"/>
      `;
      specialFeaturesHTML = `
        <!-- Heart shaped face -->
        <path d="M 23,42 C 23,32 32,32 40,37 C 48,32 57,32 57,42 C 57,50 49,53 40,53 C 31,53 23,50 23,42 Z" fill="${accentColorHex}"/>
      `;
      tailHTML = `<path d="M 16,56 Q 4,58 6,44 Q 8,30 2,28" fill="none" stroke="${mainColorHex}" stroke-width="3" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      break;

    case 'sheep':
      // Fluffy wool on top
      earHTML = `
        <!-- Floppy ears underneath -->
        <ellipse cx="18" cy="36" rx="8" ry="3.5" fill="${accentColorHex}" transform="rotate(20, 18, 36)"/>
        <ellipse cx="62" cy="36" rx="8" ry="3.5" fill="${accentColorHex}" transform="rotate(-20, 62, 36)"/>
      `;
      specialFeaturesHTML = `
        <!-- Fluffy wool clouds -->
        <circle cx="30" cy="24" r="8" fill="#FFFFFF"/>
        <circle cx="40" cy="22" r="10" fill="#FFFFFF"/>
        <circle cx="50" cy="24" r="8" fill="#FFFFFF"/>
        <circle cx="24" cy="28" r="7" fill="#FFFFFF"/>
        <circle cx="56" cy="28" r="7" fill="#FFFFFF"/>
      `;
      break;

    case 'cow':
      // Horns, ears, snout
      earHTML = `
        <!-- Horns -->
        <path d="M 26,22 C 26,14 20,12 20,12 C 20,12 28,17 29,22 Z" fill="#E9ECEF"/>
        <path d="M 54,22 C 54,14 60,12 60,12 C 60,12 52,17 51,22 Z" fill="#E9ECEF"/>
        <!-- Ears -->
        <ellipse cx="16" cy="28" rx="8" ry="3.5" fill="${mainColorHex}" transform="rotate(-15, 16, 28)"/>
        <ellipse cx="64" cy="28" rx="8" ry="3.5" fill="${mainColorHex}" transform="rotate(15, 64, 28)"/>
      `;
      snoutHTML = `
        <rect x="31" y="44" width="18" height="10" rx="5" fill="#FFA8A8" opacity="0.8"/>
        <circle cx="36" cy="49" r="1.5" fill="#495057"/>
        <circle cx="44" cy="49" r="1.5" fill="#495057"/>
      `;
      // Cow spots
      specialFeaturesHTML = `
        <path d="M 21,34 Q 28,34 26,40 T 19,42 Z" fill="#495057" opacity="0.8"/>
        <path d="M 59,38 Q 53,42 55,46 T 61,44 Z" fill="#495057" opacity="0.8"/>
      `;
      break;

    case 'elephant':
      // Big ears, trunk
      earHTML = `
        <ellipse cx="14" cy="38" rx="14" ry="18" fill="${mainColorHex}"/>
        <ellipse cx="16" cy="38" rx="10" ry="14" fill="${accentColorHex}"/>
        <ellipse cx="66" cy="38" rx="14" ry="18" fill="${mainColorHex}"/>
        <ellipse cx="64" cy="38" rx="10" ry="14" fill="${accentColorHex}"/>
      `;
      snoutHTML = `
        <!-- Trunk -->
        <path d="M 40,43 Q 40,54 44,56 Q 47,56 46,51" fill="none" stroke="${mainColorHex}" stroke-width="5.5" stroke-linecap="round"/>
      `;
      break;

    case 'lion':
      // Big mane
      earHTML = `
        <!-- Ears hidden in mane -->
        <circle cx="23" cy="24" r="6" fill="${mainColorHex}"/>
        <circle cx="57" cy="24" r="6" fill="${mainColorHex}"/>
      `;
      specialFeaturesHTML = `
        <!-- Mane -->
        <path d="M 40,16 C 24,16 16,24 16,38 C 16,52 24,60 40,60 C 56,60 64,52 64,38 C 64,24 56,16 40,16 Z" fill="${accentColorHex}" opacity="0.9"/>
        <!-- Inner head overlay -->
        <ellipse cx="40" cy="40.5" rx="18.5" ry="16.5" fill="${mainColorHex}"/>
      `;
      snoutHTML = `<polygon points="38,44 42,44 40,47" fill="#495057"/>`;
      // Lion tail with tuft
      tailHTML = `
        <path d="M 16,56 Q 6,56 4,44" fill="none" stroke="${mainColorHex}" stroke-width="2.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>
        <circle cx="4" cy="44" r="3.5" fill="${accentColorHex}"/>
      `;
      break;

    case 'dino':
      // Spine plates on back/head
      specialFeaturesHTML = `
        <polygon points="34,22 40,14 46,22" fill="${accentColorHex}" transform="translate(0, ${bounceY})"/>
        <polygon points="26,24 30,18 36,25" fill="${accentColorHex}" transform="translate(0, ${bounceY})"/>
        <polygon points="46,25 50,18 54,24" fill="${accentColorHex}" transform="translate(0, ${bounceY})"/>
      `;
      tailHTML = `<path d="M 16,56 C 8,58 2,52 4,44" fill="none" stroke="${mainColorHex}" stroke-width="5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      break;

    case 'unicorn':
      // Horn, mane
      earHTML = `
        <polygon points="23,28 18,12 28,24" fill="${mainColorHex}"/>
        <polygon points="57,28 62,12 52,24" fill="${mainColorHex}"/>
        <!-- Horn -->
        <polygon points="37,20 40,2 43,20" fill="#FFE066" transform="translate(0, ${bounceY * 0.4})"/>
      `;
      // Pastel hair mane
      specialFeaturesHTML = `
        <path d="M 32,24 C 28,24 26,30 26,35 M 48,24 C 52,24 54,30 54,35" fill="none" stroke="#FFC6FF" stroke-width="3" stroke-linecap="round"/>
      `;
      tailHTML = `<path d="M 16,56 Q 8,58 10,48" fill="none" stroke="#BDE0FE" stroke-width="4.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      break;

    case 'deer':
      // Antlers, ears
      earHTML = `
        <!-- Tiny antlers -->
        <path d="M 27,22 L 23,12 M 25,17 L 20,18" stroke="#8B5A2B" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M 53,22 L 57,12 M 55,17 L 60,18" stroke="#8B5A2B" stroke-width="2.5" stroke-linecap="round"/>
        <!-- Ears -->
        <ellipse cx="18" cy="28" rx="8" ry="3" fill="${mainColorHex}" transform="rotate(-25, 18, 28)"/>
        <ellipse cx="62" cy="28" rx="8" ry="3" fill="${mainColorHex}" transform="rotate(25, 62, 28)"/>
      `;
      // White deer spots
      specialFeaturesHTML = `
        <circle cx="28" cy="35" r="1.5" fill="#FFFFFF"/>
        <circle cx="31" cy="33" r="1.5" fill="#FFFFFF"/>
        <circle cx="52" cy="35" r="1.5" fill="#FFFFFF"/>
        <circle cx="49" cy="33" r="1.5" fill="#FFFFFF"/>
      `;
      snoutHTML = `<polygon points="38,44 42,44 40,47" fill="#2E2E2E"/>`;
      break;

    case 'hippo':
      // Tiny ears, huge snout
      earHTML = `
        <circle cx="25" cy="24" r="4.5" fill="${mainColorHex}"/>
        <circle cx="55" cy="24" r="4.5" fill="${mainColorHex}"/>
      `;
      snoutHTML = `
        <rect x="26" y="42" width="28" height="12" rx="6" fill="${accentColorHex}"/>
        <circle cx="34" cy="48" r="2" fill="#495057" opacity="0.6"/>
        <circle cx="46" cy="48" r="2" fill="#495057" opacity="0.6"/>
      `;
      break;

    case 'mouse':
      // Huge ears
      earHTML = `
        <circle cx="20" cy="22" r="10" fill="${mainColorHex}"/>
        <circle cx="20" cy="22" r="6" fill="#FFC6FF"/>
        <circle cx="60" cy="22" r="10" fill="${mainColorHex}"/>
        <circle cx="60" cy="22" r="6" fill="#FFC6FF"/>
      `;
      snoutHTML = `<circle cx="40" cy="45" r="3" fill="#FFC6FF"/>`;
      // Long tail
      tailHTML = `<path d="M 16,56 Q 6,56 8,46 T 2,42" fill="none" stroke="${accentColorHex}" stroke-width="2" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      break;

    case 'raccoon':
      // Mask, stripe tail
      earHTML = `
        <polygon points="22,30 16,12 30,24" fill="${mainColorHex}"/>
        <polygon points="58,30 64,12 50,24" fill="${mainColorHex}"/>
      `;
      specialFeaturesHTML = `
        <!-- Mask -->
        <ellipse cx="28" cy="41" rx="9" ry="6" fill="${accentColorHex}" transform="rotate(-10, 28, 41)"/>
        <ellipse cx="52" cy="41" rx="9" ry="6" fill="${accentColorHex}" transform="rotate(10, 52, 41)"/>
      `;
      // Striped tail
      tailHTML = `
        <path d="M 16,56 Q 6,54 8,44" fill="none" stroke="${mainColorHex}" stroke-width="5.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>
        <path d="M 13,54 L 9,51" stroke="${accentColorHex}" stroke-width="3"/>
        <path d="M 11,48 L 7,45" stroke="${accentColorHex}" stroke-width="3"/>
      `;
      break;

    case 'squirrel':
      // Pointed ears with hair tufts, huge tail
      earHTML = `
        <polygon points="22,30 17,14 28,24" fill="${mainColorHex}"/>
        <polygon points="58,30 63,14 52,24" fill="${mainColorHex}"/>
        <circle cx="17" cy="14" r="1.5" fill="${accentColorHex}"/>
        <circle cx="63" cy="14" r="1.5" fill="${accentColorHex}"/>
      `;
      // Big fluffy tail curled up
      tailHTML = `
        <path d="M 16,56 C 4,56 -2,42 6,34 C 12,28 14,38 18,48" fill="${accentColorHex}" transform="rotate(${tailWiggle}, 16, 56)"/>
      `;
      break;

    case 'bee':
      // Antennae, stripes, tiny wings
      earHTML = `
        <!-- Antennae -->
        <path d="M 35,26 Q 32,14 28,15" fill="none" stroke="#212529" stroke-width="1.5"/>
        <circle cx="28" cy="15" r="2" fill="#212529"/>
        <path d="M 45,26 Q 48,14 52,15" fill="none" stroke="#212529" stroke-width="1.5"/>
        <circle cx="52" cy="15" r="2" fill="#212529"/>
      `;
      // Bee stripes
      specialFeaturesHTML = `
        <path d="M 23,40 H 57 M 21,46 H 59 M 22,52 H 58" stroke="#212529" stroke-width="3.5" stroke-linecap="round"/>
      `;
      // Tiny wings flapping
      tailHTML = `
        <ellipse cx="14" cy="38" rx="8" ry="5" fill="#E3F2FD" opacity="0.8" transform="rotate(${wingWiggle - 20}, 14, 38)"/>
        <ellipse cx="66" cy="38" rx="8" ry="5" fill="#E3F2FD" opacity="0.8" transform="rotate(${-wingWiggle + 20}, 66, 38)"/>
      `;
      break;

    case 'ladybug':
      // Red shell with black spots
      specialFeaturesHTML = `
        <!-- Center line -->
        <line x1="40" y1="32" x2="40" y2="57" stroke="#212529" stroke-width="2"/>
        <!-- Spots -->
        <circle cx="28" cy="38" r="3" fill="#212529"/>
        <circle cx="52" cy="38" r="3" fill="#212529"/>
        <circle cx="26" cy="48" r="3.5" fill="#212529"/>
        <circle cx="54" cy="48" r="3.5" fill="#212529"/>
        <circle cx="34" cy="52" r="2.5" fill="#212529"/>
        <circle cx="46" cy="52" r="2.5" fill="#212529"/>
      `;
      // Antennae
      earHTML = `
        <path d="M 37,26 Q 35,16 31,18" fill="none" stroke="#212529" stroke-width="1.5"/>
        <circle cx="31" cy="18" r="1.5" fill="#212529"/>
        <path d="M 43,26 Q 45,16 49,18" fill="none" stroke="#212529" stroke-width="1.5"/>
        <circle cx="49" cy="18" r="1.5" fill="#212529"/>
      `;
      break;

    case 'turtle':
      // Shell
      specialFeaturesHTML = `
        <!-- Shell rim -->
        <ellipse cx="40" cy="45" rx="20.5" ry="12.5" fill="none" stroke="${accentColorHex}" stroke-width="3" opacity="0.9"/>
        <!-- Hex patterns inside shell -->
        <path d="M 30,45 H 50 M 35,40 L 45,40 M 35,50 L 45,50" stroke="${accentColorHex}" stroke-width="1.5" opacity="0.6"/>
      `;
      // Tiny tail
      tailHTML = `<polygon points="16,56 12,58 17,52" fill="${mainColorHex}"/>`;
      break;

    case 'crab':
      // Eyes on stalks, claws
      earHTML = `
        <!-- Eye stalks -->
        <line x1="32" y1="28" x2="32" y2="20" stroke="${mainColorHex}" stroke-width="3"/>
        <circle cx="32" cy="19" r="4.5" fill="#FFFFFF"/>
        <circle cx="32" cy="19" r="2" fill="#000000"/>
        
        <line x1="48" y1="28" x2="48" y2="20" stroke="${mainColorHex}" stroke-width="3"/>
        <circle cx="48" cy="19" r="4.5" fill="#FFFFFF"/>
        <circle cx="48" cy="19" r="2" fill="#000000"/>
      `;
      // Claws
      specialFeaturesHTML = `
        <path d="M 18,42 C 12,38 8,28 14,24 Q 18,22 18,30 Z" fill="${accentColorHex}" transform="rotate(${wingWiggle * 0.5}, 18, 42)"/>
        <path d="M 62,42 C 68,38 72,28 66,24 Q 62,22 62,30 Z" fill="${accentColorHex}" transform="rotate(${-wingWiggle * 0.5}, 62, 42)"/>
      `;
      break;

    case 'octopus':
      // Tentacles instead of legs (we will render them dynamically below, so override legs)
      // Tentacles are drawn in specialFeatures
      break;

    case 'whale':
    case 'shark':
      // Water spout or dorsal fin
      if (animal.type === 'whale') {
        earHTML = `
          <!-- Water spout -->
          <path d="M 40,24 Q 40,12 36,10 M 40,24 Q 42,12 46,10" fill="none" stroke="#E0F7FA" stroke-width="2.5" stroke-linecap="round" transform="translate(0, ${Math.sin(frame * 0.5) * 2})"/>
        `;
        tailHTML = `<path d="M 16,56 Q 4,58 2,50 Q 8,50 18,52" fill="${mainColorHex}" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      } else { // shark
        // Dorsal fin on back
        earHTML = `
          <polygon points="40,24 32,8 48,24" fill="${mainColorHex}"/>
        `;
        tailHTML = `<path d="M 16,56 Q 6,56 4,46 Q 10,48 18,52" fill="${mainColorHex}" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      }
      break;

    case 'otter':
      // Cute snout & small ears
      earHTML = `
        <circle cx="23" cy="24" r="4" fill="${mainColorHex}"/>
        <circle cx="57" cy="24" r="4" fill="${mainColorHex}"/>
      `;
      // Snout
      snoutHTML = `
        <ellipse cx="40" cy="45" rx="5" ry="3.5" fill="${accentColorHex}"/>
        <polygon points="38,43 42,43 40,45" fill="#2E2E2E"/>
      `;
      tailHTML = `<path d="M 16,56 Q 6,58 8,46" fill="none" stroke="${mainColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      break;

    case 'redpanda':
      // Fluffy ears, mask markings
      earHTML = `
        <polygon points="22,28 14,10 28,22" fill="${accentColorHex}"/>
        <polygon points="23,26 17,14 27,21" fill="#FFFFFF"/>
        <polygon points="58,28 66,10 52,22" fill="${accentColorHex}"/>
        <polygon points="57,26 63,14 53,21" fill="#FFFFFF"/>
      `;
      // White cheeks markings
      specialFeaturesHTML = `
        <ellipse cx="24" cy="46" rx="4" ry="7" fill="#FFFFFF" transform="rotate(20, 24, 46)"/>
        <ellipse cx="56" cy="46" rx="4" ry="7" fill="#FFFFFF" transform="rotate(-20, 56, 46)"/>
        <rect x="33" y="43" width="14" height="8" rx="4" fill="#FFFFFF"/>
      `;
      snoutHTML = `<polygon points="38,44 42,44 40,46.5" fill="#2E2E2E"/>`;
      // Ringed tail
      tailHTML = `
        <path d="M 16,56 C 8,56 4,46 6,38" fill="none" stroke="${accentColorHex}" stroke-width="6.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>
        <path d="M 11,51 L 8,48" stroke="#4A3C31" stroke-width="4"/>
        <path d="M 8,44 L 6,41" stroke="#4A3C31" stroke-width="4"/>
      `;
      break;

    case 'platypus':
      // Duck bill, beaver tail
      snoutHTML = `
        <rect x="31" y="42" width="18" height="8" rx="4" fill="${accentColorHex}"/>
      `;
      // Flat paddle tail
      tailHTML = `<ellipse cx="10" cy="56" rx="8" ry="4" fill="${accentColorHex}" transform="rotate(${tailWiggle + 20}, 10, 56)"/>`;
      break;

    case 'alpaca':
      // Long neck tuft, cute ears
      earHTML = `
        <rect x="25" y="14" width="6" height="15" rx="3" fill="${mainColorHex}" transform="rotate(-10, 28, 14)"/>
        <rect x="49" y="14" width="6" height="15" rx="3" fill="${mainColorHex}" transform="rotate(10, 52, 14)"/>
      `;
      specialFeaturesHTML = `
        <!-- Fluffy curls on neck and head -->
        <circle cx="40" cy="26" r="8" fill="#FFFFFF"/>
        <circle cx="34" cy="30" r="7" fill="#FFFFFF"/>
        <circle cx="46" cy="30" r="7" fill="#FFFFFF"/>
        <rect x="33" y="44" width="14" height="8" rx="4" fill="${accentColorHex}" opacity="0.8"/>
      `;
      snoutHTML = `<path d="M 37,47 L 40,49 L 43,47" fill="none" stroke="#212529" stroke-width="1.5"/>`;
      break;

    case 'axolotl':
      // Gills! 3 pink stems on each side
      earHTML = `
        <!-- Left gills -->
        <path d="M 22,30 Q 12,24 8,26" fill="none" stroke="${accentColorHex}" stroke-width="3" stroke-linecap="round" transform="translate(0, ${Math.sin(frame * 0.3) * 1.5})"/>
        <path d="M 20,38 Q 8,36 6,38" fill="none" stroke="${accentColorHex}" stroke-width="3" stroke-linecap="round" transform="translate(0, ${Math.sin(frame * 0.3 + 1) * 1.5})"/>
        <path d="M 22,46 Q 10,48 8,46" fill="none" stroke="${accentColorHex}" stroke-width="3" stroke-linecap="round" transform="translate(0, ${Math.sin(frame * 0.3 + 2) * 1.5})"/>
        <!-- Right gills -->
        <path d="M 58,30 Q 68,24 72,26" fill="none" stroke="${accentColorHex}" stroke-width="3" stroke-linecap="round" transform="translate(0, ${Math.sin(frame * 0.3) * 1.5})"/>
        <path d="M 60,38 Q 72,36 74,38" fill="none" stroke="${accentColorHex}" stroke-width="3" stroke-linecap="round" transform="translate(0, ${Math.sin(frame * 0.3 + 1) * 1.5})"/>
        <path d="M 58,46 Q 70,48 72,46" fill="none" stroke="${accentColorHex}" stroke-width="3" stroke-linecap="round" transform="translate(0, ${Math.sin(frame * 0.3 + 2) * 1.5})"/>
      `;
      // Cute wide smile
      specialFeaturesHTML = `<path d="M 35,46 Q 40,51 45,46" fill="none" stroke="${animal.text}" stroke-width="1.5" stroke-linecap="round"/>`;
      tailHTML = `<path d="M 16,56 Q 6,56 8,46" fill="none" stroke="${accentColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${tailWiggle}, 16, 56)"/>`;
      break;
  }

  // Draw Legs
  let legsHTML = '';
  if (animal.type === 'octopus') {
    // 6 visible squiggly tentacles
    legsHTML = `
      <path d="M 24,54 Q 20,62 18,58 Q 16,54 18,58" fill="none" stroke="${accentColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${Math.sin(frame * 0.4) * 20}, 24, 54)"/>
      <path d="M 30,55 Q 28,64 27,60" fill="none" stroke="${accentColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${Math.sin(frame * 0.4 + 1) * 20}, 30, 55)"/>
      <path d="M 37,56 Q 37,65 37,61" fill="none" stroke="${accentColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${Math.sin(frame * 0.4 + 2) * 20}, 37, 56)"/>
      <path d="M 43,56 Q 43,65 43,61" fill="none" stroke="${accentColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${Math.sin(frame * 0.4 + 3) * 20}, 43, 56)"/>
      <path d="M 50,55 Q 52,64 53,60" fill="none" stroke="${accentColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${Math.sin(frame * 0.4 + 4) * 20}, 50, 55)"/>
      <path d="M 56,54 Q 60,62 62,58" fill="none" stroke="${accentColorHex}" stroke-width="4.5" stroke-linecap="round" transform="rotate(${Math.sin(frame * 0.4 + 5) * 20}, 56, 54)"/>
    `;
  } else {
    // Normal 2 chibi feet
    const legColor = animal.type === 'chick' || animal.type === 'penguin' ? '#FFA94D' : accentColorHex;
    legsHTML = `
      <ellipse cx="28" cy="57" rx="5" ry="3.5" fill="${legColor}" transform="rotate(${legAngleL}, 28, 57)"/>
      <ellipse cx="52" cy="57" rx="5" ry="3.5" fill="${legColor}" transform="rotate(${legAngleR}, 52, 57)"/>
    `;
  }

  // Draw Eyes based on State
  let eyesHTML = '';
  if (state === 'sleepy') {
    // Closed curved lines u u
    eyesHTML = `
      <path d="M 28,40 Q 32,44 36,40" fill="none" stroke="${animal.text}" stroke-width="2.5" stroke-linecap="round"/>
      <path d="M 44,40 Q 48,44 52,40" fill="none" stroke="${animal.text}" stroke-width="2.5" stroke-linecap="round"/>
      <!-- Zzz letters floating -->
      <g transform="translate(56, 20) scale(${0.6 + Math.sin(frame * 0.1) * 0.15})">
        <path d="M2,2 H8 L2,8 H8" fill="none" stroke="${animal.text}" stroke-width="1.5" stroke-linecap="round"/>
        <text x="10" y="8" font-family="Arial" font-size="6" font-weight="bold" fill="${animal.text}">zZ</text>
      </g>
    `;
  } else if (state === 'running' || state === 'boosting') {
    // Happy slanted eyes ^ ^ or determined arcs
    eyesHTML = `
      <path d="M 27,42 L 32,37 L 37,42" fill="none" stroke="${animal.text}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M 43,42 L 48,37 L 53,42" fill="none" stroke="${animal.text}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
    `;
  } else if (state === 'winner') {
    // Star eyes or winking eyes
    eyesHTML = `
      <!-- Star Left -->
      <polygon points="32,32 34,37 39,37 35,40 37,45 32,42 27,45 29,40 25,37 30,37" fill="#FEEA35" stroke="#E5A93C" stroke-width="1"/>
      <!-- Wink Right -->
      <path d="M 44,40 Q 48,44 52,40" fill="none" stroke="${animal.text}" stroke-width="3.5" stroke-linecap="round"/>
    `;
  } else {
    // Normal big shiny anime eyes
    eyesHTML = `
      <g transform="scale(1, ${eyeScaleY}) translate(0, ${40 * (1 - eyeScaleY)})">
        <circle cx="32" cy="40" r="5.5" fill="${animal.text}"/>
        <circle cx="30" cy="38" r="2" fill="#FFFFFF"/>
        <circle cx="33.5" cy="41.5" r="0.8" fill="#FFFFFF"/>
        
        <circle cx="48" cy="40" r="5.5" fill="${animal.text}"/>
        <circle cx="46" cy="38" r="2" fill="#FFFFFF"/>
        <circle cx="49.5" cy="41.5" r="0.8" fill="#FFFFFF"/>
      </g>
    `;
  }

  // Draw cheeks (soft pink)
  const cheeksHTML = `
    <circle cx="24" cy="45" r="3.5" fill="#FFA8A8" opacity="0.6"/>
    <circle cx="56" cy="45" r="3.5" fill="#FFA8A8" opacity="0.6"/>
  `;

  // Draw default tiny nose if snout is not custom drawn
  const defaultNoseHTML = snoutHTML === '' ? `
    <polygon points="38,43 42,43 40,45" fill="${animal.text}"/>
    <path d="M 38,47 Q 40,49 42,47" fill="none" stroke="${animal.text}" stroke-width="1.2" stroke-linecap="round"/>
  ` : '';

  // Return full composite SVG
  // Size: 80x80 pixels
  // Includes a clean circular badge with the animal's bib number on its tummy.
  // The badge has high contrast so it's super visible from a distance!
  return `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" width="100%" height="100%">
      <defs>
        <!-- Shadow gradient -->
        <radialGradient id="shadowGlow" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stop-color="#000000" stop-opacity="0.25"/>
          <stop offset="100%" stop-color="#000000" stop-opacity="0"/>
        </radialGradient>
      </defs>
      
      <!-- Shadow underneath -->
      <ellipse cx="40" cy="58" rx="20" ry="4" fill="url(#shadowGlow)"/>
      
      <!-- Animated Tail (under body) -->
      ${tailHTML}
      
      <!-- Animated Legs -->
      ${legsHTML}
      
      <!-- Animated Ear Base -->
      ${earHTML}
      
      <!-- Main Body Group (bounces) -->
      <g transform="translate(0, ${bounceY})">
        <!-- Body Base -->
        <ellipse cx="40" cy="41.5" rx="20.5" ry="16.5" fill="${mainColorHex}"/>
        
        <!-- Belly Overlay (White/Pale) -->
        <ellipse cx="40" cy="44.5" rx="13.5" ry="10.5" fill="#FFFFFF" opacity="0.9"/>
        
        <!-- Species Special Markings -->
        ${specialFeaturesHTML}
        
        <!-- Eyes -->
        ${eyesHTML}
        
        <!-- Cheeks -->
        ${cheeksHTML}
        
        <!-- Mouth / Nose / Snout -->
        ${snoutHTML || defaultNoseHTML}
        
        <!-- Number Bib/Badge (Centered on tummy, highly readable) -->
        <g transform="translate(40, 48)">
          <!-- Circular Badge background -->
          <circle cx="0" cy="0" r="7.5" fill="#FFFFFF" stroke="${animal.text}" stroke-width="1.5"/>
          <!-- Bib Number text (font-size scales dynamically for multi-digit numbers to avoid overflow) -->
          <text x="0" y="${animal.id >= 100 ? '2.0' : '2.5'}" font-family="'Outfit', 'Fredoka', 'Segoe UI', sans-serif" font-size="${animal.id >= 100 ? '5.5' : animal.id >= 10 ? '7' : '8'}" font-weight="900" text-anchor="middle" fill="${animal.text}">
            ${animal.id}
          </text>
        </g>
      </g>
    </svg>
  `;
}

// Export data for use in other JS modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { ANIMALS, getAnimal, renderAnimalSVG };
}

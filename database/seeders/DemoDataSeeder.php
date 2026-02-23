<?php

/**
 * 数据库填充器：演示数据生成器
 *
 * 运行 `php artisan db:seed --class=DemoDataSeeder` 生成完整的演示数据，包括：
 * 1. 5 个普通用户（用于测试领养申请）
 * 2. 18 只动物（猫狗各半，包含不同审核状态）
 * 3. 每只动物 2-5 条护理日志
 * 4. 多条领养申请（各种状态）
 * 5. 每只动物 1-3 份档案记录（模拟 PDF 上传）
 */

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AdoptionApplication;
use App\Models\CareLog;
use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * 执行演示数据填充
     *
     * 分 5 个步骤顺序创建：用户 → 动物 → 护理日志 → 领养申请 → 档案记录
     */
    public function run(): void
    {
        // ========== 1. 创建用户 ==========
        $users = [];

        $userList = [
            ['name' => '李小花', 'email' => 'lixiaohua@example.com'],
            ['name' => '王大明', 'email' => 'wangdaming@example.com'],
            ['name' => '张晓梅', 'email' => 'zhangxiaomei@example.com'],
            ['name' => '刘志强', 'email' => 'liuzhiqiang@example.com'],
            ['name' => '赵雨萱', 'email' => 'zhaoyuxuan@example.com'],
        ];

        foreach ($userList as $u) {
            $users[] = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name'        => $u['name'],
                    'password'    => Hash::make('password123'),
                    'is_admin'    => 0,
                    'role_status' => 0,
                ]
            );
        }

        // 获取管理员（已存在的）
        $admin = User::where('is_admin', 1)->first() ?? User::first();

        // ========== 2. 创建动物（猫狗各半，各种状态） ==========
        $animalData = [
            // 🐱 猫
            ['name' => '小橘',   'species' => '猫', 'age' => 2,  'description' => '橘猫，性格温顺，喜欢蹭人，已绝育。',         'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '花花',   'species' => '猫', 'age' => 1,  'description' => '三花猫，活泼好动，爱玩毛线球。',             'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '黑煤球', 'species' => '猫', 'age' => 3,  'description' => '纯黑田园猫，高冷但忠诚，适合安静家庭。',     'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '奶茶',   'species' => '猫', 'age' => 1,  'description' => '奶牛猫，好奇心强，聪明伶俐。',               'review_status' => 'pending',  'visibility' => 'public'],
            ['name' => '布丁',   'species' => '猫', 'age' => 4,  'description' => '白色长毛猫，性格慵懒，适合上班族。',         'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '小虎',   'species' => '猫', 'age' => 2,  'description' => '狸花猫，捕鼠能手，健康活泼。',               'review_status' => 'adopted',  'visibility' => 'private'],

            // 🐶 狗
            ['name' => '旺财',   'species' => '狗', 'age' => 3,  'description' => '中华田园犬，忠诚勇敢，已完成免疫。',         'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '豆豆',   'species' => '狗', 'age' => 1,  'description' => '泰迪串串，毛色棕红，亲人粘人。',             'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '大黄',   'species' => '狗', 'age' => 5,  'description' => '金毛串串，温柔友善，适合有小孩的家庭。',     'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '小白',   'species' => '狗', 'age' => 2,  'description' => '萨摩耶串，毛发雪白，笑容治愈。',             'review_status' => 'pending',  'visibility' => 'public'],
            ['name' => '黑豆',   'species' => '狗', 'age' => 4,  'description' => '拉布拉多串，智商高，服从性好。',             'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '麻团',   'species' => '狗', 'age' => 1,  'description' => '比熊串串，体型娇小，适合公寓饲养。',         'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '阿福',   'species' => '狗', 'age' => 6,  'description' => '柴犬串串，表情丰富，性格独立。',             'review_status' => 'rejected', 'visibility' => 'private'],
            ['name' => '点点',   'species' => '狗', 'age' => 2,  'description' => '斑点狗串，精力旺盛，需要大运动量。',         'review_status' => 'approved', 'visibility' => 'public'],

            // 额外
            ['name' => '咪咪',   'species' => '猫', 'age' => 3,  'description' => '蓝灰色英短串，安静温和。',                   'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '毛毛',   'species' => '猫', 'age' => null,'description' => '流浪幼猫，约两个月大，正在恢复中。',        'review_status' => 'pending',  'visibility' => 'public'],
            ['name' => '来福',   'species' => '狗', 'age' => 3,  'description' => '中型田园犬，护家本能强，已驱虫。',           'review_status' => 'approved', 'visibility' => 'public'],
            ['name' => '雪球',   'species' => '猫', 'age' => 1,  'description' => '白色短毛猫，眼睛一蓝一黄，非常漂亮。',      'review_status' => 'approved', 'visibility' => 'public'],
        ];

        $animals = [];
        foreach ($animalData as $data) {
            $animals[] = Animal::create(array_merge($data, [
                'created_by' => $admin->id,
            ]));
        }

        // ========== 3. 创建护理日志 ==========
        $careTypes = ['日常喂养', '体检', '驱虫', '疫苗接种', '术后护理', '行为观察', '洗澡清洁'];
        $careNotes = [
            '日常喂养'  => ['进食量正常，精神状态良好。', '今日食欲旺盛，饮水量正常。', '更换为处方粮，观察适应情况。'],
            '体检'      => ['体检各项指标正常，毛发有光泽。', '心肺听诊正常，牙齿需要清洁。', '腹部触诊无异常，建议定期复查。'],
            '驱虫'      => ['已完成体内驱虫（拜耳），无不良反应。', '体外驱虫完成（福来恩），需隔离24小时。', '驱虫后观察粪便正常。'],
            '疫苗接种'  => ['已注射猫三联第一针，状态良好。', '狂犬疫苗接种完成，记录证书编号。', '已完成犬五联加强针。'],
            '术后护理'  => ['绝育手术后第3天，伤口恢复良好。', '拆线完毕，伤口无感染迹象。', '术后食欲恢复，活动量增加。'],
            '行为观察'  => ['对陌生人仍有警惕，需继续社会化训练。', '与其他动物相处良好，无攻击行为。', '已适应室内环境，开始主动亲近人。'],
            '洗澡清洁'  => ['全身洗澡完成，毛发蓬松。', '清理耳道和眼周分泌物。', '修剪指甲完成，无应激反应。'],
        ];

        foreach ($animals as $animal) {
            // 每只动物生成2-5条护理记录
            $logCount = rand(2, 5);
            for ($i = 0; $i < $logCount; $i++) {
                $type = $careTypes[array_rand($careTypes)];
                $notes = $careNotes[$type][array_rand($careNotes[$type])];
                $careDate = now()->subDays(rand(1, 90));

                CareLog::create([
                    'animal_id'     => $animal->id,
                    'care_date'     => $careDate->format('Y-m-d'),
                    'type'          => $type,
                    'notes'         => $notes,
                    'weight'        => $animal->species === '猫' ? round(rand(25, 60) / 10, 1) : round(rand(50, 250) / 10, 1),
                    'temperature'   => round(rand(380, 395) / 10, 1),
                    'next_visit_at' => rand(0, 1) ? $careDate->addDays(rand(7, 30))->format('Y-m-d H:i:s') : null,
                    'created_by'    => $admin->id,
                ]);
            }
        }

        // ========== 4. 创建领养申请 ==========
        $reasons = [
            '我从小就喜欢小动物，家里有足够的空间和时间照顾它，希望能给它一个温暖的家。',
            '我住在独栋房子，有院子可以让它自由活动，之前养过宠物，有丰富的饲养经验。',
            '看到它的照片就觉得很有缘分，我是自由职业者，有充足时间陪伴它，会定期带它体检。',
            '家里有一只猫/狗，想再领养一只做伴，经济条件稳定，可以负担宠物的所有开销。',
            '我是动物保护志愿者，有多年救助经验，会给它最好的生活环境和科学的饮食搭配。',
            '退休在家，子女都已工作，想领养一只宠物做伴，住所宽敞，小区允许养宠物。',
        ];

        $addresses = [
            '北京市朝阳区望京街道花园小区3号楼502',
            '上海市浦东新区陆家嘴路88号绿城小区A座1201',
            '广州市天河区体育西路168号锦绣花园2栋301',
            '深圳市南山区科技园路99号阳光家园5号楼801',
            '成都市锦江区春熙路56号锦江花园3单元602',
            '杭州市西湖区文三路200号西溪花园1幢401',
        ];

        $phones = ['13812345678', '13987654321', '15011112222', '18633334444', '17755556666'];

        // 为不同的动物创建领养申请
        $applyAnimals = array_filter($animals, fn($a) => in_array($a->review_status, ['approved', 'adopted']));
        $applyAnimals = array_values($applyAnimals);

        foreach (array_slice($applyAnimals, 0, 8) as $idx => $animal) {
            $user = $users[$idx % count($users)];
            $status = 'pending';
            
            // 让部分申请有不同状态
            if ($idx < 2) $status = 'approved';
            elseif ($idx < 4) $status = 'rejected'; 
            elseif ($idx < 6) $status = 'pending';
            else $status = 'completed';

            // 已领养的动物对应completed状态
            if ($animal->review_status === 'adopted') $status = 'completed';

            AdoptionApplication::create([
                'user_id'       => $user->id,
                'animal_id'     => $animal->id,
                'apply_reason'  => $reasons[array_rand($reasons)],
                'contact_phone' => $phones[array_rand($phones)],
                'address'       => $addresses[array_rand($addresses)],
                'status'        => $status,
                'created_at'    => now()->subDays(rand(1, 60)),
            ]);
        }

        // 额外：给部分动物添加多个申请（模拟热门动物）
        foreach (array_slice($applyAnimals, 0, 3) as $animal) {
            $otherUser = $users[array_rand($users)];
            AdoptionApplication::create([
                'user_id'       => $otherUser->id,
                'animal_id'     => $animal->id,
                'apply_reason'  => $reasons[array_rand($reasons)],
                'contact_phone' => $phones[array_rand($phones)],
                'address'       => $addresses[array_rand($addresses)],
                'status'        => 'pending',
                'created_at'    => now()->subDays(rand(1, 30)),
            ]);
        }

        // ========== 5. 创建档案记录（模拟PDF上传） ==========
        $fileTypes = ['检疫证明', '疫苗本', '体检报告', '绝育证明', '领养协议'];
        $fileRemarks = [
            '检疫证明' => '动物检疫合格，可安全领养。',
            '疫苗本'   => '疫苗接种记录完整，已完成全部免疫程序。',
            '体检报告' => '各项体检指标正常，身体健康。',
            '绝育证明' => '已完成绝育手术，术后恢复良好。',
            '领养协议' => '领养协议模板，需双方签字确认。',
        ];

        foreach (array_slice($animals, 0, 12) as $animal) {
            // 每只动物1-3个档案
            $fileCount = rand(1, 3);
            $usedTypes = [];
            
            for ($i = 0; $i < $fileCount; $i++) {
                do {
                    $type = $fileTypes[array_rand($fileTypes)];
                } while (in_array($type, $usedTypes));
                $usedTypes[] = $type;

                $reviewStatus = ['pending', 'approved', 'approved', 'approved'][rand(0, 3)]; // 大部分已审核

                FileRecord::create([
                    'animal_id'     => $animal->id,
                    'type'          => $type,
                    'path'          => 'files/demo_' . strtolower(str_replace(' ', '_', $type)) . '_' . $animal->id . '.pdf',
                    'original_name' => $type . '_' . $animal->name . '.pdf',
                    'size_kb'       => rand(100, 2000),
                    'mime'          => 'application/pdf',
                    'remark'        => $fileRemarks[$type],
                    'uploaded_by'   => $admin->id,
                    'review_status' => $reviewStatus,
                    'reviewed_by'   => $reviewStatus === 'approved' ? $admin->id : null,
                    'reviewed_at'   => $reviewStatus === 'approved' ? now()->subDays(rand(1, 30)) : null,
                    'created_at'    => now()->subDays(rand(5, 90)),
                ]);
            }
        }

        $this->command->info('✅ 演示数据创建完成！');
        $this->command->info("   👤 新用户: " . count($users) . " 个");
        $this->command->info("   🐾 动物: " . count($animals) . " 只");
        $this->command->info("   📋 护理记录: " . CareLog::count() . " 条");
        $this->command->info("   📄 档案: " . FileRecord::count() . " 份");
        $this->command->info("   📝 领养申请: " . AdoptionApplication::count() . " 条");
    }
}

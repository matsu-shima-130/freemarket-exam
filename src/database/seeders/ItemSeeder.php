<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // 1) 出品者作成（探して、なければ作る）
            $seller = User::firstOrCreate(
                ['email' => 'seller@example.com'],
                [
                    'name'     => '出品太郎',
                    'password' => bcrypt('password'),
                ]
            );

            // 2) ダミー商品
            $items = [
                [
                    'seller_id'   => $seller->id,
                    'name'        => '腕時計',
                    'brand_name'  => 'Rolax',
                    'description' => 'スタイリッシュなデザインのメンズ腕時計',
                    'price'       => 15000,
                    'condition'   => 1,
                    'status'      => 0,     // 0=販売中, 1=Sold
                    'image_path'  => 'images/items/udedokei.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => 'HDD',
                    'brand_name'  => '西芝',
                    'description' => '高速で信頼性の高いハードディスク',
                    'price'       => 5000,
                    'condition'   => 2,
                    'status'      => 0,
                    'image_path'  => 'images/items/HDD.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => '玉ねぎ3束',
                    'brand_name'  => 'なし',
                    'description' => '新鮮な玉ねぎ3束のセット',
                    'price'       => 300,
                    'condition'   => 3,
                    'status'      => 0,
                    'image_path'  => 'images/items/tamanegi.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => '革靴',
                    'brand_name'  => '',
                    'description' => 'クラシックなデザインの革靴',
                    'price'       => 4000,
                    'condition'   => 4,
                    'status'      => 0,
                    'image_path'  => 'images/items/kawagutu.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => 'ノートPC',
                    'brand_name'  => '',
                    'description' => '高性能なノートパソコン',
                    'price'       => 45000,
                    'condition'   => 1,
                    'status'      => 0,
                    'image_path'  => 'images/items/notePC.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => 'マイク',
                    'brand_name'  => 'なし',
                    'description' => '高音質のレコーディング用マイク',
                    'price'       => 8000,
                    'condition'   => 2,
                    'status'      => 0,
                    'image_path'  => 'images/items/maiku.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => 'ショルダーバッグ',
                    'brand_name'  => '',
                    'description' => 'おしゃれなショルダーバッグ',
                    'price'       => 3500,
                    'condition'   => 3,
                    'status'      => 0,
                    'image_path'  => 'images/items/bag.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => 'タンブラー',
                    'brand_name'  => 'なし',
                    'description' => '使いやすいタンブラー',
                    'price'       => 500,
                    'condition'   => 4,
                    'status'      => 0,
                    'image_path'  => 'images/items/tumbler.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => 'コーヒーミル',
                    'brand_name'  => 'Starbacks',
                    'description' => '手動のコーヒーミル',
                    'price'       => 4000,
                    'condition'   => 1,
                    'status'      => 0,
                    'image_path'  => 'images/items/coffee.jpg',
                ],
                [
                    'seller_id'   => $seller->id,
                    'name'        => 'メイクセット',
                    'brand_name'  => '',
                    'description' => '便利なメイクアップセット',
                    'price'       => 2500,
                    'condition'   => 2,
                    'status'      => 0,
                    'image_path'  => 'images/items/make.jpg',
                ]
            ];

            // 3) 画像コピー + 重複対策して登録
            foreach ($items as $data) {

                $publicSrc = public_path($data['image_path']);

                $filename  = basename($data['image_path']);
                $destPath  = "items/{$filename}";

                if (file_exists($publicSrc) && !Storage::disk('public')->exists($destPath)) {
                    Storage::disk('public')->put($destPath, file_get_contents($publicSrc));
                }

                // DBに保存
                Item::updateOrCreate(
                    ['seller_id' => $data['seller_id'], 'name' => $data['name']],
                    [
                        'brand_name'  => $data['brand_name'] ?? null,
                        'description' => $data['description'],
                        'price'       => $data['price'],
                        'condition'   => $data['condition'],
                        'status'      => $data['status'],
                        'image_path'  => $destPath,
                    ]
                );
            }
        });
    }
}
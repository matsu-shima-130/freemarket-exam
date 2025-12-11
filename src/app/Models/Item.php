<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Item extends Model
{
    use HasFactory;

    public const CONDITION_LABELS = [
        1 => '良好',
        2 => '目立った傷や汚れなし',
        3 => 'やや傷や汚れあり',
        4 => '状態が悪い',
    ];

    protected $casts = [
        'price'     => 'integer',
        'condition' => 'integer',
        'status'    => 'integer',
    ];

    protected $fillable = [
        'seller_id','name','brand_name','description','price','condition','status','image_path',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    //  $item->condition_label でラベルを取れるようにする
    public function getConditionLabelAttribute(): string
    {
        return self::CONDITION_LABELS[$this->condition] ?? '不明';
    }

    // 「Sold」表示用
    public function getIsSoldAttribute(): bool
    {
        // 1) ステータスで即判定
        if ((int) ($this->status ?? 0) === 1) {
            return true;
        }

        // 2) purchase を既に読み込んでいれば追加クエリなしで判定
        if ($this->relationLoaded('purchase')) {
            return (bool) $this->purchase; // null なら false, モデルあれば true
        }

        // 3) 読み込んでいなければ exists() で最小限のクエリ
        return $this->purchase()->exists();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_categories');
    }
}

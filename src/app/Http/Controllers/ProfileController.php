<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile()->firstOrNew();

        $justRegistered = session()->pull('just_registered', false);
        $userNameForForm = $justRegistered ? '' : ($user->name ?? '');

        $avatarUrl = ($profile->avatar_path && Storage::disk('public')->exists($profile->avatar_path))
            ? asset('storage/'.$profile->avatar_path)
            : asset('images/avatar-placeholder.png');

        $ver = $profile->updated_at ? ('?v='.$profile->updated_at->timestamp) : '';

        return view('mypage.profile_edit', compact(
            'user', 'profile', 'justRegistered', 'userNameForForm', 'avatarUrl', 'ver'
        ));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $oldPath = optional($user->profile)->avatar_path;

        // 画像アップロード
        $newPath = null;
        if ($request->hasFile('avatar')) {
            // 1) 新しい画像を保存
            $newPath = $request->file('avatar')->store('avatars', 'public');
            // 2) 旧画像があり、かつ新しいパスと違うなら削除
            if ($oldPath && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // users テーブルの名前を更新
        $user->update(['name' => $data['name']]);

        // user_profiles を upsert
        $profileData = [
            'postal_code' => $data['postal_code'],
            'address'     => $data['address'],
            'building'    => $data['building'] ?? null,
        ];
        if ($newPath) {
            $profileData['avatar_path'] = $newPath;
        }

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return redirect()->route('mypage.index')->with('status', 'プロフィールを更新しました。');
    }

}

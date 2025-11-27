<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Item;

class CommentController extends Controller
{
    // POST /items/{item}/comments
    public function store(StoreCommentRequest $request, Item $item)
    {
        $item->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $request->validated()['body'],
        ]);

        return back()->with('success', 'コメントを投稿しました。');
    }

    // DELETE /comments/{comment}
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'コメントを削除しました。');
    }
}

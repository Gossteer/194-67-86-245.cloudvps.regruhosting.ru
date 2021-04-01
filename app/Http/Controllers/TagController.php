<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\User;

class TagController extends Controller
{
    public function all(Request $request)
    {
        $userId = $request->post('user_id');
        $user = User::where(['id' => $userId])->first();

        return response()->json([
            'tags' => $user ? $user->tags()->orderBy('body', 'asc')->get() : []
        ]);
    }

    public function save(Request $request)
    {
        $userId = $request->post('user_id');
        $checkbox = $request->post('checkbox') ?? [];
        $user = User::find($userId) ?? new User();
        $user->id = $userId;
        $user->save();
        $user->tags()->detach();
        foreach ($checkbox as $value) {
            if ($value) {
                $data = explode('/', $value);
                $body = $data[0];
                $groupId = $data[1];
                $groupName = $data[2];
                $category = $data[3];
                $section = $data[4];
                $tag = Tag::where(['body' => $body])->first() ?? new Tag();
                $tag->body = trim($body);
                $tag->group_id = $groupId;
                $tag->group_name = $groupName;
                $tag->category = $category ?? '';
                $tag->section = $section ?? '';
                $tag->save();
                $user->tags()->save($tag);
            }
        }

        return response()->json([
            'response' => 'ok'
        ]);
    }

    public function remove(Request $request)
    {
        $userId = $request->post('user_id');
        $tagId = $request->post('tag_id');
        $user = User::where(['id' => $userId])->first();
        $user->tags()->detach($tagId);

        return response()->json([
            'response' => 'ok'
        ]);
    }
}

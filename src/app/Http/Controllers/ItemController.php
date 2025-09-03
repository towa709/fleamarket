<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Favorite;
use App\Http\Requests\CommentRequest;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;



class ItemController extends Controller
{
  public function index(Request $request)
  {
    $keyword = trim($request->input('keyword', ''));

    $query = Item::query();

    // 🔍 キーワード検索（部分一致）
    if ($keyword !== '') {
      $query->where('name', 'like', "%{$keyword}%");
    }

    // 自分の商品を除外
    if (Auth::check()) {
      $query->where('user_id', '!=', Auth::id());
    }

    $items = $query->with(['transaction', 'categories', 'user'])->get();

    // マイリスト用も同じキーワードを反映
    $favoriteItems = Auth::check()
      ? Item::whereHas('favorites', function ($favoriteQuery) {
        $favoriteQuery->where('user_id', Auth::id());
      })
      ->where('user_id', '!=', Auth::id())
      ->when($request->filled('keyword'), function ($itemQuery) use ($keyword) {
        $itemQuery->where('name', 'like', "%{$keyword}%");
      })
      ->with(['transaction', 'categories'])
      ->get()
      : collect();

    return view('items.index', compact('items', 'favoriteItems'))
      ->with('keyword', $request->input('keyword'));
  }

  public function show($id)
  {
    $item = Item::with(['categories', 'comments.user', 'favorites'])
      ->withCount(['comments', 'favorites'])
      ->findOrFail($id);
    $isFavorited = false;
    if (Auth::check()) {
      $isFavorited = $item->favorites()->where('user_id', Auth::id())->exists();
    }
    return view('items.item_detail', compact('item', 'isFavorited'));
  }


  public function storeComment(CommentRequest $request, $item_id)
  {
    if (!Auth::check()) {

      return redirect()->route('login')
        ->with('error');
    }

    Comment::create([
      'user_id' => auth()->id(),
      'item_id' => $item_id,
      'content' => $request->content,
    ]);

    return back()->with('success');
  }

  public function toggleFavorite(Request $request, Item $item)
  {
    $user = Auth::user();

    if ($item->favorites()->where('user_id', $user->id)->exists()) {
      $item->favorites()->detach($user->id); // いいね解除
      $status = 'removed';
    } else {
      $item->favorites()->attach($user->id); // いいね登録
      $status = 'added';
    }

    return response()->json([
      'status' => $status,
      'count' => $item->favorites()->count(),
    ]);
  }


  // 出品画面
  public function create()
  {
    $categories = Category::all();
    return view('item_create', compact('categories'));
  }

  // 出品処理
  public function store(ExhibitionRequest $request)
  {
    $path = $request->file('image')->store('items', 'public');

    $item = Item::create([
      'user_id'     => Auth::id(),
      'name'        => $request->name,
      'brand'       => $request->brand ?? null,
      'description' => $request->description,
      'img_url'     => $path,
      'condition'   => $request->condition,
      'price'       => $request->price,
    ]);

    // 複数カテゴリに対応
    $item->categories()->sync($request->category_id);

    return redirect('/')->with('success');
  }
}

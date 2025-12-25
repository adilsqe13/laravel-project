<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * List articles with pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $articles = Article::latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => [
                'current_page' => $articles->currentPage(),
                'per_page'     => $articles->perPage(),
                'total'        => $articles->total(),
                'data'         => $articles->items(),
                'first_page_url' => $articles->url(1),
                'last_page'      => $articles->lastPage(),
                'last_page_url'  => $articles->url($articles->lastPage()),
                'next_page_url'  => $articles->nextPageUrl(),
                'prev_page_url'  => $articles->previousPageUrl(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Store a new article
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'url'     => 'required|url|unique:articles,url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $article = Article::create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Article created successfully',
            'data' => $article
        ], 201);
    }

    /**
     * Show single article
     */
    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'status' => false,
                'message' => 'Article not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $article
        ], 200);
    }

    /**
     * Update an existing article
     */
    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'status' => false,
                'message' => 'Article not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'   => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        
            'url'     => 'sometimes|required|url|unique:articles,url,' . $id,
        
            // reference_article should be an array
            'reference_article'                   => 'sometimes|required|array',
        
            // each item inside reference_article
            'reference_article.*.reference_title' => 'required|string|max:255',
            'reference_article.*.content'         => 'required|string',
            'reference_article.*.url'             => 'required|url'
        ]);
        

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $article->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Article updated successfully',
            'data' => $article
        ], 200);
    }

    /**
     * Delete an article
     */
    public function destroy($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'status' => false,
                'message' => 'Article not found'
            ], 404);
        }

        $article->delete();

        return response()->json([
            'status' => true,
            'message' => 'Article deleted successfully'
        ], 200);
    }
}

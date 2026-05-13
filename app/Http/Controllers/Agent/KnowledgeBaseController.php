<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KbCategory;
use App\Models\KbFaq;

class KnowledgeBaseController extends Controller
{

    public function index()
    {
        $categories = KbCategory::with('articles')->get();
        $faqs = KbFaq::all();

        return view('agent.knowledge-base', compact('categories', 'faqs'));
    }


    public function showCategory($slug)
    {
        $category = KbCategory::where('slug', $slug)->with('articles')->firstOrFail();
        return view('knowledge-base.category', compact('category'));
    }


    public function showArticle($slug)
    {
        $article = \App\Models\KbArticle::where('slug', $slug)->with('category')->firstOrFail();
        return view('knowledge-base.article', compact('article'));
    }
}

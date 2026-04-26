<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KbCategory;
use App\Models\KbArticle;
use App\Models\KbFaq;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $categories = KbCategory::with('articles')->get();
        $faqs = KbFaq::all();

        return view('admin.knowledge-base.index', compact('categories', 'faqs'));
    }

    // --- Categories ---
    public function storeCategory(Request $request)
    {
        $request->merge(['slug' => \Illuminate\Support\Str::slug($request->title)]);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kb_categories',
            'icon' => 'required|string',
            'description' => 'required|string',
        ]);

        KbCategory::create($validated);
        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = KbCategory::findOrFail($id);
        $request->merge(['slug' => \Illuminate\Support\Str::slug($request->title)]);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kb_categories,slug,' . $id,
            'icon' => 'required|string',
            'description' => 'required|string',
        ]);

        $category->update($validated);
        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroyCategory($id)
    {
        KbCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }

    // --- Articles ---
    public function storeArticle(Request $request)
    {
        $request->merge(['slug' => \Illuminate\Support\Str::slug($request->title)]);
        $validated = $request->validate([
            'kb_category_id' => 'required|exists:kb_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kb_articles',
            'content' => 'nullable|string',
        ]);

        KbArticle::create($validated);
        return redirect()->back()->with('success', 'Article created successfully.');
    }

    public function updateArticle(Request $request, $id)
    {
        $article = KbArticle::findOrFail($id);
        $request->merge(['slug' => \Illuminate\Support\Str::slug($request->title)]);
        $validated = $request->validate([
            'kb_category_id' => 'required|exists:kb_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kb_articles,slug,' . $id,
            'content' => 'nullable|string',
        ]);

        $article->update($validated);
        return redirect()->back()->with('success', 'Article updated successfully.');
    }

    public function destroyArticle($id)
    {
        KbArticle::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Article deleted successfully.');
    }

    // --- FAQs ---
    public function storeFaq(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        KbFaq::create($validated);
        return redirect()->back()->with('success', 'FAQ created successfully.');
    }

    public function updateFaq(Request $request, $id)
    {
        $faq = KbFaq::findOrFail($id);
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq->update($validated);
        return redirect()->back()->with('success', 'FAQ updated successfully.');
    }

    public function destroyFaq($id)
    {
        KbFaq::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'FAQ deleted successfully.');
    }
}

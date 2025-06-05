<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use AuditLoggable;
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\View\View
     */    public function index(Request $request)
    {
        $query = Category::with('parent')
            ->withCount('products');          // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Apply quick search filter (from the mini search bar)
        if ($request->filled('quick_search')) {
            $query->where('name', 'like', '%' . $request->quick_search . '%');
        }
        
        // Apply parent category filter
        if ($request->filled('parent_filter')) {
            if ($request->parent_filter === 'root') {
                $query->whereNull('parent_category_id');
            } else if ($request->parent_filter === 'children') {
                $query->whereNotNull('parent_category_id');
            } else {
                $query->where('parent_category_id', $request->parent_filter);
            }
        }
        
        // Apply status filter
        if ($request->filled('status_filter')) {
            $isActive = $request->status_filter === 'active';
            $query->where('is_active', $isActive);
        }
        
        // Get categories for the filter dropdown separately
        $allCategories = Category::whereNull('parent_category_id')->get();        // Get per_page value from request or use default
        $perPage = $request->per_page ? (int)$request->per_page : 25;
        
        // Get paginated results - order by ID (smallest to largest)
        $categories = $query->orderBy('id', 'asc')
            ->paginate($perPage)
            ->appends($request->except('page'));
        
        return view('admin.categories.index', compact('categories', 'allCategories'));
    }

    /**
     * Show the form for creating a new category.
     *
     * @return \Illuminate\View\View
     */    public function create()
    {
        $parentCategories = Category::whereNull('parent_category_id')
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'parent_category_id' => ['nullable', 'exists:categories,id'],
            'category_description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_file' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        // Set default active status if not provided
        if (!isset($validatedData['is_active'])) {
            $validatedData['is_active'] = true;
        }
        
        // Set created_by
        $validatedData['created_by'] = auth('admin')->id();

        // Handle image upload or URL
        if ($request->hasFile('image_file')) {
            // File upload takes priority
            $imagePath = $request->file('image_file')->store('categories', 'public');
            $validatedData['image_url'] = $imagePath;
        } elseif ($request->filled('image_url')) {
            // Use the provided URL
            $validatedData['image_url'] = $request->input('image_url');
        }
          // Remove image_file from validated data as it's not a database field
        unset($validatedData['image_file']);

        $category = Category::create($validatedData);

        // Log the activity
        $this->logCreate($category, "Created category: {$category->name}");

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified category.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\View\View
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products']);
        
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\View\View
     */    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_category_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {        $validatedData = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('categories')->ignore($category->id),
            ],
            'parent_category_id' => [
                'nullable', 
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value == $category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                }
            ],
            'category_description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_file' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        // Set default active status if not provided
        if (!isset($validatedData['is_active'])) {
            $validatedData['is_active'] = false;
        }        // Handle image upload or URL
        if ($request->boolean('remove_image')) {
            // User wants to remove the current image
            if ($category->image_url && !str_starts_with($category->image_url, 'http')) {
                Storage::disk('public')->delete($category->image_url);
            }
            $validatedData['image_url'] = null;
        } elseif ($request->hasFile('image_file')) {
            // Remove old image if it's a file (not URL)
            if ($category->image_url && !str_starts_with($category->image_url, 'http')) {
                Storage::disk('public')->delete($category->image_url);
            }
            
            // File upload takes priority
            $imagePath = $request->file('image_file')->store('categories', 'public');
            $validatedData['image_url'] = $imagePath;
        } elseif ($request->filled('image_url')) {
            // Remove old image if it's a file (not URL) and we're switching to URL
            if ($category->image_url && !str_starts_with($category->image_url, 'http')) {
                Storage::disk('public')->delete($category->image_url);
            }
            
            // Use the provided URL
            $validatedData['image_url'] = $request->input('image_url');
        } else {
            // No new image provided, preserve the existing image
            // Remove image_url from validated data to prevent it from being updated
            unset($validatedData['image_url']);
        }
          // Remove fields that are not database columns
        unset($validatedData['image_file']);
        unset($validatedData['remove_image']);
        
        // Store original data for audit log
        $originalData = $category->toArray();
        
        $category->update($validatedData);

        // Log the activity
        $this->logUpdate($category, $originalData, "Updated category: {$category->name}");

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */    public function destroy(Category $category)
    {
        // Check if the category has products or child categories
        if ($category->products()->exists() || $category->children()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category. It has associated products or subcategories.');
        }        // Remove category image if exists and is not an external URL
        if ($category->image_url && !str_starts_with($category->image_url, 'http')) {
            Storage::disk('public')->delete($category->image_url);
        }

        // Log the activity before deleting
        $this->logDelete($category, "Deleted category: {$category->name}");

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}

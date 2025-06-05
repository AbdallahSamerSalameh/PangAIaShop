@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Category</h1>
    <a href="{{ route('admin.categories.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Categories
    </a>
</div>

<!-- Category Edit Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Category: {{ $category->name }}</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                            </div>

                            <!-- Parent Category -->
                            <div class="form-group">
                                <label for="parent_category_id">Parent Category</label>
                                <select class="form-control" id="parent_category_id" name="parent_category_id">
                                    <option value="">Select Parent Category (Root Category)</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_category_id', $category->parent_category_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="category_description">Description</label>
                                <textarea class="form-control" id="category_description" name="category_description" rows="4" placeholder="Enter category description...">{{ old('category_description', $category->category_description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Category Image -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Category Image</h6>
                        </div>
                        <div class="card-body">                            @if($category->image_url)
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-600">Current Image:</label>
                                <div class="text-center">
                                    @php
                                        $currentImageUrl = str_starts_with($category->image_url, 'http') 
                                            ? $category->image_url 
                                            : asset('storage/' . $category->image_url);
                                    @endphp
                                    <img src="{{ $currentImageUrl }}" alt="{{ $category->name }}" class="img-fluid rounded shadow border" style="max-height: 150px;">
                                </div>
                                <div class="text-center mt-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                                        <label class="custom-control-label text-danger" for="remove_image">
                                            <i class="fas fa-trash mr-1"></i>Remove current image
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Check this box to remove the current image</small>
                                </div>
                            </div>
                            @endif

                            <!-- Image Upload Tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="upload-tab" data-toggle="tab" href="#upload" role="tab">
                                        <i class="fas fa-upload mr-1"></i> Upload New Image
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="url-tab" data-toggle="tab" href="#url" role="tab">
                                        <i class="fas fa-link mr-1"></i> Image URL
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-3">
                                <!-- File Upload Tab -->
                                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                    <div class="form-group">
                                        <label for="image_file">Choose Image File</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="image_file" name="image_file" accept="image/*">
                                            <label class="custom-file-label" for="image_file">Choose file...</label>
                                        </div>
                                        <small class="form-text text-muted">Max file size: 2MB. Formats: JPG, PNG, GIF</small>
                                    </div>
                                </div>
                                
                                <!-- URL Tab -->
                                <div class="tab-pane fade" id="url" role="tabpanel">
                                    <div class="form-group">
                                        <label for="image_url_input">Image URL</label>
                                        <input type="url" class="form-control" id="image_url_input" name="image_url" value="{{ old('image_url', str_starts_with($category->image_url ?? '', 'http') ? $category->image_url : '') }}" placeholder="https://example.com/image.jpg">
                                        <small class="form-text text-muted">Enter a direct link to an image</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <hr>
                                <label class="font-weight-bold text-gray-600">New Image Preview:</label>
                                <div class="text-center">
                                    <img id="previewImg" src="" alt="Preview" class="img-fluid rounded shadow border" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings & Tips -->
                <div class="col-md-4">
                    <!-- Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Category Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">Inactive categories won't be visible to customers</small>
                            </div>
                        </div>                    </div>

                    <!-- Category Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle fa-sm mr-1"></i> Category Info
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Created</div>
                            <div class="mb-2">{{ $category->created_at->format('M d, Y h:i A') }}</div>
                            
                            <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Last Updated</div>
                            <div class="mb-2">{{ $category->updated_at->format('M d, Y h:i A') }}</div>
                            
                            <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Subcategories</div>
                            <div class="mb-2">{{ $category->children->count() }}</div>
                              <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Products</div>
                            <div>{{ $category->products->count() }}</div>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-lightbulb fa-sm mr-1"></i> Quick Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-sm text-gray-700">
                                <div class="mb-2">
                                    <i class="fas fa-tag text-primary fa-sm mr-2"></i>
                                    <strong>Name:</strong> Use clear, descriptive names
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-sitemap text-info fa-sm mr-2"></i>
                                    <strong>Parent:</strong> Organize categories hierarchically
                                </div>
                                <div class="mb-0">
                                    <i class="fas fa-image text-success fa-sm mr-2"></i>
                                    <strong>Image:</strong> Upload files or use URLs
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-eye fa-sm mr-1"></i> View Category
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-times fa-sm mr-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save fa-sm mr-1"></i> Update Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal for Image -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Image Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Image preview functionality for file upload
document.getElementById('image_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
        
        // Update file label
        const fileName = file.name;
        const label = document.querySelector('.custom-file-label');
        label.textContent = fileName;
        
        // Clear URL input when file is selected
        document.getElementById('image_url_input').value = '';
        
        // Uncheck remove image checkbox when new file is selected
        const removeCheckbox = document.getElementById('remove_image');
        if (removeCheckbox) {
            removeCheckbox.checked = false;
        }
    } else {
        document.getElementById('imagePreview').style.display = 'none';
        document.querySelector('.custom-file-label').textContent = 'Choose file...';
    }
});

// Image preview functionality for URL input
document.getElementById('image_url_input').addEventListener('input', function(e) {
    const url = e.target.value.trim();
    if (url) {
        // Clear file input when URL is entered
        document.getElementById('image_file').value = '';
        document.querySelector('.custom-file-label').textContent = 'Choose file...';
        
        // Show preview
        document.getElementById('previewImg').src = url;
        document.getElementById('imagePreview').style.display = 'block';
        
        // Uncheck remove image checkbox when new URL is entered
        const removeCheckbox = document.getElementById('remove_image');
        if (removeCheckbox) {
            removeCheckbox.checked = false;
        }
        
        // Handle image load error
        document.getElementById('previewImg').onerror = function() {
            document.getElementById('imagePreview').style.display = 'none';
        };
    } else {
        document.getElementById('imagePreview').style.display = 'none';
    }
});

// Remove image checkbox functionality
document.getElementById('remove_image')?.addEventListener('change', function(e) {
    const isChecked = e.target.checked;
    const fileInput = document.getElementById('image_file');
    const urlInput = document.getElementById('image_url_input');
    const fileLabel = document.querySelector('.custom-file-label');
    const imagePreview = document.getElementById('imagePreview');
    
    if (isChecked) {
        // Disable file upload and URL input when remove is checked
        fileInput.disabled = true;
        urlInput.disabled = true;
        fileInput.value = '';
        urlInput.value = '';
        fileLabel.textContent = 'Choose file...';
        imagePreview.style.display = 'none';
        
        // Add visual indication
        fileInput.parentElement.style.opacity = '0.5';
        urlInput.style.opacity = '0.5';
    } else {
        // Re-enable inputs when remove is unchecked
        fileInput.disabled = false;
        urlInput.disabled = false;
        fileInput.parentElement.style.opacity = '1';
        urlInput.style.opacity = '1';
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const nameField = document.getElementById('name');
    if (!nameField.value.trim()) {
        e.preventDefault();
        nameField.focus();
        alert('Category name is required.');
        return false;
    }
});
</script>
@endpush

@push('styles')
<style>
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .text-gray-500 {
        color: #858796 !important;
    }
    
    .text-gray-700 {
        color: #6e707e !important;
    }
    
    .text-sm {
        font-size: 0.875rem;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endpush

@extends('admin.layouts.app')

@section('title', 'Create Category')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Category</h1>
    <a href="{{ route('admin.categories.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Categories
    </a>
</div>

<!-- Category Create Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
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

        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf            <div class="row">
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
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter category name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Parent Category -->
                            <div class="form-group">
                                <label for="parent_category_id">Parent Category</label>
                                <select class="form-control @error('parent_category_id') is-invalid @enderror" id="parent_category_id" name="parent_category_id">
                                    <option value="">Select Parent Category (Root Category)</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_category_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Leave empty to create a root category</small>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="category_description">Description</label>
                                <textarea class="form-control @error('category_description') is-invalid @enderror" id="category_description" name="category_description" rows="4" placeholder="Enter category description...">{{ old('category_description') }}</textarea>
                                @error('category_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Category Image -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Category Image</h6>
                        </div>
                        <div class="card-body">
                            <!-- Image Upload Tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="upload-tab" data-toggle="tab" href="#upload" role="tab">
                                        <i class="fas fa-upload mr-1"></i> Upload Image
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
                                            <input type="file" class="custom-file-input @error('image_url') is-invalid @enderror" id="image_file" name="image_file" accept="image/*">
                                            <label class="custom-file-label" for="image_file">Choose file...</label>
                                        </div>
                                        @error('image_url')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Max file size: 2MB. Formats: JPG, PNG, GIF</small>
                                    </div>
                                </div>
                                
                                <!-- URL Tab -->
                                <div class="tab-pane fade" id="url" role="tabpanel">
                                    <div class="form-group">
                                        <label for="image_url_input">Image URL</label>
                                        <input type="url" class="form-control @error('image_url') is-invalid @enderror" id="image_url_input" name="image_url" value="{{ old('image_url') }}" placeholder="https://example.com/image.jpg">
                                        @error('image_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Enter a direct link to an image</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <hr>
                                <label class="font-weight-bold text-gray-600">Image Preview:</label>
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
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">Inactive categories won't be visible to customers</small>
                            </div>
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
                            <!-- Optional: Add a preview button or other actions -->
                        </div>
                        <div>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-times fa-sm mr-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save fa-sm mr-1"></i> Create Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
        
        // Handle image load error
        document.getElementById('previewImg').onerror = function() {
            document.getElementById('imagePreview').style.display = 'none';
        };
    } else {
        document.getElementById('imagePreview').style.display = 'none';
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

// Auto-focus on name field when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('name').focus();
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
    
    .text-gray-600 {
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

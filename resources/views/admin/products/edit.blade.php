@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
    <a href="{{ route('admin.products.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Products
    </a>
</div>

<!-- Product Edit Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Product: {{ $product->name }}</h6>
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

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            </div>

                            <!-- SKU -->
                            <div class="form-group">
                                <label for="sku">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description', $product->description) }}</textarea>
                            </div>

                            <!-- Categories -->
                            <div class="form-group">
                                <label for="categories">Categories <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="categories" name="categories[]" multiple required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ in_array($category->id, $productCategoryIds) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Brand -->
                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand', $product->brand) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Pricing & Inventory</h6>
                        </div>
                        <div class="card-body">
                            <!-- Regular Price -->
                            <div class="form-group">
                                <label for="price">Regular Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                                </div>
                            </div>

                            <!-- Sale Price -->
                            <div class="form-group">
                                <label for="sale_price">Sale Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" value="{{ old('sale_price', $product->sale_price) }}">
                                </div>
                            </div>                            <!-- Quantity -->
                            <div class="form-group">
                                <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="{{ old('quantity', $product->inventory ? $product->inventory->quantity : 0) }}" required>
                            </div>

                            <!-- Location -->
                            <div class="form-group">
                                <label for="location">Storage Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $product->inventory ? $product->inventory->location : 'Main Warehouse') }}" placeholder="e.g., Main Warehouse, Warehouse A">
                                <small class="form-text text-muted">Where this product is stored</small>
                            </div>

                            <!-- Status Toggles -->
                            <div class="form-group">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="in_stock" name="in_stock" value="1" {{ old('in_stock', $product->in_stock) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="in_stock">In Stock</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_featured">Featured Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Images -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Product Images</h6>
                </div>
                <div class="card-body">                    <!-- Current Images -->
                    <div class="form-group">
                        <label>Current Images</label>
                        <div class="row">
                            @if($product->images && $product->images->count() > 0)
                                @foreach($product->images as $image)
                                    <div class="col-md-3 mb-3">
                                        @php
                                            // Smart image URL handling
                                            $imageUrl = str_starts_with($image->image_url, 'http') 
                                                ? $image->image_url 
                                                : asset('storage/' . $image->image_url);
                                            
                                            // Get category fallback image
                                            $categoryImageUrl = '';
                                            if($product->directCategories && $product->directCategories->count() > 0 && $product->directCategories->first()->image_url) {
                                                $categoryImage = $product->directCategories->first()->image_url;
                                                $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                                    ? $categoryImage 
                                                    : asset('storage/' . $categoryImage);
                                            } elseif($product->categories && $product->categories->count() > 0 && $product->categories->first()->image_url) {
                                                $categoryImage = $product->categories->first()->image_url;
                                                $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                                    ? $categoryImage 
                                                    : asset('storage/' . $categoryImage);
                                            } else {
                                                $categoryImageUrl = asset('admin-assets/img/undraw_posting_photo.svg');
                                            }
                                        @endphp
                                        <div class="card">
                                            <div class="position-relative" style="height: 200px; overflow: hidden;">
                                                <img src="{{ $imageUrl }}" 
                                                    class="card-img-top" 
                                                    alt="{{ $image->alt_text ?: $product->name }}"
                                                    style="width: 100%; height: 100%; object-fit: cover; object-position: center;"
                                                    onerror="if(this.src !== '{{ $categoryImageUrl }}') { this.src='{{ $categoryImageUrl }}'; } else { this.src='{{ asset('admin-assets/img/undraw_posting_photo.svg') }}'; this.onerror=null; }"
                                                    loading="lazy">
                                                @if($image->is_primary)
                                                    <span class="badge badge-primary position-absolute" style="top: 5px; right: 5px;">Main</span>
                                                @endif
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="custom-control custom-checkbox mb-1">
                                                    <input type="checkbox" class="custom-control-input" id="delete_image_{{ $image->id }}" name="delete_images[]" value="{{ $image->id }}">
                                                    <label class="custom-control-label" for="delete_image_{{ $image->id }}">Delete</label>
                                                </div>                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" id="main_image_{{ $image->id }}" name="main_image" value="{{ $image->id }}" {{ $image->is_primary ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="main_image_{{ $image->id }}">Main Image</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <p class="text-muted">No images uploaded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>                    <!-- Upload New Images -->
                    <div class="form-group">
                        <label for="images">Upload New Images</label>
                        <div class="d-flex">
                            <div class="custom-file flex-grow-1">
                                <input type="file" class="custom-file-input" id="image-input" multiple accept="image/*">
                                <label class="custom-file-label" for="image-input" id="file-label">Choose files...</label>
                            </div>
                            <button type="button" class="btn btn-outline-danger ml-2" id="clear-files">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            You can select multiple images at once (Ctrl/Cmd+Click). Files will be added to your selection.
                            <br><span id="file-count" class="text-info"></span>
                        </small>
                        <!-- Hidden file input that will be submitted with the form -->
                        <div id="file-inputs-container"></div>
                        <div id="new-image-preview" class="row mt-3"></div>
                    </div>
                    
                    <!-- Add Images from URLs -->
                    <div class="form-group">
                        <label>Add Images from URLs</label>
                        <div id="url-inputs">
                            <div class="input-group mb-2">
                                <input type="url" class="form-control" name="image_urls[]" placeholder="Enter image URL (e.g., https://example.com/image.jpg)">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-success" onclick="addUrlInput()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">Add images from external URLs. Click + to add more URLs.</small>
                        <div id="url-image-preview" class="row mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="fas fa-save mr-1"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--multiple {
        min-height: calc(1.5em + 0.75rem + 2px);
    }
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Global array to store selected files
    var selectedFiles = [];

    // Initialize Select2 for categories
    $('.select2').select2({
        placeholder: 'Select categories'
    });
    
    // Handle image selection
    $('#image-input').change(function(e) {
        var newFiles = Array.from(e.target.files);
        
        // Add new files to our collection
        if (newFiles.length > 0) {
            selectedFiles = selectedFiles.concat(newFiles);
            
            // Update the label
            updateFileLabel();
            
            // Clear the input so the same files can be selected again if needed
            $(this).val('');
            
            // Create hidden inputs for form submission
            updateHiddenInputs();
            
            // Update the preview
            updateNewImagePreview();
        }
    });
    
    // Clear selected files
    $('#clear-files').click(function() {
        selectedFiles = [];
        updateFileLabel();
        updateHiddenInputs();
        updateNewImagePreview();
    });
    
    // Handle URL input changes
    $(document).on('input', 'input[name="image_urls[]"]', function() {
        updateUrlImagePreview();
    });
    
    // Function to update file selection label
    function updateFileLabel() {
        if (selectedFiles.length > 0) {
            var fileNames = selectedFiles.map(function(file) { return file.name; });
            $('#file-label').text(selectedFiles.length + ' files selected');
            $('#file-count').html('<strong>' + selectedFiles.length + ' files selected:</strong> ' + fileNames.join(', '));
        } else {
            $('#file-label').text('Choose files...');
            $('#file-count').text('');
        }
    }
      // Function to update hidden inputs for form submission
    function updateHiddenInputs() {
        $('#file-inputs-container').empty();
        
        for (var i = 0; i < selectedFiles.length; i++) {
            var fileInput = $('<input type="file" name="images[]" style="display:none;">');
            
            // Create a new DataTransfer object and add the file
            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(selectedFiles[i]);
            fileInput[0].files = dataTransfer.files;
            
            $('#file-inputs-container').append(fileInput);
        }
    }
    
    // Function to update file selection label
    function updateFileLabel() {
        if (selectedFiles.length > 0) {
            var fileNames = selectedFiles.map(function(file) { return file.name; });
            $('#file-label').text(selectedFiles.length + ' files selected');
            $('#file-count').html('<strong>' + selectedFiles.length + ' files selected:</strong> ' + fileNames.join(', '));
        } else {
            $('#file-label').text('Choose files...');
            $('#file-count').text('');
        }
    }
});

function addUrlInput() {
    var newInput = `
        <div class="input-group mb-2">
            <input type="url" class="form-control" name="image_urls[]" placeholder="Enter image URL (e.g., https://example.com/image.jpg)">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeUrlInput(this)">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    $('#url-inputs').append(newInput);
}

function removeUrlInput(button) {
    $(button).closest('.input-group').remove();
    updateUrlImagePreview();
}

function removeSelectedImage(button) {
    // Get the container with data attributes
    var container = $(button).closest('[data-index]');
    var index = container.data('index');
    
    // Remove from selectedFiles array
    selectedFiles.splice(index, 1);
    
    // Update hidden inputs and preview
    updateFileLabel();
    updateHiddenInputs();
    updateNewImagePreview();
}

function removeUrlImage(button) {
    // Get the container with data attributes
    var container = $(button).closest('[data-url-index]');
    var inputIndex = container.data('url-index');
    
    // Clear the URL in the input field
    $('input[name="image_urls[]"]').eq(inputIndex).val('');
    
    // Update the preview
    updateUrlImagePreview();
}

function updateNewImagePreview() {
    $('#new-image-preview').html('');
    
    if(selectedFiles.length > 0) {
        for(var i = 0; i < selectedFiles.length; i++) {
            (function(file, index) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var imageDiv = $(`
                        <div class="col-md-3 mb-3" data-index="${index}" data-type="file">
                            <div class="card">
                                <div class="position-relative" style="height: 200px; overflow: hidden;">
                                    <img src="${e.target.result}" class="card-img-top" alt="New Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; padding: 0.125rem 0.25rem; opacity: 0.9;"
                                            onclick="removeSelectedImage(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="card-body p-2">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" id="main_image_new_${index}" name="main_image" value="new-${index}">
                                        <label class="custom-control-label" for="main_image_new_${index}">Make Main</label>
                                    </div>
                                    <small class="text-muted">File Upload</small>
                                </div>
                            </div>
                        </div>
                    `);
                    $('#new-image-preview').append(imageDiv);
                };
                reader.readAsDataURL(file);
            })(selectedFiles[i], i);
        }
    }
}

function updateUrlImagePreview() {
    $('#url-image-preview').html('');
    var urlIndex = 0;
    
    $('input[name="image_urls[]"]').each(function(inputIndex) {
        var url = $(this).val().trim();
        if (url) {
            var imageDiv = $(`
                <div class="col-md-3 mb-3" data-url-index="${inputIndex}" data-type="url">
                    <div class="card">
                        <div class="position-relative" style="height: 200px; overflow: hidden;">
                            <img src="${url}" class="card-img-top" alt="URL Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center;"
                                 onerror="this.src='{{ asset('admin-assets/img/undraw_posting_photo.svg') }}'; this.style.opacity='0.5';">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; padding: 0.125rem 0.25rem; opacity: 0.9;"
                                    onclick="removeUrlImage(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="main_image_url_${urlIndex}" name="main_image" value="url-${urlIndex}">
                                <label class="custom-control-label" for="main_image_url_${urlIndex}">Make Main</label>
                            </div>
                            <small class="text-muted">URL Image</small>
                        </div>
                    </div>
                </div>
            `);
            $('#url-image-preview').append(imageDiv);
            urlIndex++;
        }
    });
}
</script>
@endpush

@extends('admin.layouts.app')

@section('title', 'Add New Product')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New Product</h1>
    <a href="{{ route('admin.products.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Products
    </a>
</div>

<!-- Product Create Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
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

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

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
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            </div>

                            <!-- SKU -->
                            <div class="form-group">
                                <label for="sku">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku') }}" required>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                            </div>

                            <!-- Categories -->
                            <div class="form-group">
                                <label for="categories">Categories <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="categories" name="categories[]" multiple required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ (old('categories') && in_array($category->id, old('categories'))) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Brand -->
                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand') }}">
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
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="{{ old('price', '0.00') }}" required>
                                </div>
                            </div>

                            <!-- Sale Price -->
                            <div class="form-group">
                                <label for="sale_price">Sale Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" value="{{ old('sale_price') }}">
                                </div>
                            </div>                            <!-- Quantity -->
                            <div class="form-group">
                                <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="{{ old('quantity', '0') }}" required>
                            </div>

                            <!-- Location -->
                            <div class="form-group">
                                <label for="location">Storage Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="{{ old('location', 'Main Warehouse') }}" placeholder="e.g., Main Warehouse, Warehouse A">
                                <small class="form-text text-muted">Where this product is stored</small>
                            </div>

                            <!-- Status Toggles -->
                            <div class="form-group">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="in_stock" name="in_stock" value="1" {{ old('in_stock', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="in_stock">In Stock</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_featured">Featured Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            <!-- Product Images -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Product Images</h6>
                </div>
                <div class="card-body">                    <!-- File Upload -->
                    <div class="form-group">
                        <label for="images">Upload Images</label>
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
                    </div>
                    
                    <!-- URL Inputs -->
                    <div class="form-group">
                        <label>Image URLs</label>
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
                    </div>
                    
                    <div id="image-preview" class="row mt-3"></div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="fas fa-plus mr-1"></i> Create Product
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
// Global array to store selected files with unique IDs
var fileStorage = [];
var nextFileId = 0;

$(document).ready(function() {
    // Initialize Select2 for categories
    $('.select2').select2({
        placeholder: 'Select categories'
    });
    
    // Handle image selection
    $('#image-input').change(function(e) {
        var newFiles = Array.from(e.target.files);
        
        // Add new files to our collection with unique IDs
        if (newFiles.length > 0) {
            newFiles.forEach(function(file) {
                fileStorage.push({
                    id: 'file_' + nextFileId++,
                    file: file
                });
            });
            
            // Update the label
            updateFileLabel();
            
            // Clear the input so the same files can be selected again if needed
            $(this).val('');
            
            // Create hidden inputs for form submission
            updateHiddenInputs();
            
            // Update the preview
            updateImagePreview();
        }
    });
      // Clear selected files
    $('#clear-files').click(function() {
        fileStorage = [];
        updateFileLabel();
        updateHiddenInputs();
        updateImagePreview();
    });
    
    // Handle URL input changes
    $(document).on('input', 'input[name="image_urls[]"]', function() {
        updateImagePreview();
    });
      // Function to update file selection label
    function updateFileLabel() {
        if (fileStorage.length > 0) {
            var fileNames = fileStorage.map(function(item) { return item.file.name; });
            $('#file-label').text(fileStorage.length + ' files selected');
            $('#file-count').html('<strong>' + fileStorage.length + ' files selected:</strong> ' + fileNames.join(', '));
        } else {
            $('#file-label').text('Choose files...');
            $('#file-count').text('');
        }
    }
    
    // Function to update hidden inputs for form submission
    function updateHiddenInputs() {
        $('#file-inputs-container').empty();
        
        fileStorage.forEach(function(item) {
            var fileInput = $('<input type="file" name="images[]" style="display:none;">');
            
            // Create a new DataTransfer object and add the file
            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(item.file);
            fileInput[0].files = dataTransfer.files;
            
            $('#file-inputs-container').append(fileInput);
        });
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
    updateImagePreview();
}

function removeImage(button) {
    // Get parent container with data attributes
    var container = $(button).closest('[data-type]');
    var type = container.data('type');
    var itemId = container.data('id');
    
    if (type === 'file') {
        // Find the index of the file with this ID
        var itemIndex = fileStorage.findIndex(function(item) {
            return item.id === itemId;
        });
        
        // Remove from fileStorage array if found
        if (itemIndex !== -1) {
            fileStorage.splice(itemIndex, 1);
            // Update hidden inputs and labels
            updateFileLabel();
            updateHiddenInputs();
        }
    } else if (type === 'url') {
        // Extract the URL index from the ID (url_X)
        var urlIndex = parseInt(itemId.replace('url_', ''));
        // Clear the corresponding input
        $('input[name="image_urls[]"]').eq(urlIndex).val('');
    }
    
    // Update preview after removal
    updateImagePreview();
}

function updateImagePreview() {
    $('#image-preview').html('');
    var radioIndex = 0;
    
    // Preview selected files
    if (fileStorage.length > 0) {
        fileStorage.forEach(function(item, i) {
            (function(fileItem, index) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    addImageToPreview(e.target.result, radioIndex, 'file', fileItem.id);
                    radioIndex++;
                };
                reader.readAsDataURL(fileItem.file);
            })(item, i);
        });
    }
    
    // Preview URL images
    $('input[name="image_urls[]"]').each(function(i) {
        var url = $(this).val().trim();
        if (url) {
            var urlId = 'url_' + i;
            addImageToPreview(url, radioIndex, 'url', urlId);
            radioIndex++;
        }
    });
}

function addImageToPreview(src, radioIndex, type, itemId) {
    var imageDiv = $(`
        <div class="col-md-3 mb-3" data-id="${itemId}" data-type="${type}">
            <div class="card">
                <div class="position-relative">
                    <img src="${src}" class="card-img-top" alt="Product Image" style="height: 150px; object-fit: cover;" 
                         onerror="this.src='${type === 'url' ? '{{ asset('admin-assets/img/undraw_posting_photo.svg') }}' : ''}'; this.style.opacity='0.5';">
                    <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; padding: 0.125rem 0.25rem; opacity: 0.9;"
                            onclick="removeImage(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body p-2">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="main_image_${radioIndex}" name="main_image" value="${radioIndex}" ${radioIndex == 0 ? 'checked' : ''}>
                        <label class="custom-control-label" for="main_image_${radioIndex}">Main Image</label>
                    </div>
                    <small class="text-muted">${type === 'file' ? 'File Upload' : 'URL'}</small>
                </div>
            </div>
        </div>
    `);
    $('#image-preview').append(imageDiv);
}
</script>
@endpush

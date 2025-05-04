import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";

// reactstrap components
import {
  Button,
  Card,
  CardHeader,
  CardBody,
  FormGroup,
  Form,
  Input,
  Container,
  Row,
  Col,
  Alert,
  Label,
  CustomInput,
  InputGroup,
  InputGroupAddon,
  InputGroupText,
  Spinner
} from "reactstrap";

// core components
import Header from "components/Headers/Header.js";

// Services
import productService from "../../services/product.service";
import categoryService from "../../services/category.service";

const ProductEdit = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  
  // Product form state
  const [product, setProduct] = useState({
    name: "",
    description: "",
    price: "",
    sale_price: "",
    sku: "",
    stock_quantity: "",
    category_id: "",
    is_featured: false,
    is_active: true,
    tax_class: "standard",
    weight: "",
    dimensions: {
      length: "",
      width: "",
      height: ""
    },
    meta_title: "",
    meta_description: "",
    meta_keywords: ""
  });
  
  // Other state
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [fetchLoading, setFetchLoading] = useState(true);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [productImages, setProductImages] = useState([]);
  const [imageFiles, setImageFiles] = useState([]);
  const [existingImages, setExistingImages] = useState([]);
  
  // Load product data and categories on component mount
  useEffect(() => {
    const fetchData = async () => {
      try {
        setFetchLoading(true);
        setError(null);
        
        // Fetch product details
        const productData = await productService.getProductById(id);
        
        // Format the product data
        const formattedProduct = {
          ...productData,
          dimensions: {
            length: productData.dimensions?.length || "",
            width: productData.dimensions?.width || "",
            height: productData.dimensions?.height || ""
          }
        };
        
        setProduct(formattedProduct);
        
        // Set existing images
        if (productData.images && productData.images.length > 0) {
          setExistingImages(productData.images);
        }
        
        // Fetch categories
        const categoriesData = await categoryService.getAllCategories();
        setCategories(categoriesData);
      } catch (err) {
        console.error("Error fetching product data:", err);
        setError("Failed to load product. Please try again later.");
      } finally {
        setFetchLoading(false);
      }
    };
    
    fetchData();
  }, [id]);
  
  // Handle form input changes
  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    
    if (name.includes(".")) {
      // Handle nested objects (e.g., dimensions.length)
      const [parent, child] = name.split(".");
      setProduct({
        ...product,
        [parent]: {
          ...product[parent],
          [child]: value
        }
      });
    } else if (type === "checkbox") {
      setProduct({
        ...product,
        [name]: checked
      });
    } else {
      setProduct({
        ...product,
        [name]: value
      });
    }
  };
  
  // Handle image upload
  const handleImageChange = (e) => {
    const files = Array.from(e.target.files);
    
    if (files.length === 0) return;
    
    // Add new files
    const newImageFiles = [...imageFiles, ...files];
    setImageFiles(newImageFiles);
    
    // Create preview URLs
    const newImages = files.map(file => URL.createObjectURL(file));
    setProductImages([...productImages, ...newImages]);
  };
  
  // Remove new image
  const removeImage = (index) => {
    const newImageFiles = [...imageFiles];
    newImageFiles.splice(index, 1);
    setImageFiles(newImageFiles);
    
    const newProductImages = [...productImages];
    URL.revokeObjectURL(newProductImages[index]); // Clean up URL object
    newProductImages.splice(index, 1);
    setProductImages(newProductImages);
  };
  
  // Remove existing image
  const removeExistingImage = async (imageId) => {
    if (window.confirm("Are you sure you want to remove this image?")) {
      try {
        await productService.deleteProductImage(id, imageId);
        
        // Update the UI
        setExistingImages(existingImages.filter(img => img.id !== imageId));
      } catch (err) {
        console.error("Error removing image:", err);
        setError("Failed to remove image. Please try again.");
      }
    }
  };
  
  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      setLoading(true);
      setError(null);
      setSuccess(null);
      
      // Update product
      await productService.updateProduct(id, product);
      
      // Upload new images if any
      if (imageFiles.length > 0) {
        for (let i = 0; i < imageFiles.length; i++) {
          await productService.uploadProductImage(id, imageFiles[i]);
        }
      }
      
      setSuccess("Product updated successfully!");
      
      // Clear new image uploads after successful submission
      setImageFiles([]);
      setProductImages([]);
      
      // Refresh the product data
      const updatedProduct = await productService.getProductById(id);
      setProduct(updatedProduct);
      
      // Update existing images
      if (updatedProduct.images && updatedProduct.images.length > 0) {
        setExistingImages(updatedProduct.images);
      }
      
    } catch (err) {
      console.error("Error updating product:", err);
      setError(err.message || "Failed to update product. Please try again.");
    } finally {
      setLoading(false);
    }
  };
  
  if (fetchLoading) {
    return (
      <>
        <Header />
        <Container className="mt--7" fluid>
          <Row>
            <Col>
              <Card className="shadow">
                <CardBody className="text-center py-5">
                  <Spinner color="primary" />
                  <p className="mt-3 mb-0">Loading product data...</p>
                </CardBody>
              </Card>
            </Col>
          </Row>
        </Container>
      </>
    );
  }
  
  return (
    <>
      <Header />
      <Container className="mt--7" fluid>
        <Row>
          <Col className="order-xl-1" xl="12">
            <Card className="bg-secondary shadow">
              <CardHeader className="bg-white border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Edit Product: {product.name}</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button
                      color="primary"
                      onClick={() => navigate("/admin/products")}
                      size="sm"
                    >
                      Back to Products
                    </Button>
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                {error && (
                  <Alert color="danger" fade={false}>
                    {error}
                  </Alert>
                )}
                
                {success && (
                  <Alert color="success" fade={false}>
                    {success}
                  </Alert>
                )}
                
                <Form onSubmit={handleSubmit}>
                  <h6 className="heading-small text-muted mb-4">
                    Product Information
                  </h6>
                  <div className="pl-lg-4">
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="name">Product Name *</Label>
                          <Input
                            id="name"
                            name="name"
                            placeholder="Enter product name"
                            type="text"
                            value={product.name}
                            onChange={handleInputChange}
                            required
                          />
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="sku">SKU</Label>
                          <Input
                            id="sku"
                            name="sku"
                            placeholder="Enter product SKU"
                            type="text"
                            value={product.sku}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    
                    <Row>
                      <Col lg="12">
                        <FormGroup>
                          <Label for="description">Description</Label>
                          <Input
                            id="description"
                            name="description"
                            placeholder="Enter product description"
                            type="textarea"
                            rows="4"
                            value={product.description}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="price">Regular Price ($) *</Label>
                          <InputGroup>
                            <InputGroupAddon addonType="prepend">
                              <InputGroupText>$</InputGroupText>
                            </InputGroupAddon>
                            <Input
                              id="price"
                              name="price"
                              placeholder="0.00"
                              type="number"
                              step="0.01"
                              min="0"
                              value={product.price}
                              onChange={handleInputChange}
                              required
                            />
                          </InputGroup>
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="sale_price">Sale Price ($)</Label>
                          <InputGroup>
                            <InputGroupAddon addonType="prepend">
                              <InputGroupText>$</InputGroupText>
                            </InputGroupAddon>
                            <Input
                              id="sale_price"
                              name="sale_price"
                              placeholder="0.00"
                              type="number"
                              step="0.01"
                              min="0"
                              value={product.sale_price}
                              onChange={handleInputChange}
                            />
                          </InputGroup>
                        </FormGroup>
                      </Col>
                    </Row>
                    
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="category_id">Category</Label>
                          <Input
                            id="category_id"
                            name="category_id"
                            type="select"
                            value={product.category_id}
                            onChange={handleInputChange}
                          >
                            <option value="">Select a category</option>
                            {categories.map((category) => (
                              <option key={category.id} value={category.id}>
                                {category.name}
                              </option>
                            ))}
                          </Input>
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="stock_quantity">Stock Quantity *</Label>
                          <Input
                            id="stock_quantity"
                            name="stock_quantity"
                            placeholder="Enter stock quantity"
                            type="number"
                            min="0"
                            value={product.stock_quantity}
                            onChange={handleInputChange}
                            required
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="tax_class">Tax Class</Label>
                          <Input
                            id="tax_class"
                            name="tax_class"
                            type="select"
                            value={product.tax_class}
                            onChange={handleInputChange}
                          >
                            <option value="standard">Standard</option>
                            <option value="reduced">Reduced Rate</option>
                            <option value="zero">Zero Rate</option>
                          </Input>
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="weight">Weight (kg)</Label>
                          <Input
                            id="weight"
                            name="weight"
                            placeholder="Enter product weight"
                            type="number"
                            step="0.01"
                            min="0"
                            value={product.weight}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    
                    <Row>
                      <Col md="4">
                        <FormGroup>
                          <Label for="dimensions.length">Length (cm)</Label>
                          <Input
                            id="dimensions.length"
                            name="dimensions.length"
                            placeholder="Length"
                            type="number"
                            step="0.1"
                            min="0"
                            value={product.dimensions.length}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                      <Col md="4">
                        <FormGroup>
                          <Label for="dimensions.width">Width (cm)</Label>
                          <Input
                            id="dimensions.width"
                            name="dimensions.width"
                            placeholder="Width"
                            type="number"
                            step="0.1"
                            min="0"
                            value={product.dimensions.width}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                      <Col md="4">
                        <FormGroup>
                          <Label for="dimensions.height">Height (cm)</Label>
                          <Input
                            id="dimensions.height"
                            name="dimensions.height"
                            placeholder="Height"
                            type="number"
                            step="0.1"
                            min="0"
                            value={product.dimensions.height}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    
                    <Row>
                      <Col lg="6">
                        <FormGroup check className="mt-2">
                          <Label check>
                            <Input
                              type="checkbox"
                              name="is_featured"
                              checked={product.is_featured}
                              onChange={handleInputChange}
                            />{" "}
                            Featured Product
                          </Label>
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup check className="mt-2">
                          <Label check>
                            <Input
                              type="checkbox"
                              name="is_active"
                              checked={product.is_active}
                              onChange={handleInputChange}
                            />{" "}
                            Active
                          </Label>
                        </FormGroup>
                      </Col>
                    </Row>
                  </div>
                  
                  <hr className="my-4" />
                  
                  <h6 className="heading-small text-muted mb-4">
                    Product Images
                  </h6>
                  <div className="pl-lg-4">
                    <Row>
                      <Col lg="12">
                        <FormGroup>
                          <Label for="product-images">Upload New Images</Label>
                          <CustomInput
                            type="file"
                            id="product-images"
                            name="product-images"
                            multiple
                            accept="image/*"
                            onChange={handleImageChange}
                          />
                          <small className="form-text text-muted">
                            Upload additional product images. You can select multiple files.
                          </small>
                        </FormGroup>
                      </Col>
                    </Row>
                    
                    {/* Display existing images */}
                    {existingImages.length > 0 && (
                      <>
                        <h6 className="heading-small text-muted mb-3 mt-3">
                          Current Images
                        </h6>
                        <Row>
                          {existingImages.map((image) => (
                            <Col md="3" key={image.id} className="mb-3">
                              <div className="image-preview-container">
                                <img
                                  src={image.url}
                                  alt={product.name}
                                  className="img-fluid rounded"
                                  style={{ maxHeight: "150px", width: "100%", objectFit: "cover" }}
                                />
                                <Button
                                  color="danger"
                                  size="sm"
                                  className="position-absolute"
                                  style={{ top: "5px", right: "20px" }}
                                  onClick={() => removeExistingImage(image.id)}
                                >
                                  <i className="fas fa-times" />
                                </Button>
                              </div>
                            </Col>
                          ))}
                        </Row>
                      </>
                    )}
                    
                    {/* Display new images to be uploaded */}
                    {productImages.length > 0 && (
                      <>
                        <h6 className="heading-small text-muted mb-3 mt-3">
                          New Images to Upload
                        </h6>
                        <Row>
                          {productImages.map((image, index) => (
                            <Col md="3" key={index} className="mb-3">
                              <div className="image-preview-container">
                                <img
                                  src={image}
                                  alt={`Preview ${index + 1}`}
                                  className="img-fluid rounded"
                                  style={{ maxHeight: "150px", width: "100%", objectFit: "cover" }}
                                />
                                <Button
                                  color="danger"
                                  size="sm"
                                  className="position-absolute"
                                  style={{ top: "5px", right: "20px" }}
                                  onClick={() => removeImage(index)}
                                >
                                  <i className="fas fa-times" />
                                </Button>
                              </div>
                            </Col>
                          ))}
                        </Row>
                      </>
                    )}
                  </div>
                  
                  <hr className="my-4" />
                  
                  <h6 className="heading-small text-muted mb-4">
                    SEO Information
                  </h6>
                  <div className="pl-lg-4">
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="meta_title">Meta Title</Label>
                          <Input
                            id="meta_title"
                            name="meta_title"
                            placeholder="Enter meta title"
                            type="text"
                            value={product.meta_title}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label for="meta_keywords">Meta Keywords</Label>
                          <Input
                            id="meta_keywords"
                            name="meta_keywords"
                            placeholder="Enter meta keywords"
                            type="text"
                            value={product.meta_keywords}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="12">
                        <FormGroup>
                          <Label for="meta_description">Meta Description</Label>
                          <Input
                            id="meta_description"
                            name="meta_description"
                            placeholder="Enter meta description"
                            type="textarea"
                            rows="2"
                            value={product.meta_description}
                            onChange={handleInputChange}
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                  </div>
                  
                  <div className="pl-lg-4 mt-4">
                    <Row>
                      <Col lg="6">
                        <Button
                          color="secondary"
                          onClick={() => navigate("/admin/products")}
                        >
                          Cancel
                        </Button>
                      </Col>
                      <Col lg="6" className="text-right">
                        <Button
                          color="primary"
                          type="submit"
                          disabled={loading}
                        >
                          {loading ? (
                            <>
                              <Spinner size="sm" className="mr-2" />
                              Updating...
                            </>
                          ) : (
                            "Update Product"
                          )}
                        </Button>
                      </Col>
                    </Row>
                  </div>
                </Form>
              </CardBody>
            </Card>
          </Col>
        </Row>
      </Container>
    </>
  );
};

export default ProductEdit;
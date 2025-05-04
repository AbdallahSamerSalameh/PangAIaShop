import { useState, useEffect, useCallback } from "react";
import { Link } from "react-router-dom";

// reactstrap components
import {
  Card,
  CardHeader,
  CardFooter,
  DropdownMenu,
  DropdownItem,
  UncontrolledDropdown,
  DropdownToggle,
  Table,
  Container,
  Row,
  Col,
  Button,
  Input,
  InputGroup,
  InputGroupAddon,
  Badge,
  Spinner,
  Alert,
  Pagination,
  PaginationItem,
  PaginationLink,
} from "reactstrap";

// core components
import Header from "components/Headers/Header.js";

// Services
import productService from "../../services/product.service";

const ProductList = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [pagination, setPagination] = useState({
    currentPage: 1,
    totalPages: 1,
    totalItems: 0,
    perPage: 10,
  });
  
  // Function to fetch products from API
  const fetchProducts = useCallback(async (page = 1, query = "") => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await productService.getAllProducts(page, pagination.perPage, query);
      
      setProducts(response.data);
      setPagination({
        currentPage: response.current_page,
        totalPages: response.last_page,
        totalItems: response.total,
        perPage: response.per_page,
      });
    } catch (err) {
      console.error("Error fetching products:", err);
      setError("Failed to load products. Please try again later.");
    } finally {
      setLoading(false);
    }
  }, [pagination.perPage]);
  
  useEffect(() => {
    fetchProducts(1, searchQuery);
  }, [searchQuery, fetchProducts]);
  
  // Handle search input change
  const handleSearchChange = (e) => {
    setSearchQuery(e.target.value);
  };
  
  // Handle search form submission
  const handleSearch = (e) => {
    e.preventDefault();
    fetchProducts(1, searchQuery);
  };
  
  // Handle pagination click
  const handlePageClick = (page) => {
    fetchProducts(page, searchQuery);
  };
  
  // Handle product deletion
  const handleDeleteProduct = async (id) => {
    if (window.confirm("Are you sure you want to delete this product?")) {
      try {
        await productService.deleteProduct(id);
        // Refresh the product list
        fetchProducts(pagination.currentPage, searchQuery);
      } catch (err) {
        console.error("Error deleting product:", err);
        setError("Failed to delete product. Please try again later.");
      }
    }
  };
  
  // Render pagination
  const renderPagination = () => {
    const { currentPage, totalPages } = pagination;
    
    if (totalPages <= 1) return null;
    
    const pages = [];
    
    // Always show first page
    pages.push(
      <PaginationItem key="first" active={currentPage === 1}>
        <PaginationLink onClick={(e) => {
          e.preventDefault();
          handlePageClick(1);
        }}>
          1
        </PaginationLink>
      </PaginationItem>
    );
    
    // Calculate range of pages to show
    let startPage = Math.max(2, currentPage - 1);
    let endPage = Math.min(totalPages - 1, currentPage + 1);
    
    // Add ellipsis if needed
    if (startPage > 2) {
      pages.push(
        <PaginationItem key="ellipsis1" disabled>
          <PaginationLink>...</PaginationLink>
        </PaginationItem>
      );
    }
    
    // Add pages in range
    for (let i = startPage; i <= endPage; i++) {
      pages.push(
        <PaginationItem key={i} active={currentPage === i}>
          <PaginationLink onClick={(e) => {
            e.preventDefault();
            handlePageClick(i);
          }}>
            {i}
          </PaginationLink>
        </PaginationItem>
      );
    }
    
    // Add ellipsis if needed
    if (endPage < totalPages - 1) {
      pages.push(
        <PaginationItem key="ellipsis2" disabled>
          <PaginationLink>...</PaginationLink>
        </PaginationItem>
      );
    }
    
    // Always show last page if more than one page
    if (totalPages > 1) {
      pages.push(
        <PaginationItem key="last" active={currentPage === totalPages}>
          <PaginationLink onClick={(e) => {
            e.preventDefault();
            handlePageClick(totalPages);
          }}>
            {totalPages}
          </PaginationLink>
        </PaginationItem>
      );
    }
    
    return (
      <Pagination className="pagination justify-content-end mb-0">
        <PaginationItem disabled={currentPage === 1}>
          <PaginationLink
            previous
            onClick={(e) => {
              e.preventDefault();
              handlePageClick(currentPage - 1);
            }}
          />
        </PaginationItem>
        
        {pages}
        
        <PaginationItem disabled={currentPage === totalPages}>
          <PaginationLink
            next
            onClick={(e) => {
              e.preventDefault();
              handlePageClick(currentPage + 1);
            }}
          />
        </PaginationItem>
      </Pagination>
    );
  };
  
  // Get badge color based on product stock status
  const getStockBadgeColor = (inStock, quantity) => {
    if (!inStock) return "danger";
    if (quantity < 5) return "warning";
    return "success";
  };
  
  return (
    <>
      <Header />
      {/* Page content */}
      <Container className="mt--7" fluid>
        {/* Products table */}
        <Row>
          <div className="col">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Products</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Link to="/admin/products/create">
                      <Button
                        color="primary"
                        size="sm"
                      >
                        Add New Product
                      </Button>
                    </Link>
                  </Col>
                </Row>
                
                {/* Search form */}
                <form onSubmit={handleSearch} className="mt-3">
                  <InputGroup>
                    <Input
                      placeholder="Search products..."
                      type="text"
                      value={searchQuery}
                      onChange={handleSearchChange}
                    />
                    <InputGroupAddon addonType="append">
                      <Button color="primary" type="submit">
                        <i className="fas fa-search" />
                      </Button>
                    </InputGroupAddon>
                  </InputGroup>
                </form>
              </CardHeader>
              
              {loading ? (
                <div className="text-center my-5">
                  <Spinner color="primary" />
                </div>
              ) : error ? (
                <div className="p-4">
                  <Alert color="danger">{error}</Alert>
                </div>
              ) : (
                <>
                  <Table className="align-items-center table-flush" responsive>
                    <thead className="thead-light">
                      <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Image</th>
                        <th scope="col">Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Price</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Status</th>
                        <th scope="col" />
                      </tr>
                    </thead>
                    <tbody>
                      {products.length === 0 ? (
                        <tr>
                          <td colSpan="8" className="text-center py-4">
                            No products found
                          </td>
                        </tr>
                      ) : (
                        products.map((product) => (
                          <tr key={product.id}>
                            <td>{product.id}</td>
                            <td>
                              <img
                                src={product.thumbnail_url || '/img/no-image.png'}
                                alt={product.name}
                                style={{
                                  width: '50px',
                                  height: '50px',
                                  objectFit: 'cover',
                                  borderRadius: '4px'
                                }}
                              />
                            </td>
                            <td>
                              <Link to={`/admin/products/edit/${product.id}`}>
                                {product.name}
                              </Link>
                            </td>
                            <td>
                              {product.category?.name || "Uncategorized"}
                            </td>
                            <td>${product.price}</td>
                            <td>{product.stock_quantity}</td>
                            <td>
                              <Badge
                                color={getStockBadgeColor(
                                  product.in_stock,
                                  product.stock_quantity
                                )}
                              >
                                {product.in_stock
                                  ? product.stock_quantity < 5
                                    ? "Low Stock"
                                    : "In Stock"
                                  : "Out of Stock"}
                              </Badge>
                            </td>
                            <td className="text-right">
                              <UncontrolledDropdown>
                                <DropdownToggle
                                  className="btn-icon-only text-light"
                                  href="#pablo"
                                  role="button"
                                  size="sm"
                                  color=""
                                  onClick={(e) => e.preventDefault()}
                                >
                                  <i className="fas fa-ellipsis-v" />
                                </DropdownToggle>
                                <DropdownMenu className="dropdown-menu-arrow" right>
                                  <DropdownItem
                                    tag={Link}
                                    to={`/admin/products/edit/${product.id}`}
                                  >
                                    Edit
                                  </DropdownItem>
                                  <DropdownItem
                                    href="#pablo"
                                    onClick={() => handleDeleteProduct(product.id)}
                                  >
                                    Delete
                                  </DropdownItem>
                                </DropdownMenu>
                              </UncontrolledDropdown>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </Table>
                  
                  <CardFooter className="py-4">
                    <Row className="align-items-center">
                      <Col xs="6">
                        <small className="text-muted">
                          Showing {products.length} of {pagination.totalItems} products
                        </small>
                      </Col>
                      <Col xs="6">
                        {renderPagination()}
                      </Col>
                    </Row>
                  </CardFooter>
                </>
              )}
            </Card>
          </div>
        </Row>
      </Container>
    </>
  );
};

export default ProductList;
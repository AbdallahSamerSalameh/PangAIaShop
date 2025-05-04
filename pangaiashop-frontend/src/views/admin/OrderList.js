import { useState, useEffect, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import moment from "moment";

// reactstrap components
import {
  Badge,
  Card,
  CardHeader,
  CardFooter,
  Table,
  Container,
  Row,
  Col,
  Button,
  Input,
  InputGroup,
  InputGroupAddon,
  Pagination,
  PaginationItem,
  PaginationLink,
  UncontrolledDropdown,
  DropdownToggle,
  DropdownMenu,
  DropdownItem,
  Form,
  FormGroup,
  Label,
  Spinner,
  Alert
} from "reactstrap";

// core components
import Header from "components/Headers/Header.js";

// Services
import orderService from "../../services/order.service";

const OrderList = () => {
  const navigate = useNavigate();
  
  // State
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalOrders, setTotalOrders] = useState(0);
  const [statusFilter, setStatusFilter] = useState("");
  const [searchQuery, setSearchQuery] = useState("");
  const [isExporting, setIsExporting] = useState(false);
  
  // Fetch orders from API
  const fetchOrders = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      
      const data = await orderService.getAllOrders(currentPage, 10, statusFilter, searchQuery);
      
      setOrders(data.data);
      setTotalPages(data.meta.last_page);
      setTotalOrders(data.meta.total);
    } catch (err) {
      console.error("Error fetching orders:", err);
      setError("Failed to load orders. Please try again later.");
    } finally {
      setLoading(false);
    }
  }, [currentPage, statusFilter, searchQuery]);

  // Load orders on component mount and when filters/pagination changes
  useEffect(() => {
    fetchOrders();
  // Adding fetchOrders and searchQuery as dependencies  
  }, [currentPage, statusFilter, searchQuery, fetchOrders]);
  

  
  // Handle search form submission
  const handleSearch = (e) => {
    e.preventDefault();
    setCurrentPage(1); // Reset to first page when searching
    fetchOrders();
  };
  
  // Handle status filter change
  const handleStatusFilterChange = (status) => {
    setStatusFilter(status);
    setCurrentPage(1); // Reset to first page when changing filter
  };
  
  // Handle page change
  const handlePageChange = (newPage) => {
    if (newPage < 1 || newPage > totalPages) return;
    setCurrentPage(newPage);
  };
  
  // Handle export to CSV
  const handleExport = async () => {
    try {
      setIsExporting(true);
      
      // Prepare export filters
      const exportFilters = {
        status: statusFilter,
        search: searchQuery
      };
      
      await orderService.exportOrders(exportFilters);
    } catch (err) {
      console.error("Error exporting orders:", err);
      setError("Failed to export orders. Please try again.");
    } finally {
      setIsExporting(false);
    }
  };
  
  // Format currency
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2
    }).format(amount);
  };
  
  // Get badge color based on order status
  const getStatusBadgeColor = (status) => {
    switch (status) {
      case 'pending':
        return 'warning';
      case 'processing':
        return 'info';
      case 'shipped':
        return 'primary';
      case 'delivered':
        return 'success';
      case 'cancelled':
        return 'danger';
      default:
        return 'secondary';
    }
  };
  
  return (
    <>
      <Header />
      {/* Page content */}
      <Container className="mt--7" fluid>
        {/* Table */}
        <Row>
          <div className="col">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Orders</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button
                      color="primary"
                      onClick={handleExport}
                      size="sm"
                      disabled={isExporting}
                    >
                      {isExporting ? "Exporting..." : "Export to CSV"}
                    </Button>
                  </Col>
                </Row>
              </CardHeader>
              
              <div className="px-4 py-3 border-bottom">
                <Row>
                  <Col md="6" className="mb-2 mb-md-0">
                    <Form onSubmit={handleSearch}>
                      <InputGroup>
                        <Input
                          placeholder="Search by order #, customer name, or email"
                          type="text"
                          value={searchQuery}
                          onChange={(e) => setSearchQuery(e.target.value)}
                        />
                        <InputGroupAddon addonType="append">
                          <Button color="primary" type="submit">
                            <i className="fas fa-search" />
                          </Button>
                        </InputGroupAddon>
                      </InputGroup>
                    </Form>
                  </Col>
                  <Col md="6" className="d-flex align-items-center justify-content-md-end">
                    <FormGroup className="mb-0">
                      <Label className="mr-2 mb-0">Status:</Label>
                      <UncontrolledDropdown>
                        <DropdownToggle caret color="secondary" size="sm">
                          {statusFilter ? statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1) : "All"}
                        </DropdownToggle>
                        <DropdownMenu>
                          <DropdownItem onClick={() => handleStatusFilterChange("")}>
                            All
                          </DropdownItem>
                          <DropdownItem onClick={() => handleStatusFilterChange("pending")}>
                            Pending
                          </DropdownItem>
                          <DropdownItem onClick={() => handleStatusFilterChange("processing")}>
                            Processing
                          </DropdownItem>
                          <DropdownItem onClick={() => handleStatusFilterChange("shipped")}>
                            Shipped
                          </DropdownItem>
                          <DropdownItem onClick={() => handleStatusFilterChange("delivered")}>
                            Delivered
                          </DropdownItem>
                          <DropdownItem onClick={() => handleStatusFilterChange("cancelled")}>
                            Cancelled
                          </DropdownItem>
                        </DropdownMenu>
                      </UncontrolledDropdown>
                    </FormGroup>
                  </Col>
                </Row>
              </div>
              
              {error && (
                <Alert color="danger" className="m-3">
                  {error}
                </Alert>
              )}
              
              {loading ? (
                <div className="text-center py-5">
                  <Spinner color="primary" />
                  <p className="mt-3 mb-0">Loading orders...</p>
                </div>
              ) : orders.length === 0 ? (
                <div className="text-center py-5">
                  <i className="fas fa-shopping-cart fa-3x mb-3 text-muted" />
                  <p>No orders found.</p>
                  {(statusFilter || searchQuery) && (
                    <Button 
                      color="primary" 
                      size="sm"
                      onClick={() => {
                        setStatusFilter("");
                        setSearchQuery("");
                        setCurrentPage(1);
                      }}
                    >
                      Clear Filters
                    </Button>
                  )}
                </div>
              ) : (
                <Table className="align-items-center table-flush" responsive>
                  <thead className="thead-light">
                    <tr>
                      <th scope="col">Order #</th>
                      <th scope="col">Customer</th>
                      <th scope="col">Date</th>
                      <th scope="col">Status</th>
                      <th scope="col">Payment</th>
                      <th scope="col">Total</th>
                      <th scope="col" className="text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {orders.map((order) => (
                      <tr key={order.id}>
                        <td>
                          <button
                            className="btn btn-link p-0 m-0 text-primary"
                            onClick={() => navigate(`/admin/orders/${order.id}`)}
                            style={{ textDecoration: 'underline' }}
                          >
                            {order.order_number}
                          </button>
                        </td>
                        <td>
                          <div className="d-flex flex-column">
                            <span>{order.customer_name}</span>
                            <small className="text-muted">{order.customer_email}</small>
                          </div>
                        </td>
                        <td>
                          <div className="d-flex flex-column">
                            <span>{moment(order.created_at).format("MMM DD, YYYY")}</span>
                            <small className="text-muted">{moment(order.created_at).format("h:mm A")}</small>
                          </div>
                        </td>
                        <td>
                          <Badge color={getStatusBadgeColor(order.status)}>
                            {order.status}
                          </Badge>
                        </td>
                        <td>
                          <Badge color={order.payment_status === "paid" ? "success" : "warning"}>
                            {order.payment_status === "paid" ? "Paid" : "Pending"}
                          </Badge>
                        </td>
                        <td>{formatCurrency(order.total_amount)}</td>
                        <td className="text-right">
                          <UncontrolledDropdown>
                            <DropdownToggle
                              className="btn-icon-only text-light"
                              role="button"
                              size="sm"
                              color=""
                              onClick={(e) => e.preventDefault()}
                            >
                              <i className="fas fa-ellipsis-v" />
                            </DropdownToggle>
                            <DropdownMenu className="dropdown-menu-arrow" right>
                              <DropdownItem
                                onClick={() => navigate(`/admin/orders/${order.id}`)}
                              >
                                View Details
                              </DropdownItem>
                              <DropdownItem divider />
                              {order.status !== "processing" && (
                                <DropdownItem
                                  onClick={async () => {
                                    try {
                                      await orderService.updateOrderStatus(order.id, "processing");
                                      // Update local state to reflect the change
                                      setOrders(
                                        orders.map((o) =>
                                          o.id === order.id ? { ...o, status: "processing" } : o
                                        )
                                      );
                                    } catch (err) {
                                      console.error("Error updating order status:", err);
                                      setError("Failed to update order status. Please try again.");
                                    }
                                  }}
                                >
                                  Mark as Processing
                                </DropdownItem>
                              )}
                              {order.status !== "shipped" && (
                                <DropdownItem
                                  onClick={() => navigate(`/admin/orders/${order.id}`)}
                                >
                                  Mark as Shipped
                                </DropdownItem>
                              )}
                              {order.status !== "delivered" && (
                                <DropdownItem
                                  onClick={async () => {
                                    try {
                                      await orderService.updateOrderStatus(order.id, "delivered");
                                      // Update local state to reflect the change
                                      setOrders(
                                        orders.map((o) =>
                                          o.id === order.id ? { ...o, status: "delivered" } : o
                                        )
                                      );
                                    } catch (err) {
                                      console.error("Error updating order status:", err);
                                      setError("Failed to update order status. Please try again.");
                                    }
                                  }}
                                >
                                  Mark as Delivered
                                </DropdownItem>
                              )}
                              {order.status !== "cancelled" && (
                                <DropdownItem
                                  onClick={async () => {
                                    if (window.confirm("Are you sure you want to cancel this order?")) {
                                      try {
                                        await orderService.updateOrderStatus(order.id, "cancelled");
                                        // Update local state to reflect the change
                                        setOrders(
                                          orders.map((o) =>
                                            o.id === order.id ? { ...o, status: "cancelled" } : o
                                          )
                                        );
                                      } catch (err) {
                                        console.error("Error cancelling order:", err);
                                        setError("Failed to cancel order. Please try again.");
                                      }
                                    }
                                  }}
                                >
                                  Cancel Order
                                </DropdownItem>
                              )}
                            </DropdownMenu>
                          </UncontrolledDropdown>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </Table>
              )}
              
              {!loading && orders.length > 0 && (
                <CardFooter className="py-4">
                  <nav aria-label="...">
                    <div className="d-flex align-items-center justify-content-between">
                      <div>
                        <small>Showing {orders.length} of {totalOrders} orders</small>
                      </div>
                      <Pagination 
                        className="pagination justify-content-end mb-0"
                        listClassName="justify-content-end mb-0"
                      >
                        <PaginationItem className={currentPage === 1 ? "disabled" : ""}>
                          <PaginationLink
                            href="#"
                            onClick={(e) => {
                              e.preventDefault();
                              handlePageChange(currentPage - 1);
                            }}
                            tabIndex="-1"
                          >
                            <i className="fas fa-angle-left" />
                            <span className="sr-only">Previous</span>
                          </PaginationLink>
                        </PaginationItem>
                        
                        {/* Generate pagination links */}
                        {[...Array(totalPages)].map((_, index) => {
                          // Only show current page, first, last, and one page before and after current
                          const pageNumber = index + 1;
                          if (
                            pageNumber === 1 ||
                            pageNumber === totalPages ||
                            pageNumber === currentPage ||
                            pageNumber === currentPage - 1 ||
                            pageNumber === currentPage + 1
                          ) {
                            return (
                              <PaginationItem
                                key={pageNumber}
                                className={pageNumber === currentPage ? "active" : ""}
                              >
                                <PaginationLink
                                  href="#"
                                  onClick={(e) => {
                                    e.preventDefault();
                                    handlePageChange(pageNumber);
                                  }}
                                >
                                  {pageNumber}
                                </PaginationLink>
                              </PaginationItem>
                            );
                          }
                          
                          // Show ellipsis for gaps
                          if (
                            (pageNumber === currentPage - 2 && currentPage > 3) ||
                            (pageNumber === currentPage + 2 && currentPage < totalPages - 2)
                          ) {
                            return (
                              <PaginationItem key={`ellipsis-${pageNumber}`} className="disabled">
                                <PaginationLink href="#" onClick={(e) => e.preventDefault()}>
                                  ...
                                </PaginationLink>
                              </PaginationItem>
                            );
                          }
                          
                          return null;
                        })}
                        
                        <PaginationItem className={currentPage === totalPages ? "disabled" : ""}>
                          <PaginationLink
                            href="#"
                            onClick={(e) => {
                              e.preventDefault();
                              handlePageChange(currentPage + 1);
                            }}
                          >
                            <i className="fas fa-angle-right" />
                            <span className="sr-only">Next</span>
                          </PaginationLink>
                        </PaginationItem>
                      </Pagination>
                    </div>
                  </nav>
                </CardFooter>
              )}
            </Card>
          </div>
        </Row>
      </Container>
    </>
  );
};

export default OrderList;
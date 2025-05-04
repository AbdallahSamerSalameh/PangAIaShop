import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import moment from "moment";

// reactstrap components
import {
  Button,
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Table,
  Container,
  Row,
  Col,
  Badge,
  Spinner,
  Alert,
  UncontrolledDropdown,
  DropdownToggle,
  DropdownMenu,
  DropdownItem,
  FormGroup,
  Input,
  Label,
  Modal,
  ModalHeader,
  ModalBody,
  ModalFooter,
  Form
} from "reactstrap";

// core components
import Header from "components/Headers/Header.js";

// Services
import orderService from "../../services/order.service";

const OrderDetail = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  
  // State
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [statusUpdateLoading, setStatusUpdateLoading] = useState(false);
  
  // Modal states
  const [shipmentModalOpen, setShipmentModalOpen] = useState(false);
  const [shipmentData, setShipmentData] = useState({
    tracking_number: "",
    carrier: "",
    notes: ""
  });
  
  // Fetch order details on component mount
  useEffect(() => {
    const fetchOrderDetails = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const orderData = await orderService.getOrderById(id);
        setOrder(orderData);
      } catch (err) {
        console.error("Error fetching order details:", err);
        setError("Failed to load order details. Please try again later.");
      } finally {
        setLoading(false);
      }
    };
    
    fetchOrderDetails();
  }, [id]);
  
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
  
  // Handle order status update
  const handleUpdateStatus = async (newStatus) => {
    try {
      setStatusUpdateLoading(true);
      setError(null);
      setSuccess(null);
      
      // If status is "shipped" and there's no shipment info, open the shipment modal
      if (newStatus === 'shipped' && (!order.tracking_number || !order.carrier)) {
        setShipmentModalOpen(true);
        setStatusUpdateLoading(false);
        return;
      }
      
      // Update the order status
      await orderService.updateOrderStatus(id, newStatus);
      
      // Update the UI
      setOrder({ ...order, status: newStatus });
      setSuccess(`Order status updated to ${newStatus}`);
    } catch (err) {
      console.error("Error updating order status:", err);
      setError("Failed to update order status. Please try again.");
    } finally {
      setStatusUpdateLoading(false);
    }
  };
  
  // Handle shipment data input change
  const handleShipmentInputChange = (e) => {
    const { name, value } = e.target;
    setShipmentData({
      ...shipmentData,
      [name]: value
    });
  };
  
  // Handle shipment form submission
  const handleShipmentSubmit = async (e) => {
    e.preventDefault();
    
    try {
      setStatusUpdateLoading(true);
      setError(null);
      
      // Update shipment information
      await orderService.addShipmentTracking(id, shipmentData);
      
      // Update order status to shipped
      await orderService.updateOrderStatus(id, 'shipped');
      
      // Update UI
      setOrder({
        ...order,
        status: 'shipped',
        tracking_number: shipmentData.tracking_number,
        carrier: shipmentData.carrier,
        shipment_notes: shipmentData.notes
      });
      
      setShipmentModalOpen(false);
      setSuccess("Order has been marked as shipped with tracking information");
    } catch (err) {
      console.error("Error adding shipment information:", err);
      setError("Failed to add shipment information. Please try again.");
    } finally {
      setStatusUpdateLoading(false);
    }
  };
  
  // Toggle shipment modal
  const toggleShipmentModal = () => {
    setShipmentModalOpen(!shipmentModalOpen);
  };
  
  if (loading) {
    return (
      <>
        <Header />
        <Container className="mt--7" fluid>
          <Row>
            <Col>
              <Card className="shadow">
                <CardBody className="text-center py-5">
                  <Spinner color="primary" />
                  <p className="mt-3 mb-0">Loading order details...</p>
                </CardBody>
              </Card>
            </Col>
          </Row>
        </Container>
      </>
    );
  }
  
  if (error) {
    return (
      <>
        <Header />
        <Container className="mt--7" fluid>
          <Row>
            <Col>
              <Card className="shadow">
                <CardBody>
                  <Alert color="danger">
                    {error}
                  </Alert>
                  <div className="text-center mt-3">
                    <Button color="primary" onClick={() => navigate("/admin/orders")}>
                      Back to Orders
                    </Button>
                  </div>
                </CardBody>
              </Card>
            </Col>
          </Row>
        </Container>
      </>
    );
  }
  
  if (!order) {
    return (
      <>
        <Header />
        <Container className="mt--7" fluid>
          <Row>
            <Col>
              <Card className="shadow">
                <CardBody>
                  <Alert color="warning">
                    Order not found.
                  </Alert>
                  <div className="text-center mt-3">
                    <Button color="primary" onClick={() => navigate("/admin/orders")}>
                      Back to Orders
                    </Button>
                  </div>
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
      {/* Page content */}
      <Container className="mt--7" fluid>
        {/* Order Details */}
        <Row>
          <Col xs="12">
            <Card className="shadow mb-4">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Order #{order.order_number}</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button
                      color="primary"
                      onClick={() => navigate("/admin/orders")}
                      size="sm"
                    >
                      Back to Orders
                    </Button>
                  </Col>
                </Row>
              </CardHeader>
              
              <CardBody>
                {success && (
                  <Alert color="success" fade={false} className="mb-4">
                    {success}
                  </Alert>
                )}
                
                <Row>
                  <Col md="6">
                    <h6 className="heading-small text-muted mb-3">Order Information</h6>
                    <div className="pl-lg-4">
                      <Row>
                        <Col lg="6">
                          <p className="mb-2">
                            <strong>Order Date:</strong><br />
                            {moment(order.created_at).format('MMM DD, YYYY, h:mm A')}
                          </p>
                        </Col>
                        <Col lg="6">
                          <p className="mb-2">
                            <strong>Status:</strong><br />
                            <Badge color={getStatusBadgeColor(order.status)}>
                              {order.status}
                            </Badge>
                          </p>
                        </Col>
                      </Row>
                      <Row>
                        <Col lg="6">
                          <p className="mb-2">
                            <strong>Payment Method:</strong><br />
                            {order.payment_method}
                          </p>
                        </Col>
                        <Col lg="6">
                          <p className="mb-2">
                            <strong>Payment Status:</strong><br />
                            <Badge color={order.payment_status === 'paid' ? 'success' : 'warning'}>
                              {order.payment_status === 'paid' ? 'Paid' : 'Pending'}
                            </Badge>
                          </p>
                        </Col>
                      </Row>
                      {order.tracking_number && (
                        <Row>
                          <Col lg="6">
                            <p className="mb-2">
                              <strong>Tracking Number:</strong><br />
                              {order.tracking_number}
                            </p>
                          </Col>
                          <Col lg="6">
                            <p className="mb-2">
                              <strong>Carrier:</strong><br />
                              {order.carrier}
                            </p>
                          </Col>
                        </Row>
                      )}
                      {order.shipment_notes && (
                        <Row>
                          <Col lg="12">
                            <p className="mb-2">
                              <strong>Shipment Notes:</strong><br />
                              {order.shipment_notes}
                            </p>
                          </Col>
                        </Row>
                      )}
                    </div>
                  </Col>
                  
                  <Col md="6">
                    <h6 className="heading-small text-muted mb-3">Customer Information</h6>
                    <div className="pl-lg-4">
                      <Row>
                        <Col lg="12">
                          <p className="mb-2">
                            <strong>Customer Name:</strong><br />
                            {order.customer_name}
                          </p>
                        </Col>
                      </Row>
                      <Row>
                        <Col lg="6">
                          <p className="mb-2">
                            <strong>Email:</strong><br />
                            {order.customer_email}
                          </p>
                        </Col>
                        <Col lg="6">
                          <p className="mb-2">
                            <strong>Phone:</strong><br />
                            {order.customer_phone || 'N/A'}
                          </p>
                        </Col>
                      </Row>
                      <Row>
                        <Col lg="12">
                          <p className="mb-3">
                            <strong>Customer Notes:</strong><br />
                            {order.customer_notes || 'No notes provided'}
                          </p>
                        </Col>
                      </Row>
                    </div>
                  </Col>
                </Row>
                
                <hr className="my-4" />
                
                <Row>
                  <Col md="6">
                    <h6 className="heading-small text-muted mb-3">Shipping Address</h6>
                    <div className="pl-lg-4">
                      <p className="mb-0">{order.shipping_address?.full_name}</p>
                      <p className="mb-0">{order.shipping_address?.address_line1}</p>
                      {order.shipping_address?.address_line2 && (
                        <p className="mb-0">{order.shipping_address.address_line2}</p>
                      )}
                      <p className="mb-0">
                        {order.shipping_address?.city}, {order.shipping_address?.state} {order.shipping_address?.postal_code}
                      </p>
                      <p className="mb-3">{order.shipping_address?.country}</p>
                    </div>
                  </Col>
                  
                  <Col md="6">
                    <h6 className="heading-small text-muted mb-3">Billing Address</h6>
                    <div className="pl-lg-4">
                      <p className="mb-0">{order.billing_address?.full_name}</p>
                      <p className="mb-0">{order.billing_address?.address_line1}</p>
                      {order.billing_address?.address_line2 && (
                        <p className="mb-0">{order.billing_address.address_line2}</p>
                      )}
                      <p className="mb-0">
                        {order.billing_address?.city}, {order.billing_address?.state} {order.billing_address?.postal_code}
                      </p>
                      <p className="mb-3">{order.billing_address?.country}</p>
                    </div>
                  </Col>
                </Row>
                
                <hr className="my-4" />
                
                <h6 className="heading-small text-muted mb-3">Order Items</h6>
                <div className="pl-lg-4">
                  <Table className="align-items-center table-flush" responsive>
                    <thead className="thead-light">
                      <tr>
                        <th scope="col" className="sort" data-sort="name">Product</th>
                        <th scope="col" className="sort" data-sort="budget">Price</th>
                        <th scope="col" className="sort" data-sort="status">Quantity</th>
                        <th scope="col" className="text-right">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      {order.items.map((item) => (
                        <tr key={item.id}>
                          <td>
                            <div className="d-flex align-items-center">
                              {item.product_image && (
                                <div className="mr-3">
                                  <img 
                                    src={item.product_image} 
                                    alt={item.product_name}
                                    style={{ width: "40px", height: "40px", objectFit: "cover", borderRadius: "4px" }}
                                  />
                                </div>
                              )}
                              <div>
                                <span className="font-weight-bold">{item.product_name}</span>
                                {item.variant_name && (
                                  <div><small className="text-muted">{item.variant_name}</small></div>
                                )}
                                {item.sku && (
                                  <div><small className="text-muted">SKU: {item.sku}</small></div>
                                )}
                              </div>
                            </div>
                          </td>
                          <td>{formatCurrency(item.price)}</td>
                          <td>{item.quantity}</td>
                          <td className="text-right">{formatCurrency(item.price * item.quantity)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                  
                  <Row className="mt-4">
                    <Col md="6"></Col>
                    <Col md="6">
                      <div className="bg-secondary p-3 rounded">
                        <div className="d-flex justify-content-between mb-2">
                          <span>Subtotal:</span>
                          <span>{formatCurrency(order.subtotal)}</span>
                        </div>
                        <div className="d-flex justify-content-between mb-2">
                          <span>Shipping:</span>
                          <span>{formatCurrency(order.shipping_cost)}</span>
                        </div>
                        {order.discount > 0 && (
                          <div className="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span>-{formatCurrency(order.discount)}</span>
                          </div>
                        )}
                        {order.tax > 0 && (
                          <div className="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>{formatCurrency(order.tax)}</span>
                          </div>
                        )}
                        <hr className="my-2" />
                        <div className="d-flex justify-content-between font-weight-bold">
                          <span>Total:</span>
                          <span>{formatCurrency(order.total_amount)}</span>
                        </div>
                      </div>
                    </Col>
                  </Row>
                </div>
              </CardBody>
              
              <CardFooter>
                <Row>
                  <Col md="6">
                    <Button
                      color="secondary"
                      onClick={() => navigate("/admin/orders")}
                    >
                      Back to Orders
                    </Button>
                  </Col>
                  <Col md="6" className="text-right">
                    <UncontrolledDropdown>
                      <DropdownToggle
                        caret
                        color="primary"
                        disabled={statusUpdateLoading}
                      >
                        {statusUpdateLoading ? 'Updating...' : 'Update Status'}
                      </DropdownToggle>
                      <DropdownMenu right>
                        {order.status !== 'pending' && (
                          <DropdownItem onClick={() => handleUpdateStatus('pending')}>
                            Mark as Pending
                          </DropdownItem>
                        )}
                        {order.status !== 'processing' && (
                          <DropdownItem onClick={() => handleUpdateStatus('processing')}>
                            Mark as Processing
                          </DropdownItem>
                        )}
                        {order.status !== 'shipped' && (
                          <DropdownItem onClick={() => handleUpdateStatus('shipped')}>
                            Mark as Shipped
                          </DropdownItem>
                        )}
                        {order.status !== 'delivered' && (
                          <DropdownItem onClick={() => handleUpdateStatus('delivered')}>
                            Mark as Delivered
                          </DropdownItem>
                        )}
                        <DropdownItem divider />
                        {order.status !== 'cancelled' && (
                          <DropdownItem onClick={() => handleUpdateStatus('cancelled')}>
                            Cancel Order
                          </DropdownItem>
                        )}
                      </DropdownMenu>
                    </UncontrolledDropdown>
                  </Col>
                </Row>
              </CardFooter>
            </Card>
          </Col>
        </Row>
      </Container>
      
      {/* Shipment Modal */}
      <Modal isOpen={shipmentModalOpen} toggle={toggleShipmentModal}>
        <ModalHeader toggle={toggleShipmentModal}>Add Shipment Information</ModalHeader>
        <Form onSubmit={handleShipmentSubmit}>
          <ModalBody>
            <FormGroup>
              <Label for="tracking_number">Tracking Number</Label>
              <Input
                id="tracking_number"
                name="tracking_number"
                placeholder="Enter tracking number"
                value={shipmentData.tracking_number}
                onChange={handleShipmentInputChange}
                required
              />
            </FormGroup>
            <FormGroup>
              <Label for="carrier">Carrier</Label>
              <Input
                id="carrier"
                name="carrier"
                type="select"
                value={shipmentData.carrier}
                onChange={handleShipmentInputChange}
                required
              >
                <option value="">Select a carrier</option>
                <option value="UPS">UPS</option>
                <option value="FedEx">FedEx</option>
                <option value="USPS">USPS</option>
                <option value="DHL">DHL</option>
                <option value="Other">Other</option>
              </Input>
            </FormGroup>
            <FormGroup>
              <Label for="notes">Shipment Notes (Optional)</Label>
              <Input
                id="notes"
                name="notes"
                type="textarea"
                rows="3"
                placeholder="Add any notes about this shipment"
                value={shipmentData.notes}
                onChange={handleShipmentInputChange}
              />
            </FormGroup>
          </ModalBody>
          <ModalFooter>
            <Button color="secondary" onClick={toggleShipmentModal}>
              Cancel
            </Button>
            <Button color="primary" type="submit" disabled={statusUpdateLoading}>
              {statusUpdateLoading ? 'Processing...' : 'Save & Mark as Shipped'}
            </Button>
          </ModalFooter>
        </Form>
      </Modal>
    </>
  );
};

export default OrderDetail;
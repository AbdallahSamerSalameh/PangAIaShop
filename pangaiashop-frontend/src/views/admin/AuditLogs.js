import React, { useState, useEffect } from "react";
import {
  Card,
  CardHeader,
  CardFooter,
  Table,
  Container,
  Row,
  Col,
  Badge,
  Button,
  InputGroup,
  Input,
  InputGroupAddon,
  InputGroupText,
  FormGroup,
} from "reactstrap";
import Header from "components/Headers/Header.js";

const AuditLogs = () => {
  const [loading, setLoading] = useState(true);
  const [auditLogs, setAuditLogs] = useState([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [filterByUser, setFilterByUser] = useState("");
  const [filterByAction, setFilterByAction] = useState("");

  useEffect(() => {
    // Simulate API call for audit logs
    setLoading(true);
    setTimeout(() => {
      const dummyAuditLogs = [
        {
          id: 1,
          user_id: 1,
          user_name: "Admin User",
          action: "product_create",
          description: "Created new product: Wireless Headphones",
          entity_type: "product",
          entity_id: 101,
          metadata: { product_name: "Wireless Headphones", price: 99.99 },
          ip_address: "192.168.1.100",
          created_at: "2025-04-23T10:30:00Z"
        },
        {
          id: 2,
          user_id: 1,
          user_name: "Admin User",
          action: "order_status_update",
          description: "Changed order status from 'Pending' to 'Processing'",
          entity_type: "order",
          entity_id: 5001,
          metadata: { order_id: 5001, old_status: "pending", new_status: "processing" },
          ip_address: "192.168.1.100",
          created_at: "2025-04-23T10:35:00Z"
        },
        {
          id: 3,
          user_id: 2,
          user_name: "Support Staff",
          action: "user_update",
          description: "Updated user information for: John Doe",
          entity_type: "user",
          entity_id: 1001,
          metadata: { user_email: "john.doe@example.com", fields_changed: ["phone_number"] },
          ip_address: "192.168.1.101",
          created_at: "2025-04-23T11:15:00Z"
        },
        {
          id: 4,
          user_id: 1,
          user_name: "Admin User",
          action: "product_update",
          description: "Updated product price: Smart Watch",
          entity_type: "product",
          entity_id: 102,
          metadata: { product_name: "Smart Watch", old_price: 149.99, new_price: 129.99 },
          ip_address: "192.168.1.100",
          created_at: "2025-04-23T12:05:00Z"
        },
        {
          id: 5,
          user_id: 3,
          user_name: "Marketing Manager",
          action: "promo_code_create",
          description: "Created new promo code: SUMMER25",
          entity_type: "promo_code",
          entity_id: 201,
          metadata: { code: "SUMMER25", discount: "25%" },
          ip_address: "192.168.1.102",
          created_at: "2025-04-23T14:30:00Z"
        }
      ];
      setAuditLogs(dummyAuditLogs);
      setLoading(false);
    }, 1000);
  }, []);

  const getActionBadge = (action) => {
    if (action.includes("create")) return "success";
    if (action.includes("update")) return "info";
    if (action.includes("delete")) return "danger";
    return "primary";
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
  };

  const formatActionText = (action) => {
    return action
      .split("_")
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ");
  };

  const handleSearch = (e) => {
    setSearchTerm(e.target.value);
  };

  const filteredLogs = auditLogs.filter(log => {
    const matchesSearch = searchTerm === "" || 
      log.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
      log.user_name.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesUser = filterByUser === "" || log.user_name === filterByUser;
    
    const matchesAction = filterByAction === "" || log.action === filterByAction;
    
    return matchesSearch && matchesUser && matchesAction;
  });

  // Get unique users for filter
  const uniqueUsers = [...new Set(auditLogs.map(log => log.user_name))];
  
  // Get unique actions for filter
  const uniqueActions = [...new Set(auditLogs.map(log => log.action))];

  return (
    <>
      <Header />
      <Container className="mt--7" fluid>
        <Row className="mb-3">
          <Col md="4">
            <FormGroup>
              <InputGroup className="input-group-alternative">
                <InputGroupAddon addonType="prepend">
                  <InputGroupText>
                    <i className="fas fa-search" />
                  </InputGroupText>
                </InputGroupAddon>
                <Input
                  placeholder="Search..."
                  type="text"
                  value={searchTerm}
                  onChange={handleSearch}
                />
              </InputGroup>
            </FormGroup>
          </Col>
          <Col md="3">
            <FormGroup>
              <Input
                type="select"
                value={filterByUser}
                onChange={(e) => setFilterByUser(e.target.value)}
              >
                <option value="">Filter by User</option>
                {uniqueUsers.map((user, index) => (
                  <option key={index} value={user}>{user}</option>
                ))}
              </Input>
            </FormGroup>
          </Col>
          <Col md="3">
            <FormGroup>
              <Input
                type="select"
                value={filterByAction}
                onChange={(e) => setFilterByAction(e.target.value)}
              >
                <option value="">Filter by Action</option>
                {uniqueActions.map((action, index) => (
                  <option key={index} value={action}>{formatActionText(action)}</option>
                ))}
              </Input>
            </FormGroup>
          </Col>
          <Col md="2">
            <Button
              color="secondary"
              onClick={() => {
                setSearchTerm("");
                setFilterByUser("");
                setFilterByAction("");
              }}
              block
            >
              Clear Filters
            </Button>
          </Col>
        </Row>
        <Row>
          <div className="col">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Audit Logs</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button
                      color="primary"
                      onClick={() => window.print()}
                      size="sm"
                    >
                      Export Logs
                    </Button>
                  </Col>
                </Row>
              </CardHeader>

              <Table className="align-items-center table-flush" responsive>
                <thead className="thead-light">
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">User</th>
                    <th scope="col">Action</th>
                    <th scope="col">Description</th>
                    <th scope="col">IP Address</th>
                    <th scope="col">Date & Time</th>
                    <th scope="col">Details</th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr>
                      <td colSpan="7" className="text-center">
                        Loading audit logs...
                      </td>
                    </tr>
                  ) : filteredLogs.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="text-center">
                        No audit logs found
                      </td>
                    </tr>
                  ) : (
                    filteredLogs.map((log) => (
                      <tr key={log.id}>
                        <td>{log.id}</td>
                        <td>{log.user_name}</td>
                        <td>
                          <Badge color={getActionBadge(log.action)}>
                            {formatActionText(log.action)}
                          </Badge>
                        </td>
                        <td>
                          {log.description}
                        </td>
                        <td>
                          {log.ip_address}
                        </td>
                        <td>{formatDate(log.created_at)}</td>
                        <td>
                          <Button
                            color="info"
                            size="sm"
                            onClick={() => alert(`View details of log ${log.id}`)}
                          >
                            View
                          </Button>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </Table>
              <CardFooter className="py-4">
                <nav aria-label="...">
                  {/* Pagination to be implemented */}
                </nav>
              </CardFooter>
            </Card>
          </div>
        </Row>
      </Container>
    </>
  );
};

export default AuditLogs;
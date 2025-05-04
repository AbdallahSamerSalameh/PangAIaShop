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
} from "reactstrap";
import { Link } from "react-router-dom";
import Header from "components/Headers/Header.js";

const SupportTickets = () => {
  const [loading, setLoading] = useState(true);
  const [tickets, setTickets] = useState([]);

  useEffect(() => {
    // Simulate API call for support tickets
    setLoading(true);
    setTimeout(() => {
      const dummyTickets = [
        {
          id: 1,
          subject: "Order delivery issue",
          status: "open",
          priority: "high",
          user_name: "John Doe",
          created_at: "2025-04-22T10:30:00",
          updated_at: "2025-04-22T14:20:00"
        },
        {
          id: 2,
          subject: "Payment not processed",
          status: "pending",
          priority: "medium",
          user_name: "Jane Smith",
          created_at: "2025-04-21T08:15:00",
          updated_at: "2025-04-21T09:45:00"
        },
        {
          id: 3,
          subject: "Product information inquiry",
          status: "closed",
          priority: "low",
          user_name: "Robert Johnson",
          created_at: "2025-04-20T16:30:00",
          updated_at: "2025-04-21T10:15:00"
        }
      ];
      setTickets(dummyTickets);
      setLoading(false);
    }, 1000);
  }, []);

  const getStatusBadge = (status) => {
    switch (status) {
      case "open":
        return "success";
      case "pending":
        return "warning";
      case "closed":
        return "danger";
      default:
        return "primary";
    }
  };

  const getPriorityBadge = (priority) => {
    switch (priority) {
      case "high":
        return "danger";
      case "medium":
        return "warning";
      case "low":
        return "info";
      default:
        return "secondary";
    }
  };

  return (
    <>
      <Header />
      <Container className="mt--7" fluid>
        <Row>
          <div className="col">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Support Tickets</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button color="primary" size="sm">
                      Filter Tickets
                    </Button>
                  </Col>
                </Row>
              </CardHeader>

              <Table className="align-items-center table-flush" responsive>
                <thead className="thead-light">
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Subject</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Status</th>
                    <th scope="col">Priority</th>
                    <th scope="col">Created</th>
                    <th scope="col">Last Updated</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr>
                      <td colSpan="8" className="text-center">
                        Loading tickets...
                      </td>
                    </tr>
                  ) : tickets.length === 0 ? (
                    <tr>
                      <td colSpan="8" className="text-center">
                        No support tickets found
                      </td>
                    </tr>
                  ) : (
                    tickets.map((ticket) => (
                      <tr key={ticket.id}>
                        <td>#{ticket.id}</td>
                        <td>
                          <Link to={`/admin/support-tickets/${ticket.id}`}>
                            {ticket.subject}
                          </Link>
                        </td>
                        <td>{ticket.user_name}</td>
                        <td>
                          <Badge color={getStatusBadge(ticket.status)}>
                            {ticket.status}
                          </Badge>
                        </td>
                        <td>
                          <Badge color={getPriorityBadge(ticket.priority)}>
                            {ticket.priority}
                          </Badge>
                        </td>
                        <td>{new Date(ticket.created_at).toLocaleString()}</td>
                        <td>{new Date(ticket.updated_at).toLocaleString()}</td>
                        <td>
                          <Link to={`/admin/support-tickets/${ticket.id}`}>
                            <Button color="primary" size="sm">
                              View
                            </Button>
                          </Link>
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

export default SupportTickets;
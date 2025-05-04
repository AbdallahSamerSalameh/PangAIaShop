import React, { useState, useEffect } from "react";
import {
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Container,
  Row,
  Col,
  Button,
  Badge,
  Form,
  FormGroup,
  Input,
  Media,
} from "reactstrap";
import { useNavigate, useParams } from "react-router-dom";
import Header from "components/Headers/Header.js";

const SupportTicketDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [ticket, setTicket] = useState(null);
  const [messages, setMessages] = useState([]);
  const [reply, setReply] = useState("");

  useEffect(() => {
    // Simulate API call for ticket details
    setLoading(true);
    setTimeout(() => {
      const dummyTicket = {
        id: parseInt(id),
        subject: "Order delivery issue",
        description: "I ordered product #12345 three days ago and still haven't received any shipping confirmation. Can you please check the status?",
        status: "open",
        priority: "high",
        user_id: 42,
        user_name: "John Doe",
        user_email: "john.doe@example.com",
        created_at: "2025-04-22T10:30:00",
        updated_at: "2025-04-22T14:20:00",
        order_id: 5678
      };

      const dummyMessages = [
        {
          id: 101,
          ticket_id: parseInt(id),
          message: "I ordered product #12345 three days ago and still haven't received any shipping confirmation. Can you please check the status?",
          user_id: 42,
          user_name: "John Doe",
          is_admin: false,
          created_at: "2025-04-22T10:30:00"
        },
        {
          id: 102,
          ticket_id: parseInt(id),
          message: "Hello John, thank you for contacting us. I'm looking into your order now and will get back to you shortly.",
          user_id: 1,
          user_name: "Support Agent",
          is_admin: true,
          created_at: "2025-04-22T11:15:00"
        },
        {
          id: 103,
          ticket_id: parseInt(id),
          message: "I've checked your order #5678 and it looks like there was a delay at our warehouse. I've contacted the fulfillment team and they will process your order today. You should receive a shipping confirmation by the end of the day.",
          user_id: 1,
          user_name: "Support Agent",
          is_admin: true,
          created_at: "2025-04-22T14:20:00"
        }
      ];

      setTicket(dummyTicket);
      setMessages(dummyMessages);
      setLoading(false);
    }, 1000);
  }, [id]);

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

  const handleStatusChange = (newStatus) => {
    console.log(`Changing ticket status to: ${newStatus}`);
    setTicket({ ...ticket, status: newStatus });
  };

  const handlePriorityChange = (newPriority) => {
    console.log(`Changing ticket priority to: ${newPriority}`);
    setTicket({ ...ticket, priority: newPriority });
  };

  const handleSubmitReply = (e) => {
    e.preventDefault();
    if (!reply.trim()) return;

    const newMessage = {
      id: messages.length + 104,
      ticket_id: parseInt(id),
      message: reply,
      user_id: 1,
      user_name: "Support Agent",
      is_admin: true,
      created_at: new Date().toISOString()
    };

    setMessages([...messages, newMessage]);
    setReply("");
  };

  if (loading) {
    return (
      <>
        <Header />
        <Container className="mt--7" fluid>
          <Row>
            <Col>
              <Card className="shadow">
                <CardBody className="text-center">
                  <div className="p-4">Loading ticket data...</div>
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
          <Col lg="4">
            <Card className="shadow mb-4">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <Col>
                    <h3 className="mb-0">Ticket Details</h3>
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <div className="mb-3">
                  <h6 className="text-uppercase text-muted mb-1">Ticket ID</h6>
                  <div className="h2">#{ticket.id}</div>
                </div>

                <div className="mb-3">
                  <h6 className="text-uppercase text-muted mb-1">Subject</h6>
                  <div>{ticket.subject}</div>
                </div>

                <div className="mb-3">
                  <h6 className="text-uppercase text-muted mb-1">Status</h6>
                  <Badge color={getStatusBadge(ticket.status)}>
                    {ticket.status}
                  </Badge>
                  <div className="mt-2">
                    <Button
                      color="outline-success"
                      size="sm"
                      className="mr-2"
                      onClick={() => handleStatusChange("open")}
                    >
                      Open
                    </Button>
                    <Button
                      color="outline-warning"
                      size="sm"
                      className="mr-2"
                      onClick={() => handleStatusChange("pending")}
                    >
                      Pending
                    </Button>
                    <Button
                      color="outline-danger"
                      size="sm"
                      onClick={() => handleStatusChange("closed")}
                    >
                      Close
                    </Button>
                  </div>
                </div>

                <div className="mb-3">
                  <h6 className="text-uppercase text-muted mb-1">Priority</h6>
                  <Badge color={getPriorityBadge(ticket.priority)}>
                    {ticket.priority}
                  </Badge>
                  <div className="mt-2">
                    <Button
                      color="outline-danger"
                      size="sm"
                      className="mr-2"
                      onClick={() => handlePriorityChange("high")}
                    >
                      High
                    </Button>
                    <Button
                      color="outline-warning"
                      size="sm"
                      className="mr-2"
                      onClick={() => handlePriorityChange("medium")}
                    >
                      Medium
                    </Button>
                    <Button
                      color="outline-info"
                      size="sm"
                      onClick={() => handlePriorityChange("low")}
                    >
                      Low
                    </Button>
                  </div>
                </div>

                <div className="mb-3">
                  <h6 className="text-uppercase text-muted mb-1">Customer</h6>
                  <div>{ticket.user_name}</div>
                  <div>{ticket.user_email}</div>
                </div>

                {ticket.order_id && (
                  <div className="mb-3">
                    <h6 className="text-uppercase text-muted mb-1">Related Order</h6>
                    <div>
                      <a href={`/admin/orders/${ticket.order_id}`}>
                        Order #{ticket.order_id}
                      </a>
                    </div>
                  </div>
                )}

                <div className="mb-3">
                  <h6 className="text-uppercase text-muted mb-1">Created</h6>
                  <div>{new Date(ticket.created_at).toLocaleString()}</div>
                </div>

                <div className="mb-3">
                  <h6 className="text-uppercase text-muted mb-1">Updated</h6>
                  <div>{new Date(ticket.updated_at).toLocaleString()}</div>
                </div>
              </CardBody>
              <CardFooter>
                <Button
                  color="primary"
                  block
                  onClick={() => navigate("/admin/support-tickets")}
                >
                  Back to Tickets
                </Button>
              </CardFooter>
            </Card>
          </Col>

          <Col lg="8">
            <Card className="shadow mb-4">
              <CardHeader className="border-0">
                <h3 className="mb-0">Conversation</h3>
              </CardHeader>
              <CardBody>
                <div className="conversation">
                  {messages.map((message) => (
                    <Media className={`mb-4 ${message.is_admin ? 'admin-message' : 'customer-message'}`} key={message.id}>
                      <div className="media-body ml-3">
                        <div className="d-flex justify-content-between align-items-center mb-1">
                          <h6 className="mb-0">
                            {message.user_name}
                            {message.is_admin && (
                              <Badge color="primary" className="ml-2">Admin</Badge>
                            )}
                          </h6>
                          <small className="text-muted">
                            {new Date(message.created_at).toLocaleString()}
                          </small>
                        </div>
                        <p className="mb-0">{message.message}</p>
                      </div>
                    </Media>
                  ))}
                </div>
              </CardBody>
              <CardFooter>
                <Form onSubmit={handleSubmitReply}>
                  <FormGroup>
                    <Input
                      type="textarea"
                      placeholder="Type your reply here..."
                      rows="4"
                      value={reply}
                      onChange={(e) => setReply(e.target.value)}
                      required
                    />
                  </FormGroup>
                  <Button color="primary" type="submit">
                    Send Reply
                  </Button>
                </Form>
              </CardFooter>
            </Card>
          </Col>
        </Row>
      </Container>
    </>
  );
};

export default SupportTicketDetail;
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
  ButtonGroup,
  UncontrolledDropdown,
  DropdownToggle,
  DropdownMenu,
  DropdownItem,
} from "reactstrap";
import Header from "components/Headers/Header.js";

const ReviewModeration = () => {
  const [loading, setLoading] = useState(true);
  const [reviews, setReviews] = useState([]);
  const [filter, setFilter] = useState("pending");

  useEffect(() => {
    // Simulate API call for reviews
    setLoading(true);
    setTimeout(() => {
      const dummyReviews = [
        {
          id: 1,
          product_id: 101,
          product_name: "Wireless Headphones",
          product_image: "/img/products/headphones.jpg",
          user_id: 1001,
          user_name: "John Smith",
          rating: 4,
          title: "Great product, very comfortable",
          content: "These headphones are really comfortable to wear for long periods. The sound quality is excellent and battery life is impressive. Highly recommended!",
          status: "approved",
          created_at: "2025-04-20T14:30:00Z",
          updated_at: "2025-04-20T15:00:00Z"
        },
        {
          id: 2,
          product_id: 102,
          product_name: "Smart Watch",
          product_image: "/img/products/smartwatch.jpg",
          user_id: 1002,
          user_name: "Jane Doe",
          rating: 2,
          title: "Not as advertised, disappointing",
          content: "The battery life is much shorter than advertised, and the fitness tracking is inaccurate. I also found the interface to be confusing and difficult to navigate. Would not recommend.",
          status: "pending",
          created_at: "2025-04-22T09:15:00Z",
          updated_at: "2025-04-22T09:15:00Z"
        },
        {
          id: 3,
          product_id: 103,
          product_name: "Bluetooth Speaker",
          product_image: "/img/products/speaker.jpg",
          user_id: 1003,
          user_name: "Robert Johnson",
          rating: 1,
          title: "Absolute garbage product",
          content: "This speaker is terrible. The sound quality is awful, it constantly disconnects, and the battery died after just one month. Complete waste of money! DO NOT BUY THIS PIECE OF JUNK!!!",
          status: "rejected",
          created_at: "2025-04-21T16:45:00Z",
          updated_at: "2025-04-21T17:30:00Z"
        },
        {
          id: 4,
          product_id: 104,
          product_name: "Laptop Backpack",
          product_image: "/img/products/backpack.jpg",
          user_id: 1004,
          user_name: "Sarah Wilson",
          rating: 5,
          title: "Perfect backpack for daily use",
          content: "I've been using this backpack daily for commuting to work and it's perfect. Lots of compartments, waterproof, and very comfortable to carry. The laptop sleeve fits my 15-inch laptop perfectly.",
          status: "pending",
          created_at: "2025-04-23T11:20:00Z",
          updated_at: "2025-04-23T11:20:00Z"
        }
      ];
      setReviews(dummyReviews);
      setLoading(false);
    }, 1000);
  }, []);

  const filteredReviews = reviews.filter(review => {
    if (filter === "all") return true;
    return review.status === filter;
  });

  const handleStatusChange = (reviewId, newStatus) => {
    setReviews(
      reviews.map(review =>
        review.id === reviewId
          ? { ...review, status: newStatus, updated_at: new Date().toISOString() }
          : review
      )
    );
  };

  const getStatusBadge = (status) => {
    switch (status) {
      case "approved":
        return "success";
      case "pending":
        return "warning";
      case "rejected":
        return "danger";
      default:
        return "primary";
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
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
                    <h3 className="mb-0">Review Moderation</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <ButtonGroup>
                      <Button
                        color={filter === "all" ? "primary" : "secondary"}
                        onClick={() => setFilter("all")}
                        size="sm"
                      >
                        All
                      </Button>
                      <Button
                        color={filter === "pending" ? "primary" : "secondary"}
                        onClick={() => setFilter("pending")}
                        size="sm"
                      >
                        Pending
                      </Button>
                      <Button
                        color={filter === "approved" ? "primary" : "secondary"}
                        onClick={() => setFilter("approved")}
                        size="sm"
                      >
                        Approved
                      </Button>
                      <Button
                        color={filter === "rejected" ? "primary" : "secondary"}
                        onClick={() => setFilter("rejected")}
                        size="sm"
                      >
                        Rejected
                      </Button>
                    </ButtonGroup>
                  </Col>
                </Row>
              </CardHeader>

              <Table className="align-items-center table-flush" responsive>
                <thead className="thead-light">
                  <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Rating</th>
                    <th scope="col">Review</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr>
                      <td colSpan="7" className="text-center">
                        Loading reviews...
                      </td>
                    </tr>
                  ) : filteredReviews.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="text-center">
                        No reviews found
                      </td>
                    </tr>
                  ) : (
                    filteredReviews.map((review) => (
                      <tr key={review.id}>
                        <td>
                          <div className="d-flex align-items-center">
                            <div>{review.product_name}</div>
                          </div>
                        </td>
                        <td>{review.user_name}</td>
                        <td>
                          <div className="d-flex">
                            {[...Array(5)].map((_, i) => (
                              <i
                                key={i}
                                className={`fas fa-star ${
                                  i < review.rating ? "text-warning" : "text-muted"
                                }`}
                              />
                            ))}
                          </div>
                        </td>
                        <td>
                          <div>
                            <strong>{review.title}</strong>
                          </div>
                          <div className="text-muted text-sm">
                            {review.content.length > 50
                              ? `${review.content.substring(0, 50)}...`
                              : review.content}
                          </div>
                        </td>
                        <td>
                          <Badge color={getStatusBadge(review.status)}>
                            {review.status}
                          </Badge>
                        </td>
                        <td>{formatDate(review.created_at)}</td>
                        <td>
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
                                onClick={() => handleStatusChange(review.id, "approved")}
                                disabled={review.status === "approved"}
                              >
                                Approve
                              </DropdownItem>
                              <DropdownItem
                                onClick={() => handleStatusChange(review.id, "rejected")}
                                disabled={review.status === "rejected"}
                              >
                                Reject
                              </DropdownItem>
                              <DropdownItem
                                onClick={() => handleStatusChange(review.id, "pending")}
                                disabled={review.status === "pending"}
                              >
                                Mark as Pending
                              </DropdownItem>
                              <DropdownItem divider />
                              <DropdownItem
                                onClick={() => alert(`View full review: ${review.id}`)}
                              >
                                View Full Review
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

export default ReviewModeration;
import React, { useState, useEffect } from "react";
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
  Button,
  Col,
  Badge,
} from "reactstrap";
import { Link } from "react-router-dom";
import Header from "components/Headers/Header.js";

const PromoCodeList = () => {
  const [loading, setLoading] = useState(true);
  const [promoCodes, setPromoCodes] = useState([]);

  useEffect(() => {
    // Simulate API call for promo codes
    setLoading(true);
    setTimeout(() => {
      const dummyPromoCodes = [
        {
          id: 1,
          code: "WELCOME20",
          discount_percent: 20,
          discount_amount: null,
          min_purchase: 50,
          start_date: "2025-01-01T00:00:00Z",
          end_date: "2025-12-31T23:59:59Z",
          usage_limit: 1000,
          used_count: 342,
          active: true
        },
        {
          id: 2,
          code: "SPRING25",
          discount_percent: 25,
          discount_amount: null,
          min_purchase: 100,
          start_date: "2025-03-01T00:00:00Z",
          end_date: "2025-05-31T23:59:59Z",
          usage_limit: 500,
          used_count: 89,
          active: true
        },
        {
          id: 3,
          code: "FLAT30",
          discount_percent: null,
          discount_amount: 30,
          min_purchase: 150,
          start_date: "2025-04-01T00:00:00Z",
          end_date: "2025-04-30T23:59:59Z",
          usage_limit: 200,
          used_count: 45,
          active: true
        }
      ];
      setPromoCodes(dummyPromoCodes);
      setLoading(false);
    }, 1000);
  }, []);

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
  };

  const getDiscountDisplay = (promoCode) => {
    if (promoCode.discount_percent) {
      return `${promoCode.discount_percent}%`;
    } else if (promoCode.discount_amount) {
      return `$${promoCode.discount_amount}`;
    }
    return "-";
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
                    <h3 className="mb-0">Promo Codes</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Link to="/admin/promo-codes/create">
                      <Button color="primary" size="sm">
                        Add Promo Code
                      </Button>
                    </Link>
                  </Col>
                </Row>
              </CardHeader>

              <Table className="align-items-center table-flush" responsive>
                <thead className="thead-light">
                  <tr>
                    <th scope="col">Code</th>
                    <th scope="col">Discount</th>
                    <th scope="col">Min Purchase</th>
                    <th scope="col">Valid From</th>
                    <th scope="col">Valid Until</th>
                    <th scope="col">Usage</th>
                    <th scope="col">Status</th>
                    <th scope="col" />
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr>
                      <td colSpan="8" className="text-center">
                        Loading promo codes...
                      </td>
                    </tr>
                  ) : promoCodes.length === 0 ? (
                    <tr>
                      <td colSpan="8" className="text-center">
                        No promo codes found
                      </td>
                    </tr>
                  ) : (
                    promoCodes.map((promoCode) => (
                      <tr key={promoCode.id}>
                        <td>
                          <Link to={`/admin/promo-codes/edit/${promoCode.id}`}>
                            {promoCode.code}
                          </Link>
                        </td>
                        <td>{getDiscountDisplay(promoCode)}</td>
                        <td>${promoCode.min_purchase || 0}</td>
                        <td>{formatDate(promoCode.start_date)}</td>
                        <td>{formatDate(promoCode.end_date)}</td>
                        <td>
                          {promoCode.used_count} / {promoCode.usage_limit || "Unlimited"}
                        </td>
                        <td>
                          <Badge color={promoCode.active ? 'success' : 'danger'}>
                            {promoCode.active ? 'Active' : 'Inactive'}
                          </Badge>
                        </td>
                        <td className="text-right">
                          <UncontrolledDropdown>
                            <DropdownToggle
                              className="btn-icon-only text-light"
                              href="#"
                              role="button"
                              size="sm"
                              color=""
                              onClick={(e) => e.preventDefault()}
                            >
                              <i className="fas fa-ellipsis-v" />
                            </DropdownToggle>
                            <DropdownMenu className="dropdown-menu-arrow" right>
                              <Link to={`/admin/promo-codes/edit/${promoCode.id}`}>
                                <DropdownItem>Edit</DropdownItem>
                              </Link>
                              <DropdownItem
                                onClick={() => {
                                  // Delete functionality to be implemented
                                  alert(`Delete promo code: ${promoCode.id}`);
                                }}
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

export default PromoCodeList;
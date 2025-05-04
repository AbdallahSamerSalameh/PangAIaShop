import React, { useState } from "react";
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
  InputGroup,
  InputGroupAddon,
  InputGroupText,
} from "reactstrap";
import { useNavigate } from "react-router-dom";
import Header from "components/Headers/Header.js";

const PromoCodeCreate = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    code: "",
    discount_type: "percent",
    discount_value: "",
    min_purchase: "",
    start_date: "",
    end_date: "",
    usage_limit: "",
    active: true
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData({
      ...formData,
      [name]: type === "checkbox" ? checked : value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      // API call would go here to create the promo code
      console.log("Creating promo code:", formData);
      setTimeout(() => {
        navigate("/admin/promo-codes");
      }, 1000);
    } catch (err) {
      setError("Failed to create promo code");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

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
                    <h3 className="mb-0">Create New Promo Code</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button
                      color="primary"
                      onClick={() => navigate("/admin/promo-codes")}
                      size="sm"
                    >
                      Back to Promo Codes
                    </Button>
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                {error && (
                  <div className="alert alert-danger" role="alert">
                    {error}
                  </div>
                )}
                <Form onSubmit={handleSubmit}>
                  <div className="pl-lg-4">
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-code"
                          >
                            Promo Code
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-code"
                            name="code"
                            placeholder="SUMMER25"
                            type="text"
                            value={formData.code}
                            onChange={handleChange}
                            required
                          />
                          <small className="text-muted">
                            Enter a unique code without spaces (e.g., SUMMER25, WELCOME10)
                          </small>
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-discount-type"
                          >
                            Discount Type
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-discount-type"
                            name="discount_type"
                            type="select"
                            value={formData.discount_type}
                            onChange={handleChange}
                          >
                            <option value="percent">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                          </Input>
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-discount-value"
                          >
                            Discount Value
                          </label>
                          <InputGroup className="input-group-alternative">
                            <Input
                              className="form-control-alternative"
                              id="input-discount-value"
                              name="discount_value"
                              placeholder={formData.discount_type === "percent" ? "25" : "10.00"}
                              type="number"
                              step={formData.discount_type === "percent" ? "1" : "0.01"}
                              min="0"
                              value={formData.discount_value}
                              onChange={handleChange}
                              required
                            />
                            <InputGroupAddon addonType="append">
                              <InputGroupText>
                                {formData.discount_type === "percent" ? "%" : "$"}
                              </InputGroupText>
                            </InputGroupAddon>
                          </InputGroup>
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-min-purchase"
                          >
                            Minimum Purchase (Optional)
                          </label>
                          <InputGroup className="input-group-alternative">
                            <InputGroupAddon addonType="prepend">
                              <InputGroupText>$</InputGroupText>
                            </InputGroupAddon>
                            <Input
                              className="form-control-alternative"
                              id="input-min-purchase"
                              name="min_purchase"
                              placeholder="50.00"
                              type="number"
                              step="0.01"
                              min="0"
                              value={formData.min_purchase}
                              onChange={handleChange}
                            />
                          </InputGroup>
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-usage-limit"
                          >
                            Usage Limit (Optional)
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-usage-limit"
                            name="usage_limit"
                            placeholder="Leave blank for unlimited"
                            type="number"
                            min="1"
                            value={formData.usage_limit}
                            onChange={handleChange}
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-start-date"
                          >
                            Start Date
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-start-date"
                            name="start_date"
                            type="date"
                            value={formData.start_date}
                            onChange={handleChange}
                            required
                          />
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-end-date"
                          >
                            End Date
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-end-date"
                            name="end_date"
                            type="date"
                            value={formData.end_date}
                            onChange={handleChange}
                            required
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup check>
                          <Input
                            className="form-check-input"
                            id="input-active"
                            name="active"
                            type="checkbox"
                            checked={formData.active}
                            onChange={handleChange}
                          />
                          <label
                            className="form-control-label"
                            htmlFor="input-active"
                          >
                            Active
                          </label>
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row className="mt-4">
                      <Col>
                        <Button
                          color="primary"
                          type="submit"
                          disabled={loading}
                        >
                          {loading ? "Creating..." : "Create Promo Code"}
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

export default PromoCodeCreate;
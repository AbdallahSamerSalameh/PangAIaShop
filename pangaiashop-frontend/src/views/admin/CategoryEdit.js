import React, { useState, useEffect } from "react";
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
} from "reactstrap";
import { useNavigate, useParams } from "react-router-dom";
import Header from "components/Headers/Header.js";
import { getCategory, updateCategory } from "services/category.service";

const CategoryEdit = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: "",
    description: "",
    active: true
  });
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchCategory = async () => {
      try {
        setLoadingData(true);
        const response = await getCategory(id);
        setFormData({
          name: response.data.name,
          description: response.data.description || "",
          active: response.data.active || false,
        });
      } catch (err) {
        setError("Failed to load category data");
        console.error(err);
      } finally {
        setLoadingData(false);
      }
    };

    fetchCategory();
  }, [id]);

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
      await updateCategory(id, formData);
      navigate("/admin/categories");
    } catch (err) {
      setError(err.response?.data?.message || "Failed to update category");
    } finally {
      setLoading(false);
    }
  };

  if (loadingData) {
    return (
      <>
        <Header />
        <Container className="mt--7" fluid>
          <Row>
            <Col>
              <Card className="shadow">
                <CardBody className="text-center">
                  <div className="p-4">Loading category data...</div>
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
                    <h3 className="mb-0">Edit Category</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button
                      color="primary"
                      onClick={() => navigate("/admin/categories")}
                      size="sm"
                    >
                      Back to Categories
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
                            htmlFor="input-name"
                          >
                            Category Name
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-name"
                            name="name"
                            placeholder="Enter category name"
                            type="text"
                            value={formData.name}
                            onChange={handleChange}
                            required
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col>
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-description"
                          >
                            Description
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-description"
                            name="description"
                            placeholder="Enter category description"
                            type="textarea"
                            rows="4"
                            value={formData.description}
                            onChange={handleChange}
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
                          {loading ? "Updating..." : "Update Category"}
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

export default CategoryEdit;
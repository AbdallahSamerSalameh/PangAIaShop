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
import { getUser, updateUser } from "services/user.service";

const UserEdit = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    role: "customer",
    active: true,
    password: "",
    password_confirmation: "",
  });
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        setLoadingData(true);
        const response = await getUser(id);
        setFormData({
          name: response.data.name,
          email: response.data.email,
          role: response.data.role || "customer",
          active: response.data.active,
          password: "",
          password_confirmation: "",
        });
      } catch (err) {
        setError("Failed to load user data");
        console.error(err);
      } finally {
        setLoadingData(false);
      }
    };

    fetchUser();
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
    
    if (formData.password && formData.password !== formData.password_confirmation) {
      setError("Passwords do not match");
      return;
    }
    
    setLoading(true);
    setError(null);

    // Create a copy without password fields if they're empty
    const userDataToUpdate = {...formData};
    if (!userDataToUpdate.password) {
      delete userDataToUpdate.password;
      delete userDataToUpdate.password_confirmation;
    }

    try {
      await updateUser(id, userDataToUpdate);
      navigate("/admin/users");
    } catch (err) {
      setError(err.response?.data?.message || "Failed to update user");
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
                  <div className="p-4">Loading user data...</div>
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
                    <h3 className="mb-0">Edit User</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    <Button
                      color="primary"
                      onClick={() => navigate("/admin/users")}
                      size="sm"
                    >
                      Back to Users
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
                            Full Name
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-name"
                            name="name"
                            placeholder="Enter full name"
                            type="text"
                            value={formData.name}
                            onChange={handleChange}
                            required
                          />
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-email"
                          >
                            Email
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-email"
                            name="email"
                            placeholder="Enter email address"
                            type="email"
                            value={formData.email}
                            onChange={handleChange}
                            required
                          />
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-password"
                          >
                            New Password (leave blank to keep current)
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-password"
                            name="password"
                            placeholder="Enter new password"
                            type="password"
                            value={formData.password}
                            onChange={handleChange}
                          />
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <label
                            className="form-control-label"
                            htmlFor="input-password-confirmation"
                          >
                            Confirm New Password
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-password-confirmation"
                            name="password_confirmation"
                            placeholder="Confirm new password"
                            type="password"
                            value={formData.password_confirmation}
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
                            htmlFor="input-role"
                          >
                            Role
                          </label>
                          <Input
                            className="form-control-alternative"
                            id="input-role"
                            name="role"
                            type="select"
                            value={formData.role}
                            onChange={handleChange}
                          >
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                          </Input>
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup check className="mt-4">
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
                          {loading ? "Updating..." : "Update User"}
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

export default UserEdit;
import React, { useState, useEffect } from 'react';
import {
  Card,
  CardHeader,
  CardBody,
  Container,
  Row,
  Col,
  Button,
  Form,
  FormGroup,
  Label,
  Input,
  Alert,
  Spinner
} from 'reactstrap';
import { useNavigate, useParams } from 'react-router-dom';
import Header from 'components/Headers/Header.js';
import adminService from '../../services/admin.service';
import authService from '../../services/auth.service';

const AdminEdit = () => {
  const { id } = useParams();
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'admin',
    phone_number: '',
    is_active: true
  });
  const [loading, setLoading] = useState(true);
  const [updating, setUpdating] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);
  const [validationErrors, setValidationErrors] = useState({});
  const [currentUser, setCurrentUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    // Get current user
    const user = authService.getCurrentUser();
    setCurrentUser(user);
    
    // Only Super Admins should be able to access this page
    if (user && user.is_super_admin !== true && user.role !== 'Super Admin') {
      navigate('/admin/index');
      return;
    }
    
    // Load admin data
    loadAdminData();
  }, [id]);

  const loadAdminData = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await adminService.getAdmin(id);
      const admin = response.data.data;
      
      setFormData({
        username: admin.username || '',
        email: admin.email || '',
        role: admin.role || 'admin',
        phone_number: admin.phone_number || '',
        is_active: admin.is_active === undefined ? true : admin.is_active,
        password: '',
        password_confirmation: ''
      });
      
    } catch (err) {
      console.error('Error loading admin data:', err);
      setError('Failed to load admin data. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData({
      ...formData,
      [name]: type === 'checkbox' ? checked : value
    });
    
    // Clear validation error for this field
    if (validationErrors[name]) {
      setValidationErrors({
        ...validationErrors,
        [name]: null
      });
    }
  };

  const validateForm = () => {
    const errors = {};
    
    if (!formData.username.trim()) {
      errors.username = 'Username is required';
    }
    
    if (!formData.email.trim()) {
      errors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      errors.email = 'Email is invalid';
    }
    
    // Only validate password if provided
    if (formData.password) {
      if (formData.password.length < 8) {
        errors.password = 'Password must be at least 8 characters';
      }
      
      if (formData.password !== formData.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match';
      }
    }
    
    return errors;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validate form
    const errors = validateForm();
    if (Object.keys(errors).length > 0) {
      setValidationErrors(errors);
      return;
    }
    
    try {
      setUpdating(true);
      setError(null);
      setSuccess(false);
      
      // Only include password in the update if it was provided
      const dataToUpdate = { ...formData };
      if (!dataToUpdate.password) {
        delete dataToUpdate.password;
        delete dataToUpdate.password_confirmation;
      }
      
      await adminService.updateAdmin(id, dataToUpdate);
      
      setSuccess(true);
      
      // Navigate back to admin list after a short delay
      setTimeout(() => {
        navigate('/admin/admins');
      }, 2000);
      
    } catch (err) {
      console.error('Error updating admin:', err);
      
      if (err.response && err.response.data && err.response.data.errors) {
        // Set validation errors from the server
        setValidationErrors(err.response.data.errors);
      } else if (err.response && err.response.data && err.response.data.message) {
        setError(err.response.data.message);
      } else {
        setError('Failed to update admin. Please try again.');
      }
    } finally {
      setUpdating(false);
    }
  };

  const handleCancel = () => {
    navigate('/admin/admins');
  };

  if (loading) {
    return (
      <>
        <Header />
        <Container className="mt--7" fluid>
          <Row>
            <Col className="order-xl-1" xl="12">
              <Card className="bg-secondary shadow">
                <CardBody>
                  <div className="text-center py-5">
                    <Spinner color="primary" />
                    <p className="mt-3">Loading admin data...</p>
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
      <Container className="mt--7" fluid>
        <Row>
          <Col className="order-xl-1" xl="12">
            <Card className="bg-secondary shadow">
              <CardHeader className="bg-white border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Edit Admin</h3>
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                {success && (
                  <Alert color="success">
                    Admin updated successfully! Redirecting...
                  </Alert>
                )}
                
                {error && (
                  <Alert color="danger">
                    {error}
                  </Alert>
                )}
                
                <Form onSubmit={handleSubmit}>
                  <h6 className="heading-small text-muted mb-4">
                    Admin Information
                  </h6>
                  <div className="pl-lg-4">
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label className="form-control-label" for="username">
                            Username*
                          </Label>
                          <Input
                            id="username"
                            name="username"
                            placeholder="Enter username"
                            type="text"
                            value={formData.username}
                            onChange={handleChange}
                            invalid={!!validationErrors.username}
                          />
                          {validationErrors.username && (
                            <div className="text-danger mt-1">
                              {validationErrors.username}
                            </div>
                          )}
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label className="form-control-label" for="email">
                            Email*
                          </Label>
                          <Input
                            id="email"
                            name="email"
                            placeholder="Enter email"
                            type="email"
                            value={formData.email}
                            onChange={handleChange}
                            invalid={!!validationErrors.email}
                          />
                          {validationErrors.email && (
                            <div className="text-danger mt-1">
                              {validationErrors.email}
                            </div>
                          )}
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label className="form-control-label" for="password">
                            Password (leave blank to keep current)
                          </Label>
                          <Input
                            id="password"
                            name="password"
                            placeholder="Enter new password"
                            type="password"
                            value={formData.password}
                            onChange={handleChange}
                            invalid={!!validationErrors.password}
                          />
                          {validationErrors.password && (
                            <div className="text-danger mt-1">
                              {validationErrors.password}
                            </div>
                          )}
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label className="form-control-label" for="password_confirmation">
                            Confirm Password
                          </Label>
                          <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirm new password"
                            type="password"
                            value={formData.password_confirmation}
                            onChange={handleChange}
                            invalid={!!validationErrors.password_confirmation}
                          />
                          {validationErrors.password_confirmation && (
                            <div className="text-danger mt-1">
                              {validationErrors.password_confirmation}
                            </div>
                          )}
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup>
                          <Label className="form-control-label" for="role">
                            Role*
                          </Label>
                          <Input
                            id="role"
                            name="role"
                            type="select"
                            value={formData.role}
                            onChange={handleChange}
                          >
                            <option value="admin">Admin</option>
                            <option value="Super Admin">Super Admin</option>
                          </Input>
                          {validationErrors.role && (
                            <div className="text-danger mt-1">
                              {validationErrors.role}
                            </div>
                          )}
                        </FormGroup>
                      </Col>
                      <Col lg="6">
                        <FormGroup>
                          <Label className="form-control-label" for="phone_number">
                            Phone Number
                          </Label>
                          <Input
                            id="phone_number"
                            name="phone_number"
                            placeholder="Enter phone number"
                            type="text"
                            value={formData.phone_number}
                            onChange={handleChange}
                            invalid={!!validationErrors.phone_number}
                          />
                          {validationErrors.phone_number && (
                            <div className="text-danger mt-1">
                              {validationErrors.phone_number}
                            </div>
                          )}
                        </FormGroup>
                      </Col>
                    </Row>
                    <Row>
                      <Col lg="6">
                        <FormGroup check className="mb-3">
                          <Label check>
                            <Input
                              type="checkbox"
                              name="is_active"
                              checked={formData.is_active}
                              onChange={handleChange}
                            />{' '}
                            Active Account
                          </Label>
                        </FormGroup>
                      </Col>
                    </Row>
                  </div>
                  <hr className="my-4" />
                  
                  <div className="pl-lg-4">
                    <Row className="mt-3">
                      <Col>
                        <Button 
                          color="primary" 
                          type="submit"
                          disabled={updating}
                        >
                          {updating ? (
                            <>
                              <Spinner size="sm" className="mr-2" />
                              Updating...
                            </>
                          ) : (
                            'Update Admin'
                          )}
                        </Button>
                        <Button 
                          color="secondary" 
                          onClick={handleCancel}
                          className="ml-2"
                          disabled={updating}
                        >
                          Cancel
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

export default AdminEdit;
/*!

=========================================================
* Argon Dashboard React - v1.2.4
=========================================================

* Product Page: https://www.creative-tim.com/product/argon-dashboard-react
* Copyright 2024 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://github.com/creativetimofficial/argon-dashboard-react/blob/master/LICENSE.md)

* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

*/
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
// reactstrap components
import {
  Button,
  Card,
  CardHeader,
  CardBody,
  FormGroup,
  Form,
  Input,
  InputGroupAddon,
  InputGroupText,
  InputGroup,
  Row,
  Col,
  Alert,
  Spinner
} from "reactstrap";

import authService from "../../services/auth.service";

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  
  const navigate = useNavigate();
  
  // Check for auth errors stored in session storage (from AuthGuard)
  useEffect(() => {
    const authError = sessionStorage.getItem('auth_error');
    if (authError) {
      setError(authError);
      // Clear the error after displaying it
      sessionStorage.removeItem('auth_error');
    }
    
    // Check if already logged in
    const token = localStorage.getItem('token');
    const user = authService.getCurrentUser();
    
    if (token && user && user.role) {
      // Already logged in, redirect to dashboard
      navigate("/admin/index");
    }
  }, [navigate]);

  const handleLogin = async (e) => {
    e.preventDefault();
    
    // Validate form
    if (!email || !password) {
      setError("Email and password are required");
      return;
    }
    
    try {
      setLoading(true);
      setError("");
      
      // Call login API
      if (process.env.NODE_ENV === 'development') {
        console.log("Attempting login with:", email);
      }
      
      const response = await authService.login(email, password);
      
      // Check if login was successful
      if (!response?.success) {
        setError(response?.message || "Login failed. Please check your credentials.");
        setLoading(false);
        return;
      }
      
      // Check if user is admin
      if (!authService.isAdmin()) {
        setError("You don't have permission to access the admin dashboard. Only admin users can access this area.");
        authService.logout();
        setLoading(false);
        return;
      }
      
      // Store remember me preference if selected
      if (rememberMe) {
        localStorage.setItem('remember_email', email);
      } else {
        localStorage.removeItem('remember_email');
      }
      
      // Login successful, redirect to dashboard
      navigate("/admin/index");
    } catch (err) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Login error:", err);
      }
      
      // Format error message
      const errorResponse = err.response?.data;
      let errorMessage = "Login failed. Please check your credentials.";
      
      if (errorResponse?.message) {
        errorMessage = errorResponse.message;
      } else if (err.message === "Network Error") {
        errorMessage = "Unable to connect to the server. Please check your internet connection.";
      }
      
      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  // Load remembered email if available
  useEffect(() => {
    const rememberedEmail = localStorage.getItem('remember_email');
    if (rememberedEmail) {
      setEmail(rememberedEmail);
      setRememberMe(true);
    }
  }, []);

  const handleForgotPassword = () => {
    navigate("/auth/forgot-password");
  };

  return (
    <>
      <Col lg="5" md="7">
        <Card className="bg-secondary shadow border-0">
          <CardHeader className="bg-transparent pb-5">
            <div className="text-muted text-center mt-2 mb-3">
              <small>PangAIa Shop Admin Login</small>
            </div>
          </CardHeader>
          <CardBody className="px-lg-5 py-lg-5">
            {error && (
              <Alert color="danger" fade={false}>
                {error}
              </Alert>
            )}
            <Form role="form" onSubmit={handleLogin}>
              <FormGroup className="mb-3">
                <InputGroup className="input-group-alternative">
                  <InputGroupAddon addonType="prepend">
                    <InputGroupText>
                      <i className="ni ni-email-83" />
                    </InputGroupText>
                  </InputGroupAddon>
                  <Input
                    placeholder="Email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    autoComplete="email"
                    required
                  />
                </InputGroup>
              </FormGroup>
              <FormGroup>
                <InputGroup className="input-group-alternative">
                  <InputGroupAddon addonType="prepend">
                    <InputGroupText>
                      <i className="ni ni-lock-circle-open" />
                    </InputGroupText>
                  </InputGroupAddon>
                  <Input
                    placeholder="Password"
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    autoComplete="current-password"
                    required
                  />
                </InputGroup>
              </FormGroup>
              <div className="custom-control custom-control-alternative custom-checkbox">
                <input
                  className="custom-control-input"
                  id="customCheckLogin"
                  type="checkbox"
                  checked={rememberMe}
                  onChange={() => setRememberMe(!rememberMe)}
                />
                <label
                  className="custom-control-label"
                  htmlFor="customCheckLogin"
                >
                  <span className="text-muted">Remember me</span>
                </label>
              </div>
              <div className="text-center">
                <Button 
                  className="my-4" 
                  color="primary" 
                  type="submit"
                  disabled={loading}
                >
                  {loading ? (
                    <>
                      <Spinner size="sm" className="mr-2" />
                      Signing in...
                    </>
                  ) : (
                    "Sign in"
                  )}
                </Button>
              </div>
            </Form>
          </CardBody>
        </Card>
        <Row className="mt-3">
          <Col xs="6">
            <Button
              className="text-light p-0"
              color="link"
              onClick={handleForgotPassword}
            >
              <small>Forgot password?</small>
            </Button>
          </Col>
        </Row>
      </Col>
    </>
  );
};

export default Login;

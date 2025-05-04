import { useState, useEffect } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import authService from '../../services/auth.service';
import { Spinner, Alert } from 'reactstrap';

const AuthGuard = ({ children }) => {
  const location = useLocation();
  const [isAuthenticated, setIsAuthenticated] = useState(null);
  const [loading, setLoading] = useState(true);
  const [authError, setAuthError] = useState(null);
  const [retryCount, setRetryCount] = useState(0);

  useEffect(() => {
    const checkAuthStatus = async () => {
      try {
        // First check if we have a token and user data in localStorage
        const token = localStorage.getItem('token');
        const user = authService.getCurrentUser();
        
        if (!token) {
          setAuthError("Please log in to access the admin dashboard");
          setIsAuthenticated(false);
          setLoading(false);
          return;
        }
        
        try {
          // Try server verification
          const isAuth = await authService.isAuthenticated();
          setIsAuthenticated(isAuth);
          
          // Additional check for admin privileges
          if (isAuth && !authService.isAdmin()) {
            setAuthError("You don't have permission to access the admin dashboard. Only admin users can access this area.");
            setIsAuthenticated(false);
          }
        } catch (serverError) {
          // Fallback: If we have a token and user data with admin role, still allow access
          // This prevents logged-in users from getting locked out due to API issues
          if (token && user && authService.isAdmin()) {
            setIsAuthenticated(true);
          } else {
            setAuthError("Authentication failed. Please log in again.");
            setIsAuthenticated(false);
          }
        }
      } catch (error) {
        setAuthError("An error occurred during authentication. Please try again.");
        setIsAuthenticated(false);
      } finally {
        setLoading(false);
      }
    };

    checkAuthStatus();
  }, [retryCount]); // Include retryCount to allow manual retries

  const handleRetry = () => {
    setLoading(true);
    setAuthError(null);
    setRetryCount(prev => prev + 1);
  };

  if (loading) {
    return (
      <div className="auth-loading-container d-flex align-items-center justify-content-center" style={{ height: '100vh' }}>
        <div className="text-center">
          <Spinner color="primary" />
          <p className="mt-3">Verifying your credentials...</p>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    // If authentication failed due to a temporary error, show retry option
    if (authError && authError.includes("error occurred")) {
      return (
        <div className="auth-error-container d-flex align-items-center justify-content-center" style={{ height: '100vh' }}>
          <div className="text-center" style={{ maxWidth: '500px' }}>
            <Alert color="danger">
              {authError}
              <div className="mt-3">
                <button onClick={handleRetry} className="btn btn-danger btn-sm">
                  Retry Authentication
                </button>
              </div>
            </Alert>
          </div>
        </div>
      );
    }
    
    // For other errors, redirect to login
    if (authError) {
      sessionStorage.setItem('auth_error', authError);
    }
    
    return <Navigate to="/auth/login" state={{ from: location }} replace />;
  }

  return children;
};

export default AuthGuard;
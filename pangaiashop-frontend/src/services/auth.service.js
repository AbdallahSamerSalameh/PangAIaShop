import api from './api';

const authService = {
  /**
   * Check if the current user has admin privileges
   * @returns {boolean} - True if user is an admin
   */
  isAdmin: () => {
    const user = authService.getCurrentUser();
    
    // If there's no user, they're not an admin
    if (!user) {
      if (process.env.NODE_ENV === 'development') {
        console.log("No user found in localStorage");
      }
      return false;
    }
    
    // Accept any role that contains 'admin' (case insensitive) or has is_super_admin flag
    const isAdminUser = user.role?.toLowerCase().includes('admin') || user.is_super_admin === true;
    
    return isAdminUser;
  },
  
  /**
   * Check if current user is a Super Admin
   * @returns {boolean} - True if user is a Super Admin
   */
  isSuperAdmin: () => {
    const user = authService.getCurrentUser();
    
    // Check specific super admin indicators
    const isSuperAdmin = user && (
      user.role === 'Super Admin' || 
      user.is_super_admin === true
    );
    
    return isSuperAdmin;
  },
  
  /**
   * Login a user with email and password
   * @param {string} email - User email
   * @param {string} password - User password
   * @returns {Promise} - Promise with user data
   */
  login: async (email, password) => {
    try {
      if (process.env.NODE_ENV === 'development') {
        console.log("Attempting admin login with:", email);
      }
      
      // Send password as password_hash to match backend database schema
      const response = await api.post('/admin/login', { 
        email, 
        password_hash: password 
      });
      
      // Save token and user data to localStorage
      if (response.data?.success && response.data?.data?.access_token) {
        const token = response.data.data.access_token;
        localStorage.setItem('token', token);
        
        // Store refresh token if provided by the API
        if (response.data.data.refresh_token) {
          localStorage.setItem('refresh_token', response.data.data.refresh_token);
        }
        
        // Save user data with normalized role values
        const user = response.data.data.user;
        
        // Ensure proper role and permission flags are set
        if (user) {
          // Normalize the role - make sure it's a string
          user.role = user.role || 'admin';
          
          // Make sure the is_super_admin flag is set if the role is Super Admin
          if (user.role === 'Super Admin' && user.is_super_admin !== true) {
            user.is_super_admin = true;
          }
          
          localStorage.setItem('user', JSON.stringify(user));
        }
      } else if (process.env.NODE_ENV === 'development') {
        console.warn("Login response missing success flag or access_token:", response.data);
      }
      
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Login error:", error.message);
      }
      throw error;
    }
  },
  
  /**
   * Register a new user
   * @param {Object} userData - User registration data
   * @returns {Promise} - Promise with registered user data
   */
  register: async (userData) => {
    try {
      // Map password to password_hash field for backend compatibility
      const formattedUserData = {
        ...userData,
        password_hash: userData.password
      };
      
      // If password_confirmation exists, also update it
      if (userData.password_confirmation) {
        formattedUserData.password_hash_confirmation = userData.password_confirmation;
        // Remove the original password fields
        delete formattedUserData.password;
        delete formattedUserData.password_confirmation;
      }
      
      const response = await api.post('/register', formattedUserData);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Logout current user
   * @returns {Promise} - Promise resolving to logout success
   */
  logout: async () => {
    try {
      // Try to revoke token on server
      await api.post('/admin/logout');
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Error logging out:', error.message);
      }
    } finally {
      // Clear local storage regardless of server response
      localStorage.removeItem('token');
      localStorage.removeItem('refresh_token');
      localStorage.removeItem('user');
      sessionStorage.removeItem('auth_error');
    }
  },
  
  /**
   * Check if a user is currently authenticated
   * @returns {Promise<boolean>} - Promise resolving to authentication status
   */
  isAuthenticated: async () => {
    try {
      const token = localStorage.getItem('token');
      
      // If no token, user is not authenticated
      if (!token) {
        return false;
      }
      
      // Try server verification
      try {
        const response = await api.get('/admin/user');
        
        // Update user data if the API returns updated information
        if (response.data?.data?.user) {
          localStorage.setItem('user', JSON.stringify(response.data.data.user));
        }
        
        return true;
      } catch (userError) {
        // Try direct verification with a simple local check
        const user = authService.getCurrentUser();
        if (user) {
          // Use same logic as isAdmin function
          if (user.role?.toLowerCase().includes('admin') || user.is_super_admin === true) {
            return true;
          }
        }
        
        return false;
      }
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Auth verification error:', error.message);
      }
      return false;
    }
  },
  
  /**
   * Get current authenticated user data
   * @returns {Object|null} - User data or null if not authenticated
   */
  getCurrentUser: () => {
    const userStr = localStorage.getItem('user');
    if (userStr) {
      try {
        const user = JSON.parse(userStr);
        
        // Ensure role is set to a string value if missing
        if (!user.role) {
          user.role = 'admin';
        }
        
        // Ensure proper super admin flag if role is Super Admin
        if (user.role === 'Super Admin' && user.is_super_admin !== true) {
          user.is_super_admin = true;
        }
        
        return user;
      } catch (e) {
        if (process.env.NODE_ENV === 'development') {
          console.error('Error parsing user data from localStorage:', e.message);
        }
        return null;
      }
    }
    return null;
  },
  
  /**
   * Update the current user's profile
   * @param {Object} userData - Updated user data
   * @returns {Promise} - Promise with updated user data
   */
  updateProfile: async (userData) => {
    try {
      const response = await api.put('/admin/profile', userData);
      
      // Update stored user data
      if (response.data?.success && response.data?.data) {
        localStorage.setItem('user', JSON.stringify(response.data.data));
      }
      
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Change the current user's password
   * @param {string} currentPassword - Current password
   * @param {string} newPassword - New password
   * @param {string} confirmPassword - Confirm new password
   * @returns {Promise} - Promise with success message
   */
  changePassword: async (currentPassword, newPassword, confirmPassword) => {
    try {
      const response = await api.post('/admin/change-password', {
        current_password: currentPassword,
        password: newPassword,
        password_confirmation: confirmPassword
      });
      
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Request a password reset for a given email
   * @param {string} email - User email
   * @returns {Promise} - Promise with success message
   */
  forgotPassword: async (email) => {
    try {
      const response = await api.post('/admin/forgot-password', { email });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Reset password with token
   * @param {string} token - Reset token from email
   * @param {string} email - User email
   * @param {string} password - New password
   * @param {string} passwordConfirmation - Confirm new password
   * @returns {Promise} - Promise with success message
   */
  resetPassword: async (token, email, password, passwordConfirmation) => {
    try {
      const response = await api.post('/admin/reset-password', {
        token,
        email,
        password,
        password_confirmation: passwordConfirmation
      });
      
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};

export default authService;
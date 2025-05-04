import axios from 'axios';

const API_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  // Add timeout to prevent infinite waiting
  timeout: 30000 // Increased to 30 seconds for long operations
});

// Add request interceptor to add auth token to all requests
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      // Make sure to format the Authorization header exactly as the backend expects
      config.headers['Authorization'] = `Bearer ${token}`;
      
      // Debug token information in development only
      if (process.env.NODE_ENV === 'development') {
        console.log(`API Request: ${config.method?.toUpperCase()} ${config.url}`);
      }
    }
    return config;
  },
  (error) => {
    if (process.env.NODE_ENV === 'development') {
      console.error('API Request Error:', error);
    }
    return Promise.reject(error);
  }
);

// Add response interceptor to handle auth errors and token refresh
apiClient.interceptors.response.use(
  (response) => {
    // Only log in development
    if (process.env.NODE_ENV === 'development') {
      console.log(`API Response: ${response.status} ${response.config.url}`);
    }
    return response;
  },
  async (error) => {
    // Only log detailed errors in development
    if (process.env.NODE_ENV === 'development') {
      console.error('API Response Error:', {
        url: error.config?.url,
        method: error.config?.method?.toUpperCase(),
        status: error.response?.status,
        message: error.response?.data?.message || error.message
      });
    }
    
    const originalRequest = error.config;
    
    // If error is 401 Unauthorized and we have a refresh token and not already retrying
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      // Check if this is a token refresh endpoint, to avoid infinite loop
      if (originalRequest.url?.includes('/refresh-token')) {
        // If refresh token call fails, force logout
        localStorage.removeItem('token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user');
        
        // Store error message for login page
        sessionStorage.setItem('auth_error', 'Your session has expired. Please log in again.');
        
        // Redirect to login page
        window.location.href = '/auth/login';
        return Promise.reject(error);
      }
      
      // Check if we have a refresh token
      const refreshToken = localStorage.getItem('refresh_token');
      
      if (refreshToken) {
        try {
          // Attempt to refresh the token
          const response = await apiClient.post('/refresh-token', {
            refresh_token: refreshToken
          });
          
          if (response.data?.access_token) {
            const newToken = response.data.access_token;
            
            // Update tokens in storage
            localStorage.setItem('token', newToken);
            
            // Update the expired token in the original request
            originalRequest.headers['Authorization'] = `Bearer ${newToken}`;
            
            // Retry the original request with new token
            return apiClient(originalRequest);
          }
        } catch (refreshError) {
          // If refresh token fails, force logout
          if (process.env.NODE_ENV === 'development') {
            console.error('Token refresh failed:', refreshError);
          }
        }
      }
      
      // If we reach here, refresh token failed or wasn't available
      // When we get a 401, clear the tokens and redirect to login
      localStorage.removeItem('token');
      localStorage.removeItem('refresh_token');
      localStorage.removeItem('user');
      
      // Store error message for login page
      sessionStorage.setItem('auth_error', 'Your session has expired. Please log in again.');
      
      // Redirect to login page
      window.location.href = '/auth/login';
    } else if (error.response?.status === 403) {
      // Handle forbidden errors (different from unauthorized)
      if (process.env.NODE_ENV === 'development') {
        console.error('Access forbidden:', error.response?.data?.message);
      }
      
      // May not need to redirect, just show a permission error in the component
    } else if (error.code === 'ECONNABORTED') {
      // Handle timeout errors
      if (process.env.NODE_ENV === 'development') {
        console.error('Request timeout:', error);
      }
      
      // Create more user-friendly error message
      error.message = 'The request took too long to complete. Please try again later.';
    } else if (!error.response) {
      // Handle network errors (no response)
      if (process.env.NODE_ENV === 'development') {
        console.error('Network error:', error);
      }
      
      // Create more user-friendly error message
      error.message = 'Could not connect to the server. Please check your internet connection.';
    }
    
    return Promise.reject(error);
  }
);

// API wrapper functions
const api = {
  // General API methods
  get: (endpoint, options = {}) => {
    return apiClient.get(endpoint, options);
  },
  
  post: (endpoint, data = {}, options = {}) => {
    return apiClient.post(endpoint, data, options);
  },
  
  put: (endpoint, data = {}, options = {}) => {
    return apiClient.put(endpoint, data, options);
  },
  
  patch: (endpoint, data = {}, options = {}) => {
    return apiClient.patch(endpoint, data, options);
  },
  
  delete: (endpoint, options = {}) => {
    return apiClient.delete(endpoint, options);
  },
  
  // File upload specific method with multipart/form-data
  upload: (endpoint, formData, options = {}) => {
    const uploadOptions = {
      ...options,
      headers: {
        ...options.headers,
        'Content-Type': 'multipart/form-data'
      }
    };
    return apiClient.post(endpoint, formData, uploadOptions);
  },
  
  // Special method for downloading files as blobs
  download: (endpoint, params = {}, filename = null, options = {}) => {
    const downloadOptions = {
      ...options,
      params,
      responseType: 'blob'
    };
    
    return apiClient.get(endpoint, downloadOptions).then(response => {
      // Create a URL for the blob
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      
      // Set the filename from Content-Disposition header if available, or use provided filename
      const contentDisposition = response.headers['content-disposition'];
      let downloadFilename = filename;
      
      if (contentDisposition) {
        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
        const matches = filenameRegex.exec(contentDisposition);
        if (matches != null && matches[1]) {
          downloadFilename = matches[1].replace(/['"]/g, '');
        }
      }
      
      if (downloadFilename) {
        link.setAttribute('download', downloadFilename);
      }
      
      document.body.appendChild(link);
      link.click();
      
      // Clean up
      setTimeout(() => {
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
      }, 100);
      
      return response;
    });
  },
  
  // Helper method for health check
  healthCheck: () => {
    return apiClient.get('/health-check', { timeout: 5000 })
      .then(response => response.data)
      .catch(error => {
        if (process.env.NODE_ENV === 'development') {
          console.error('API health check failed:', error);
        }
        return { status: 'error', message: error.message };
      });
  }
};

export default api;
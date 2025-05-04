import api from './api';

const adminService = {
  // Get all admins with pagination
  getAdmins: async (page = 1, perPage = 10, search = '') => {
    try {
      const response = await api.get('/admins', {
        params: {
          page,
          per_page: perPage,
          search
        }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Error fetching admin list:', error);
      }
      // Return empty data structure instead of throwing
      return {
        data: [],
        total: 0,
        per_page: perPage,
        current_page: page,
        last_page: 1
      };
    }
  },

  // Get a single admin
  getAdmin: async (id) => {
    if (!id) {
      throw new Error('Admin ID is required');
    }
    
    try {
      const response = await api.get(`/admins/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error fetching admin ${id}:`, error);
      }
      throw error;
    }
  },

  // Create a new admin
  createAdmin: async (adminData) => {
    if (!adminData || !adminData.email) {
      throw new Error('Admin email is required');
    }
    
    try {
      const response = await api.post('/admins', adminData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Error creating admin:', error);
      }
      throw error;
    }
  },

  // Update an existing admin
  updateAdmin: async (id, adminData) => {
    if (!id || !adminData) {
      throw new Error('Admin ID and data are required');
    }
    
    try {
      const response = await api.put(`/admins/${id}`, adminData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating admin ${id}:`, error);
      }
      throw error;
    }
  },

  // Delete an admin
  deleteAdmin: async (id) => {
    if (!id) {
      throw new Error('Admin ID is required');
    }
    
    try {
      const response = await api.delete(`/admins/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error deleting admin ${id}:`, error);
      }
      throw error;
    }
  },

  // Get audit logs with pagination and filtering
  getAuditLogs: async (page = 1, perPage = 10, filters = {}) => {
    try {
      const params = {
        page,
        per_page: perPage,
        ...filters
      };
      
      const response = await api.get('/audit-logs', { params });
      
      // Process the response data to handle encoding issues
      if (response.data && response.data.data && Array.isArray(response.data.data)) {
        response.data.data = response.data.data.map(log => {
          // Decode any HTML entities in text fields that might contain special characters
          return {
            ...log,
            action: log.action ? decodeURIComponent(encodeURIComponent(log.action)) : log.action,
            details: log.details ? decodeURIComponent(encodeURIComponent(log.details)) : log.details,
            description: log.description ? decodeURIComponent(encodeURIComponent(log.description)) : log.description
          };
        });
      }
      
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Error fetching audit logs:', error);
      }
      // Return empty data structure
      return {
        data: [],
        total: 0,
        per_page: perPage,
        current_page: page,
        last_page: 1
      };
    }
  },
  
  // Update admin password
  updatePassword: async (id, passwordData) => {
    if (!id || !passwordData || !passwordData.password || !passwordData.password_confirmation) {
      throw new Error('Admin ID and password data are required');
    }
    
    try {
      const response = await api.put(`/admins/${id}/password`, passwordData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating password for admin ${id}:`, error);
      }
      throw error;
    }
  },
  
  // Update admin permissions
  updatePermissions: async (id, permissions) => {
    if (!id || !permissions) {
      throw new Error('Admin ID and permissions data are required');
    }
    
    try {
      const response = await api.put(`/admins/${id}/permissions`, { permissions });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating permissions for admin ${id}:`, error);
      }
      throw error;
    }
  },
  
  // Get admin dashboard summary
  getAdminStats: async () => {
    try {
      const response = await api.get('/admins/stats');
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Error fetching admin stats:', error);
      }
      // Return default empty data
      return {
        total_admins: 0,
        active_admins: 0,
        admin_actions: [],
        recent_activity: []
      };
    }
  }
};

// Export individual functions for direct import
export const {
  getAdmins,
  getAdmin,
  createAdmin,
  updateAdmin,
  deleteAdmin,
  getAuditLogs,
  updatePassword,
  updatePermissions,
  getAdminStats
} = adminService;

export default adminService;

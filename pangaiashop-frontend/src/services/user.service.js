import api from "./api";

const userService = {
  // Get list of users with pagination and filters
  getUserList: async (page = 1, perPage = 10, filters = {}) => {
    try {
      const params = {
        page,
        per_page: perPage,
        ...filters
      };
      
      const response = await api.get('/users', { params });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching users:", error);
      }
      // Return empty data for dashboard components
      return { data: [], total: 0 };
    }
  },

  // Get user by ID
  getUser: async (id) => {
    if (!id) {
      throw new Error('User ID is required');
    }
    
    try {
      const response = await api.get(`/users/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error fetching user ${id}:`, error);
      }
      throw error;
    }
  },

  // Create new user
  createUser: async (userData) => {
    if (!userData || !userData.email) {
      throw new Error('Email is required for user creation');
    }
    
    try {
      const response = await api.post('/users', userData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error creating user:", error);
      }
      throw error;
    }
  },

  // Update existing user
  updateUser: async (id, userData) => {
    if (!id || !userData) {
      throw new Error('User ID and data are required');
    }
    
    try {
      const response = await api.put(`/users/${id}`, userData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating user ${id}:`, error);
      }
      throw error;
    }
  },

  // Delete user
  deleteUser: async (id) => {
    if (!id) {
      throw new Error('User ID is required');
    }
    
    try {
      const response = await api.delete(`/users/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error deleting user ${id}:`, error);
      }
      throw error;
    }
  },
  
  // Get user statistics for dashboard
  getUserStats: async () => {
    try {
      const response = await api.get('/users/stats');
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching user statistics:", error);
      }
      // Return default empty stats
      return {
        total: 0,
        active: 0,
        newThisMonth: 0,
        newThisWeek: 0
      };
    }
  },
  
  // Get recent users for dashboard
  getRecentUsers: async (limit = 5) => {
    try {
      const response = await api.get('/users/recent', {
        params: { limit }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching recent users:", error);
      }
      // Return empty array for dashboard components
      return { data: [] };
    }
  },
  
  // Update user status (activate/deactivate)
  updateUserStatus: async (id, isActive) => {
    if (!id) {
      throw new Error('User ID is required');
    }
    
    try {
      const response = await api.patch(`/users/${id}/status`, { is_active: isActive });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating status for user ${id}:`, error);
      }
      throw error;
    }
  }
};

// Export individual functions for direct import
export const {
  getUserList,
  getUser,
  createUser,
  updateUser,
  deleteUser,
  getUserStats,
  getRecentUsers,
  updateUserStatus
} = userService;

export default userService;
import api from "./api";

const categoryService = {
  // Get list of categories
  getCategoryList: async (page = 1, perPage = 50) => {
    try {
      const response = await api.get('/categories', {
        params: { page, per_page: perPage }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching categories:", error);
      }
      // Return empty array instead of throwing
      return { data: [], total: 0 };
    }
  },

  // Get category by ID
  getCategory: async (id) => {
    if (!id) {
      throw new Error('Category ID is required');
    }
    
    try {
      const response = await api.get(`/categories/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error fetching category ${id}:`, error);
      }
      throw error;
    }
  },

  // Create new category
  createCategory: async (categoryData) => {
    if (!categoryData || !categoryData.name) {
      throw new Error('Category name is required');
    }
    
    try {
      const response = await api.post('/categories', categoryData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error creating category:", error);
      }
      throw error;
    }
  },

  // Update existing category
  updateCategory: async (id, categoryData) => {
    if (!id || !categoryData) {
      throw new Error('Category ID and data are required');
    }
    
    try {
      const response = await api.put(`/categories/${id}`, categoryData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating category ${id}:`, error);
      }
      throw error;
    }
  },

  // Delete category
  deleteCategory: async (id) => {
    if (!id) {
      throw new Error('Category ID is required');
    }
    
    try {
      const response = await api.delete(`/categories/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error deleting category ${id}:`, error);
      }
      throw error;
    }
  },

  // Function to get all categories for dropdown lists (no pagination)
  getAllCategories: async () => {
    try {
      const response = await api.get('/categories/all');
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching all categories:", error);
      }
      // Return empty array instead of throwing
      return { data: [] };
    }
  },
  
  // Get category statistics
  getCategoryStats: async () => {
    try {
      const response = await api.get('/categories/stats');
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching category statistics:", error);
      }
      // Return default empty stats
      return { 
        total: 0,
        active: 0,
        inactive: 0,
        mostPopular: null
      };
    }
  }
};

// Export individual functions for direct import
export const { 
  getCategoryList, 
  getCategory, 
  createCategory, 
  updateCategory, 
  deleteCategory,
  getAllCategories,
  getCategoryStats
} = categoryService;

export default categoryService;
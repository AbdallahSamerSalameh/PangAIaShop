import api from "./api";

const productService = {
  // Get list of products with pagination
  getProducts: async (page = 1, perPage = 10, filters = {}) => {
    try {
      const params = {
        page,
        per_page: perPage,
        ...filters
      };
      
      const response = await api.get('/products', { params });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching products:", error);
      }
      // Return empty data for product grid
      return { data: [], total: 0 };
    }
  },

  // Get product by ID
  getProduct: async (id) => {
    if (!id) {
      throw new Error('Product ID is required');
    }
    
    try {
      const response = await api.get(`/products/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error fetching product ${id}:`, error);
      }
      throw error;
    }
  },

  // Create new product
  createProduct: async (productData) => {
    if (!productData || !productData.name) {
      throw new Error('Product name is required');
    }
    
    try {
      // Use FormData if there are file uploads
      let requestData = productData;
      if (productData.image && productData.image instanceof File) {
        requestData = new FormData();
        Object.keys(productData).forEach(key => {
          requestData.append(key, productData[key]);
        });
      }
      
      const response = await api.post('/products', requestData, {
        headers: requestData instanceof FormData ? 
          { 'Content-Type': 'multipart/form-data' } : {}
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error creating product:", error);
      }
      throw error;
    }
  },

  // Update existing product
  updateProduct: async (id, productData) => {
    if (!id || !productData) {
      throw new Error('Product ID and data are required');
    }
    
    try {
      // Use FormData if there are file uploads
      let requestData = productData;
      if (productData.image && productData.image instanceof File) {
        requestData = new FormData();
        Object.keys(productData).forEach(key => {
          requestData.append(key, productData[key]);
        });
        requestData.append('_method', 'PUT'); // Laravel form method spoofing
      }
      
      const response = await api.put(`/products/${id}`, requestData, {
        headers: requestData instanceof FormData ? 
          { 'Content-Type': 'multipart/form-data' } : {}
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating product ${id}:`, error);
      }
      throw error;
    }
  },

  // Delete product
  deleteProduct: async (id) => {
    if (!id) {
      throw new Error('Product ID is required');
    }
    
    try {
      const response = await api.delete(`/products/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error deleting product ${id}:`, error);
      }
      throw error;
    }
  },
  
  // Get product statistics for dashboard
  getProductStats: async () => {
    try {
      const response = await api.get('/products/stats');
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching product statistics:", error);
      }
      // Return default empty stats
      return {
        total: 0,
        inStock: 0,
        lowStock: 0,
        outOfStock: 0,
        mostViewed: null,
        bestSelling: null
      };
    }
  },
  
  // Get product reviews
  getProductReviews: async (productId, page = 1, perPage = 10) => {
    if (!productId) {
      throw new Error('Product ID is required');
    }
    
    try {
      const response = await api.get(`/products/${productId}/reviews`, {
        params: { page, per_page: perPage }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error fetching reviews for product ${productId}:`, error);
      }
      // Return empty array instead of throwing
      return { data: [], total: 0 };
    }
  },
  
  // Update product inventory
  updateInventory: async (productId, quantity) => {
    if (!productId) {
      throw new Error('Product ID is required');
    }
    
    try {
      const response = await api.patch(`/products/${productId}/inventory`, {
        quantity
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating inventory for product ${productId}:`, error);
      }
      throw error;
    }
  },
  
  // Update product status (active/inactive)
  updateProductStatus: async (productId, isActive) => {
    if (!productId) {
      throw new Error('Product ID is required');
    }
    
    try {
      const response = await api.patch(`/products/${productId}/status`, {
        is_active: isActive
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating status for product ${productId}:`, error);
      }
      throw error;
    }
  }
};

// Export individual functions for direct import
export const {
  getProducts,
  getProduct,
  createProduct,
  updateProduct,
  deleteProduct,
  getProductStats,
  getProductReviews,
  updateInventory,
  updateProductStatus
} = productService;

export default productService;
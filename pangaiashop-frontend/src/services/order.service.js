import api from './api';

const orderService = {
  /**
   * Get all orders with pagination and optional filters
   * @param {number} page - Page number
   * @param {number} perPage - Results per page
   * @param {string} status - Filter by status (optional)
   * @param {string} query - Search query (optional)
   * @returns {Promise} - Promise with paginated orders data
   */
  getAllOrders: async (page = 1, perPage = 10, status = '', query = '') => {
    try {
      const params = {
        page,
        per_page: perPage
      };
      
      if (status) {
        params.status = status;
      }
      
      if (query) {
        params.search = query;
      }
      
      const response = await api.get('/orders', { params });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching orders:", error);
      }
      throw error;
    }
  },
  
  /**
   * Get a specific order by ID
   * @param {number|string} id - Order ID
   * @returns {Promise} - Promise with order data
   */
  getOrderById: async (id) => {
    if (!id) {
      throw new Error('Order ID is required');
    }
    
    try {
      const response = await api.get(`/orders/${id}`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error fetching order ${id}:`, error);
      }
      throw error;
    }
  },
  
  /**
   * Update an order's status
   * @param {number|string} id - Order ID
   * @param {string} status - New status
   * @returns {Promise} - Promise with updated order data
   */
  updateOrderStatus: async (id, status) => {
    if (!id || !status) {
      throw new Error('Order ID and status are required');
    }
    
    try {
      const response = await api.patch(`/orders/${id}/status`, { status });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error updating order ${id} status:`, error);
      }
      throw error;
    }
  },
  
  /**
   * Add shipment tracking information to an order
   * @param {number|string} id - Order ID
   * @param {object} shipmentData - Shipment tracking data
   * @returns {Promise} - Promise with updated order data
   */
  addShipmentTracking: async (id, shipmentData) => {
    if (!id || !shipmentData) {
      throw new Error('Order ID and shipment data are required');
    }
    
    try {
      const response = await api.post(`/orders/${id}/tracking`, shipmentData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error adding tracking to order ${id}:`, error);
      }
      throw error;
    }
  },
  
  /**
   * Get order statistics for dashboard
   * @returns {Promise} - Promise with order statistics
   */
  getOrderStats: async () => {
    try {
      const response = await api.get('/orders/stats');
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching order statistics:", error);
      }
      // Return default stats instead of throwing
      return {
        pending: 0,
        processing: 0,
        shipped: 0,
        delivered: 0,
        cancelled: 0,
        total: 0
      };
    }
  },
  
  /**
   * Get recent orders for dashboard
   * @param {number} limit - Number of orders to retrieve
   * @returns {Promise} - Promise with recent orders
   */
  getRecentOrders: async (limit = 5) => {
    try {
      const response = await api.get('/orders/recent', { 
        params: { limit } 
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error fetching recent orders:", error);
      }
      // Return empty array instead of throwing
      return { data: [] };
    }
  },
  
  /**
   * Export orders to CSV
   * @param {Object} filters - Export filters
   * @returns {Promise} - Promise with CSV blob
   */
  exportOrders: async (filters = {}) => {
    try {
      return await api.download('/orders/export', filters, 'orders-export.csv');
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error("Error exporting orders:", error);
      }
      throw error;
    }
  },
  
  /**
   * Process refund for an order
   * @param {number|string} id - Order ID
   * @param {Object} refundData - Refund data including amount and reason
   * @returns {Promise} - Promise with refund result
   */
  processRefund: async (id, refundData) => {
    if (!id || !refundData || !refundData.amount) {
      throw new Error('Order ID and refund data with amount are required');
    }
    
    try {
      const response = await api.post(`/orders/${id}/refund`, refundData);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error processing refund for order ${id}:`, error);
      }
      throw error;
    }
  },
  
  /**
   * Add a note to an order
   * @param {number|string} id - Order ID
   * @param {string} note - Note content
   * @returns {Promise} - Promise with updated order data
   */
  addOrderNote: async (id, note) => {
    if (!id || !note) {
      throw new Error('Order ID and note content are required');
    }
    
    try {
      const response = await api.post(`/orders/${id}/notes`, { note });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error adding note to order ${id}:`, error);
      }
      throw error;
    }
  },
  
  /**
   * Get order history/timeline
   * @param {number|string} id - Order ID
   * @returns {Promise} - Promise with order history data
   */
  getOrderHistory: async (id) => {
    if (!id) {
      throw new Error('Order ID is required');
    }
    
    try {
      const response = await api.get(`/orders/${id}/history`);
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error fetching history for order ${id}:`, error);
      }
      // Return empty array instead of throwing
      return { data: [] };
    }
  },
  
  /**
   * Cancel an order
   * @param {number|string} id - Order ID
   * @param {string} reason - Cancellation reason
   * @returns {Promise} - Promise with updated order data
   */
  cancelOrder: async (id, reason = '') => {
    if (!id) {
      throw new Error('Order ID is required');
    }
    
    try {
      const response = await api.post(`/orders/${id}/cancel`, { reason });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error(`Error cancelling order ${id}:`, error);
      }
      throw error;
    }
  }
};

export default orderService;
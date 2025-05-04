import api from './api';

const dashboardService = {
  // Get dashboard summary data
  getDashboardData: async () => {
    try {
      const response = await api.get('/admin/dashboard');
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Dashboard data fetch error:', error);
      }
      // Return default data instead of throwing error
      return { 
        success: false, 
        stats: {
          totalSales: 0,
          totalOrders: 0,
          totalProducts: 0,
          totalCustomers: 0,
          totalCategories: 0,
          pendingOrders: 0,
          processingOrders: 0,
          shippedOrders: 0,
          deliveredOrders: 0,
          cancelledOrders: 0,
          returnedOrders: 0,
        } 
      };
    }
  },

  // Get sales report data with filters
  getSalesReport: async (startDate, endDate, groupBy = 'day') => {
    try {
      // Validate dates before sending to API
      if (!startDate || !endDate) {
        throw new Error('Start date and end date are required');
      }
      
      const response = await api.get('/admin/sales-report', {
        params: { startDate, endDate, groupBy }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Sales report fetch error:', error);
      }
      // Return default empty data
      return { 
        labels: [], 
        data: [] 
      };
    }
  },

  // Get recent orders for dashboard
  getRecentOrders: async (limit = 5) => {
    try {
      const response = await api.get('/admin/orders', {
        params: { limit, page: 1, sort: 'created_at,desc' }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Recent orders fetch error:', error);
      }
      return { data: [] };
    }
  },

  // Get low stock products
  getLowStockProducts: async (limit = 5) => {
    try {
      const response = await api.get('/admin/low-stock-products', {
        params: { limit }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Low stock products fetch error:', error);
      }
      return { data: [] };
    }
  },
  
  // Get recent customer registrations
  getRecentCustomers: async (limit = 5) => {
    try {
      const response = await api.get('/admin/users', {
        params: { limit, page: 1, sort: 'created_at,desc' }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Recent customers fetch error:', error);
      }
      return { data: [] };
    }
  },
  
  // Get revenue by category for pie chart
  getRevenueByCategory: async (period = 'month') => {
    try {
      const response = await api.get('/admin/revenue-by-category', {
        params: { period }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Revenue by category fetch error:', error);
      }
      return { 
        labels: ['No Data'], 
        data: [100] 
      };
    }
  },
  
  // Get sales comparison (current vs previous period)
  getSalesComparison: async (period = 'month') => {
    try {
      const response = await api.get('/admin/sales-comparison', {
        params: { period }
      });
      return response.data;
    } catch (error) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Sales comparison fetch error:', error);
      }
      return { 
        currentPeriod: { total: 0, data: [] },
        previousPeriod: { total: 0, data: [] },
        percentChange: 0
      };
    }
  }
};

export default dashboardService;
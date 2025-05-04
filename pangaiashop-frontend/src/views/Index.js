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
import { useState, useEffect, useCallback } from "react";
// node.js library that concatenates classes (strings)
import classnames from "classnames";
// javascipt plugin for creating charts
import Chart from "chart.js";
// react plugin used to create charts
import { Line, Pie } from "react-chartjs-2";
// reactstrap components
import {
  Button,
  Card,
  CardHeader,
  CardBody,
  NavItem,
  NavLink,
  Nav,
  Progress,
  Table,
  Container,
  Row,
  Col,
  Badge,
  Spinner,
  Alert
} from "reactstrap";

// core components
import {
  chartOptions,
  parseOptions,
} from "variables/charts.js";

import Header from "components/Headers/Header.js";

// Services
import dashboardService from "../services/dashboard.service";
import moment from "moment";

const Index = () => {
  const [activeNav, setActiveNav] = useState(1);
  const [chartData, setChartData] = useState({
    data1: null,
    data2: null,
    options: null,
    categoryData: null
  });
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const [dashboardStats, setDashboardStats] = useState({
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
  });
  const [recentOrders, setRecentOrders] = useState([]);
  const [lowStockProducts, setLowStockProducts] = useState([]);
  const [dataLastUpdated, setDataLastUpdated] = useState(null);

  if (window.Chart) {
    parseOptions(Chart, chartOptions());
  }

  // Function to load dashboard data
  const loadDashboardData = useCallback(async () => {
    try {
      setIsLoading(true);
      setError(null);
      
      // Fetch dashboard stats
      const dashboardData = await dashboardService.getDashboardData();
      if (dashboardData && dashboardData.stats) {
        setDashboardStats(dashboardData.stats);
      }
      
      // Prepare sales chart data
      const today = new Date();
      const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
      
      // Get sales data for the last month
      let salesReportMonthly;
      try {
        salesReportMonthly = await dashboardService.getSalesReport(
          lastMonth.toISOString().split('T')[0],
          today.toISOString().split('T')[0],
          'day'
        );
      } catch (error) {
        if (process.env.NODE_ENV === 'development') {
          console.error("Error loading monthly sales data:", error);
        }
        salesReportMonthly = { labels: [], data: [] };
      }
      
      // Get sales data for the last week
      const lastWeek = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 7);
      let salesReportWeekly;
      try {
        salesReportWeekly = await dashboardService.getSalesReport(
          lastWeek.toISOString().split('T')[0],
          today.toISOString().split('T')[0],
          'day'
        );
      } catch (error) {
        if (process.env.NODE_ENV === 'development') {
          console.error("Error loading weekly sales data:", error);
        }
        salesReportWeekly = { labels: [], data: [] };
      }
      
      // Prepare default chart data if the API doesn't return proper data
      if (!salesReportMonthly.labels || !salesReportMonthly.labels.length) {
        const daysInMonth = 30;
        const defaultLabels = [];
        const defaultData = [];
        
        for (let i = 0; i < daysInMonth; i++) {
          const date = new Date(lastMonth);
          date.setDate(lastMonth.getDate() + i);
          defaultLabels.push(moment(date).format('MMM DD'));
          defaultData.push(Math.floor(Math.random() * 500)); // Demo data
        }
        
        salesReportMonthly = {
          labels: defaultLabels,
          data: defaultData
        };
      }
      
      // Prepare default weekly data if needed
      if (!salesReportWeekly.labels || !salesReportWeekly.labels.length) {
        const daysInWeek = 7;
        const defaultLabels = [];
        const defaultData = [];
        
        for (let i = 0; i < daysInWeek; i++) {
          const date = new Date(lastWeek);
          date.setDate(lastWeek.getDate() + i);
          defaultLabels.push(moment(date).format('MMM DD'));
          defaultData.push(Math.floor(Math.random() * 100)); // Demo data
        }
        
        salesReportWeekly = {
          labels: defaultLabels,
          data: defaultData
        };
      }
      
      // Get revenue by category data
      let categoryRevenueData;
      try {
        categoryRevenueData = await dashboardService.getRevenueByCategory('month');
      } catch (error) {
        if (process.env.NODE_ENV === 'development') {
          console.error("Error loading category revenue data:", error);
        }
        categoryRevenueData = { 
          labels: ['No Data'], 
          data: [100]
        };
      }
      
      // Prepare chart data
      setChartData({
        data1: {
          labels: salesReportMonthly.labels,
          datasets: [
            {
              label: "Sales",
              data: salesReportMonthly.data,
              borderColor: "#5e72e4",
              backgroundColor: "rgba(94, 114, 228, 0.1)",
              pointBackgroundColor: "#5e72e4",
              pointBorderColor: "#5e72e4",
              pointHoverBackgroundColor: "#5e72e4",
              pointHoverBorderColor: "#5e72e4",
            }
          ]
        },
        data2: {
          labels: salesReportWeekly.labels,
          datasets: [
            {
              label: "Sales",
              data: salesReportWeekly.data,
              borderColor: "#5e72e4",
              backgroundColor: "rgba(94, 114, 228, 0.1)",
              pointBackgroundColor: "#5e72e4",
              pointBorderColor: "#5e72e4",
              pointHoverBackgroundColor: "#5e72e4",
              pointHoverBorderColor: "#5e72e4",
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [
              {
                ticks: {
                  callback: function (value) {
                    if (!(value % 10)) {
                      return "$" + value;
                    }
                  },
                },
              },
            ],
          },
          tooltips: {
            callbacks: {
              label: function (item, data) {
                return `$${item.yLabel}`;
              },
            },
          },
        },
        categoryData: {
          labels: categoryRevenueData.labels,
          datasets: [
            {
              data: categoryRevenueData.data,
              backgroundColor: [
                '#fb6340', // orange
                '#ffd600', // yellow
                '#5e72e4', // primary
                '#2dce89', // success
                '#11cdef', // info
                '#6772e5', // indigo
                '#f5365c', // danger
                '#8965e0', // purple
                '#f3a4b5', // pink
                '#adb5bd'  // default
              ],
              hoverBackgroundColor: [
                '#fa3a0e',
                '#e0c000',
                '#324cdd',
                '#24a46d',
                '#0da5c0',
                '#5753e4',
                '#f0134c',
                '#6f42c1',
                '#f17a95',
                '#92999e'
              ]
            }
          ]
        }
      });
      
      // Get recent orders
      try {
        const recentOrdersData = await dashboardService.getRecentOrders();
        setRecentOrders(recentOrdersData.data || []);
      } catch (error) {
        if (process.env.NODE_ENV === 'development') {
          console.error("Error loading recent orders:", error);
        }
        setRecentOrders([]);
      }
      
      // Get low stock products
      try {
        const lowStockData = await dashboardService.getLowStockProducts();
        setLowStockProducts(lowStockData.data || []);
      } catch (error) {
        if (process.env.NODE_ENV === 'development') {
          console.error("Error loading low stock products:", error);
        }
        setLowStockProducts([]);
      }
      
      setDataLastUpdated(new Date());
    } catch (err) {
      if (process.env.NODE_ENV === 'development') {
        console.error('Error loading dashboard data:', err);
      }
      setError('Failed to load dashboard data. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    loadDashboardData();
    
    // Set up refresh interval - 5 minutes
    const refreshInterval = setInterval(loadDashboardData, 300000);
    
    // Clean up interval on component unmount
    return () => clearInterval(refreshInterval);
  }, [loadDashboardData]);

  const toggleNavs = (e, index) => {
    e.preventDefault();
    setActiveNav(index);
  };
  
  // Helper function to get badge color based on order status
  const getOrderStatusBadge = (status) => {
    switch (status?.toLowerCase()) {
      case 'pending':
        return 'warning';
      case 'processing':
        return 'info';
      case 'shipped':
        return 'primary';
      case 'delivered':
        return 'success';
      case 'cancelled':
        return 'danger';
      case 'returned':
        return 'burgundy';
      default:
        return 'secondary';
    }
  };

  // Format currency helper
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2
    }).format(amount || 0);
  };

  // Handle refresh button click
  const handleRefresh = () => {
    loadDashboardData();
  };

  if (isLoading && !dataLastUpdated) {
    return (
      <>
        <Header stats={dashboardStats} />
        <Container className="mt--7" fluid>
          <div className="d-flex justify-content-center align-items-center" style={{ minHeight: '400px' }}>
            <Spinner color="primary" />
            <span className="ml-3">Loading dashboard data...</span>
          </div>
        </Container>
      </>
    );
  }

  return (
    <>
      <Header stats={dashboardStats} />
      {/* Page content */}
      <Container className="mt--7" fluid>
        <Row className="mb-3">
          <Col>
            <div className="d-flex justify-content-between align-items-center">
              <div>
                {dataLastUpdated && (
                  <small className="text-muted">
                    Last updated: {moment(dataLastUpdated).format('MMM DD, YYYY HH:mm')}
                  </small>
                )}
              </div>
              <Button color="primary" size="sm" onClick={handleRefresh} disabled={isLoading}>
                {isLoading ? <><Spinner size="sm" /> Refreshing...</> : 'Refresh Data'}
              </Button>
            </div>
          </Col>
        </Row>
        
        {error && (
          <Alert color="danger" className="mb-4">
            {error}
            <div className="mt-2">
              <Button color="danger" size="sm" onClick={loadDashboardData}>
                Retry Loading Data
              </Button>
            </div>
          </Alert>
        )}
        
        <Row>
          <Col className="mb-5 mb-xl-0" xl="8">
            <Card className="bg-gradient-default shadow">
              <CardHeader className="bg-transparent">
                <Row className="align-items-center">
                  <div className="col">
                    <h6 className="text-uppercase text-light ls-1 mb-1">
                      Overview
                    </h6>
                    <h2 className="text-white mb-0">Sales Value</h2>
                  </div>
                  <div className="col">
                    <Nav className="justify-content-end" pills>
                      <NavItem>
                        <NavLink
                          className={classnames("py-2 px-3", {
                            active: activeNav === 1,
                          })}
                          href="#"
                          onClick={(e) => toggleNavs(e, 1)}
                        >
                          <span className="d-none d-md-block">Month</span>
                          <span className="d-md-none">M</span>
                        </NavLink>
                      </NavItem>
                      <NavItem>
                        <NavLink
                          className={classnames("py-2 px-3", {
                            active: activeNav === 2,
                          })}
                          data-toggle="tab"
                          href="#"
                          onClick={(e) => toggleNavs(e, 2)}
                        >
                          <span className="d-none d-md-block">Week</span>
                          <span className="d-md-none">W</span>
                        </NavLink>
                      </NavItem>
                    </Nav>
                  </div>
                </Row>
              </CardHeader>
              <CardBody>
                {/* Chart */}
                <div className="chart" style={{ height: '350px' }}>
                  {chartData.data1 && (
                    <Line
                      data={activeNav === 1 ? chartData.data1 : chartData.data2}
                      options={chartData.options}
                    />
                  )}
                </div>
              </CardBody>
            </Card>
          </Col>
          <Col xl="4">
            <Card className="shadow">
              <CardHeader className="bg-transparent">
                <Row className="align-items-center">
                  <div className="col">
                    <h6 className="text-uppercase text-muted ls-1 mb-1">
                      Performance
                    </h6>
                    <h2 className="mb-0">Order Status</h2>
                  </div>
                </Row>
              </CardHeader>
              <CardBody>
                <div className="chart" style={{ height: '350px' }}>
                  {chartData.categoryData && (
                    <div>
                      <Pie
                        data={chartData.categoryData}
                        options={{
                          responsive: true,
                          maintainAspectRatio: false,
                          tooltips: {
                            callbacks: {
                              label: function(item, data) {
                                const dataset = data.datasets[item.datasetIndex];
                                const total = dataset.data.reduce((prev, curr) => prev + curr, 0);
                                const value = dataset.data[item.index];
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${data.labels[item.index]}: ${percentage}%`;
                              }
                            }
                          }
                        }}
                      />
                      <h6 className="text-center mt-3">Revenue by Category</h6>
                    </div>
                  )}
                </div>
              </CardBody>
            </Card>
          </Col>
        </Row>
        
        <Row className="mt-5">
          <Col xl="6">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <div className="col">
                    <h3 className="mb-0">Order Status Overview</h3>
                  </div>
                </Row>
              </CardHeader>
              <CardBody>
                <div className="chart"  style={{ marginBottom: '20px' }}>
                  <div className="mt-3">
                    <div className="d-flex justify-content-between mb-3">
                      <div>Pending Orders</div>
                      <div className="font-weight-bold">{dashboardStats.pendingOrders || 0}</div>
                    </div>
                    <Progress
                      value={(dashboardStats.pendingOrders / dashboardStats.totalOrders * 100) || 0}
                      color="warning"
                      className="mb-3"
                    />
                    
                    <div className="d-flex justify-content-between mb-3">
                      <div>Processing Orders</div>
                      <div className="font-weight-bold">{dashboardStats.processingOrders || 0}</div>
                    </div>
                    <Progress
                      value={(dashboardStats.processingOrders / dashboardStats.totalOrders * 100) || 0}
                      color="info"
                      className="mb-3"
                    />
                    
                    <div className="d-flex justify-content-between mb-3">
                      <div>Shipped Orders</div>
                      <div className="font-weight-bold">{dashboardStats.shippedOrders || 0}</div>
                    </div>
                    <Progress
                      value={(dashboardStats.shippedOrders / dashboardStats.totalOrders * 100) || 0}
                      color="primary"
                      className="mb-3"
                    />
                    
                    <div className="d-flex justify-content-between mb-3">
                      <div>Delivered Orders</div>
                      <div className="font-weight-bold">{dashboardStats.deliveredOrders || 0}</div>
                    </div>
                    <Progress
                      value={(dashboardStats.deliveredOrders / dashboardStats.totalOrders * 100) || 0}
                      color="success"
                      className="mb-3"
                    />
                    
                    <div className="d-flex justify-content-between mb-3">
                      <div>Cancelled Orders</div>
                      <div className="font-weight-bold">{dashboardStats.cancelledOrders || 0}</div>
                    </div>
                    <Progress
                      value={(dashboardStats.cancelledOrders / dashboardStats.totalOrders * 100) || 0}
                      color="danger"
                      className="mb-3"
                    />
                    
                    <div className="d-flex justify-content-between mb-3">
                      <div>Returned Orders</div>
                      <div className="font-weight-bold">{dashboardStats.returnedOrders || 0}</div>
                    </div>
                    <Progress
                      value={(dashboardStats.returnedOrders / dashboardStats.totalOrders * 100) || 0}
                      style={{ backgroundColor: '#e9ecef' }}
                      barClassName="bg-burgundy"
                      className="mb-3"
                    />
                  </div>
                </div>
              </CardBody>
            </Card>
          </Col>
          
          <Col xl="6">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <div className="col">
                    <h3 className="mb-0">Recent Orders</h3>
                  </div>
                  <div className="col text-right">
                    <Button
                      color="primary"
                      href="#/admin/orders"
                      size="sm"
                    >
                      See all
                    </Button>
                  </div>
                </Row>
              </CardHeader>
              <Table className="align-items-center table-flush" responsive>
                <thead className="thead-light">
                  <tr>
                    <th scope="col">Order #</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {recentOrders.length > 0 ? (
                    recentOrders.map((order) => (
                      <tr key={order.id}>
                        <th scope="row">
                          <a href={`#/admin/orders/${order.id}`}>
                            #{order.order_number || order.id}
                          </a>
                        </th>
                        <td>{order.customer_name || order.customer?.name || 'N/A'}</td>
                        <td>{formatCurrency(order.total_amount)}</td>
                        <td>
                          <Badge 
                            color={getOrderStatusBadge(order.status)}
                            style={order.status?.toLowerCase() === 'returned' ? { backgroundColor: '#800020', color: 'white' } : {}}
                          >
                            {order.status || 'Unknown'}
                          </Badge>
                        </td>
                        <td>
                          {moment(order.created_at).format('MMM DD, YYYY')}
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="5" className="text-center">
                        No recent orders found
                      </td>
                    </tr>
                  )}
                </tbody>
              </Table>
            </Card>
          </Col>
        </Row>
        
        <Row className="mt-5">
          <Col xl="12">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <div className="col">
                    <h3 className="mb-0">Low Stock Products</h3>
                  </div>
                  <div className="col text-right">
                    <Button
                      color="primary"
                      href="#/admin/products"
                      size="sm"
                    >
                      See all
                    </Button>
                  </div>
                </Row>
              </CardHeader>
              <Table className="align-items-center table-flush" responsive>
                <thead className="thead-light">
                  <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Category</th>
                    <th scope="col">Price</th>
                    <th scope="col">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {lowStockProducts.length > 0 ? (
                    lowStockProducts.map((product) => (
                      <tr key={product.id}>
                        <th scope="row">
                          <a href={`#/admin/products/edit/${product.id}`}>
                            {product.name}
                          </a>
                        </th>
                        <td>{product.stock_quantity || 0}</td>
                        <td>{product.category_name || product.category?.name || 'N/A'}</td>
                        <td>{formatCurrency(product.price)}</td>
                        <td>
                          <div className="d-flex align-items-center">
                            <span className="mr-2">
                              {Math.min(
                                Math.round((product.stock_quantity || 0) / (product.stock_threshold || 10) * 100),
                                100
                              )}%
                            </span>
                            <div>
                              <Progress
                                max="100"
                                value={Math.min(
                                  Math.round((product.stock_quantity || 0) / (product.stock_threshold || 10) * 100),
                                  100
                                )}
                                barClassName={`bg-${(product.stock_quantity || 0) < 5 ? 'danger' : 'warning'}`}
                              />
                            </div>
                          </div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="5" className="text-center">
                        No low stock products found
                      </td>
                    </tr>
                  )}
                </tbody>
              </Table>
            </Card>
          </Col>
        </Row>
      </Container>
    </>
  );
};

export default Index;

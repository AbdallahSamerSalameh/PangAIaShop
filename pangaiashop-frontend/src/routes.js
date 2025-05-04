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
import Index from "views/Index.js";
import Profile from "views/examples/Profile.js";
import Login from "views/examples/Login.js";

// PangAIa Shop Admin Routes
import ProductList from "views/admin/ProductList.js";
import ProductCreate from "views/admin/ProductCreate.js";
import ProductEdit from "views/admin/ProductEdit.js";
import CategoryList from "views/admin/CategoryList.js";
import CategoryCreate from "views/admin/CategoryCreate.js";
import CategoryEdit from "views/admin/CategoryEdit.js";
import OrderList from "views/admin/OrderList.js";
import OrderDetail from "views/admin/OrderDetail.js";
import UserList from "views/admin/UserList.js";
import UserCreate from "views/admin/UserCreate.js";
import UserEdit from "views/admin/UserEdit.js";
import SupportTickets from "views/admin/SupportTickets.js";
import SupportTicketDetail from "views/admin/SupportTicketDetail.js";
import PromoCodeList from "views/admin/PromoCodeList.js";
import PromoCodeCreate from "views/admin/PromoCodeCreate.js";
import PromoCodeEdit from "views/admin/PromoCodeEdit.js";
import ReviewModeration from "views/admin/ReviewModeration.js";
import AuditLogs from "views/admin/AuditLogs.js";
import AdminList from "views/admin/AdminList.js";
import AdminCreate from "views/admin/AdminCreate.js";
import AdminEdit from "views/admin/AdminEdit.js";

var routes = [
  // Dashboard
  {
    path: "/index",
    name: "Dashboard",
    icon: "ni ni-tv-2 text-primary",
    component: <Index />,
    layout: "/admin",
    showInSidebar: true,
  },
  
  // Products
  {
    path: "/products",
    name: "Products",
    icon: "ni ni-box-2 text-blue",
    component: <ProductList />,
    layout: "/admin",
    showInSidebar: true,
  },
  {
    path: "/products/create",
    name: "Add Product",
    component: <ProductCreate />,
    layout: "/admin",
    showInSidebar: false,
  },
  {
    path: "/products/edit/:id",
    name: "Edit Product",
    component: <ProductEdit />,
    layout: "/admin",
    showInSidebar: false,
  },
  
  // Categories
  {
    path: "/categories",
    name: "Categories",
    icon: "ni ni-tag text-orange",
    component: <CategoryList />,
    layout: "/admin",
    showInSidebar: true,
  },
  {
    path: "/categories/create",
    name: "Add Category",
    component: <CategoryCreate />,
    layout: "/admin",
    showInSidebar: false,
  },
  {
    path: "/categories/edit/:id",
    name: "Edit Category",
    component: <CategoryEdit />,
    layout: "/admin",
    showInSidebar: false,
  },
  
  // Orders
  {
    path: "/orders",
    name: "Orders",
    icon: "ni ni-cart text-yellow",
    component: <OrderList />,
    layout: "/admin",
    showInSidebar: true,
  },
  {
    path: "/orders/:id",
    name: "Order Details",
    component: <OrderDetail />,
    layout: "/admin",
    showInSidebar: false,
  },
  
  // Users
  {
    path: "/users",
    name: "Users",
    icon: "ni ni-single-02 text-red",
    component: <UserList />,
    layout: "/admin",
    showInSidebar: true,
  },
  {
    path: "/users/create",
    name: "Add User",
    component: <UserCreate />,
    layout: "/admin",
    showInSidebar: false,
  },
  {
    path: "/users/edit/:id",
    name: "Edit User",
    component: <UserEdit />,
    layout: "/admin",
    showInSidebar: false,
  },
  
  // Admins 
  {
    path: "/admins",
    name: "Admins",
    icon: "ni ni-circle-08 text-purple",
    component: <AdminList />,
    layout: "/admin",
    showInSidebar: true,
    superAdminOnly: true, // Optional: Can use this flag to restrict certain routes to super admins
  },
  {
    path: "/admins/create",
    name: "Add Admin",
    component: <AdminCreate />,
    layout: "/admin",
    showInSidebar: false,
  },
  {
    path: "/admins/edit/:id",
    name: "Edit Admin",
    component: <AdminEdit />,
    layout: "/admin",
    showInSidebar: false,
  },
  
  // Support Tickets
  {
    path: "/support-tickets",
    name: "Support Tickets",
    icon: "ni ni-support-16 text-info",
    component: <SupportTickets />,
    layout: "/admin",
    showInSidebar: true,
  },
  {
    path: "/support-tickets/:id",
    name: "Ticket Details",
    component: <SupportTicketDetail />,
    layout: "/admin",
    showInSidebar: false,
  },
  
  // Promo Codes
  {
    path: "/promo-codes",
    name: "Promo Codes",
    icon: "ni ni-tag text-purple",
    component: <PromoCodeList />,
    layout: "/admin",
    showInSidebar: true,
  },
  {
    path: "/promo-codes/create",
    name: "Add Promo Code",
    component: <PromoCodeCreate />,
    layout: "/admin",
    showInSidebar: false,
  },
  {
    path: "/promo-codes/edit/:id",
    name: "Edit Promo Code",
    component: <PromoCodeEdit />,
    layout: "/admin",
    showInSidebar: false,
  },
  
  // Reviews
  {
    path: "/reviews",
    name: "Reviews",
    icon: "ni ni-chat-round text-green",
    component: <ReviewModeration />,
    layout: "/admin",
    showInSidebar: true,
  },
  
  // Audit Logs
  {
    path: "/audit-logs",
    name: "Audit Logs",
    icon: "ni ni-bullet-list-67 text-gray",
    component: <AuditLogs />,
    layout: "/admin",
    showInSidebar: true,
    superAdminOnly: true,
  },
  
  // User Profile
  {
    path: "/user-profile",
    name: "User Profile",
    icon: "ni ni-single-02 text-yellow",
    component: <Profile />,
    layout: "/admin",
    showInSidebar: true,
  },
  
  // Authentication Routes
  {
    path: "/login",
    name: "Login",
    icon: "ni ni-key-25 text-info",
    component: <Login />,
    layout: "/auth",
    showInSidebar: false,
  },
];

export default routes;

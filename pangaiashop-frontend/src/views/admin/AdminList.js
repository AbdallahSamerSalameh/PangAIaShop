import React, { useState, useEffect, useCallback } from 'react';
import {
  Card,
  CardHeader,
  Container,
  Row,
  Col,
  Table,
  Button,
  Badge,
  UncontrolledDropdown,
  DropdownToggle,
  DropdownMenu,
  DropdownItem,
  Input,
  FormGroup,
  Form,
  InputGroup,
  InputGroupAddon,
  InputGroupText,
  Spinner,
  Alert,
  Pagination,
  PaginationItem,
  PaginationLink
} from 'reactstrap';
import Header from 'components/Headers/Header.js';
import adminService from '../../services/admin.service';
import { useNavigate } from 'react-router-dom';
import authService from 'services/auth.service';

const AdminList = () => {
  const [admins, setAdmins] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [currentUser, setCurrentUser] = useState(null);
  const navigate = useNavigate();

  // Use useCallback to memoize the loadAdmins function
  const loadAdmins = useCallback(async (searchTerm = '') => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await adminService.getAdmins(currentPage, 10, searchTerm);
      
      // Check if response has the expected format
      if (response && response.data) {
        setAdmins(response.data);
        setTotalPages(Math.ceil(response.total / response.per_page));
      } else {
        // If the response doesn't have expected structure, show empty state
        setAdmins([]);
        setTotalPages(1);
        setError('No admin data available');
      }
    } catch (err) {
      console.error('Error loading admins:', err);
      setError('Failed to load admin data. Please try again.');
      setAdmins([]);
      setTotalPages(1);
    } finally {
      setLoading(false);
    }
  }, [currentPage]); // currentPage as dependency

  useEffect(() => {
    // Get current user
    const user = authService.getCurrentUser();
    setCurrentUser(user);
    
    // Only Super Admins should be able to access this page
    if (user && user.is_super_admin !== true && user.role !== 'Super Admin') {
      navigate('/admin/index');
    }
    
    loadAdmins(searchQuery);
  }, [loadAdmins, navigate, searchQuery]);

  const handleSearch = (e) => {
    e.preventDefault();
    setCurrentPage(1); // Reset to first page on new search
    loadAdmins(searchQuery);
  };

  const handleCreateAdmin = () => {
    navigate('/admin/admins/create');
  };

  const handleEdit = (id) => {
    navigate(`/admin/admins/edit/${id}`);
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this admin? This action cannot be undone.')) {
      try {
        await adminService.deleteAdmin(id);
        // Refresh the list after deletion
        loadAdmins(searchQuery);
      } catch (err) {
        console.error('Error deleting admin:', err);
        setError('Failed to delete admin. Please try again.');
      }
    }
  };
  
  // Check if user is Super Admin
  const isSuperAdmin = () => {
    return currentUser && (currentUser.is_super_admin === true || currentUser.role === 'Super Admin');
  };

  // Function to get active status badge
  const getStatusBadge = (isActive) => {
    return isActive ? (
      <Badge color="success">Active</Badge>
    ) : (
      <Badge color="danger">Inactive</Badge>
    );
  };

  return (
    <>
      <Header />
      <Container className="mt--7" fluid>
        <Row>
          <div className="col">
            <Card className="shadow">
              <CardHeader className="border-0">
                <Row className="align-items-center">
                  <Col xs="8">
                    <h3 className="mb-0">Admin Management</h3>
                  </Col>
                  <Col className="text-right" xs="4">
                    {isSuperAdmin() && (
                      <Button
                        color="primary"
                        onClick={handleCreateAdmin}
                        size="sm"
                      >
                        Add Admin
                      </Button>
                    )}
                  </Col>
                </Row>
              </CardHeader>
              
              <div className="px-4 py-3 border-0">
                <Form onSubmit={handleSearch}>
                  <Row>
                    <Col md="4">
                      <FormGroup className="mb-0">
                        <InputGroup className="input-group-alternative">
                          <InputGroupAddon addonType="prepend">
                            <InputGroupText>
                              <i className="fas fa-search" />
                            </InputGroupText>
                          </InputGroupAddon>
                          <Input 
                            placeholder="Search by username or email..." 
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                          />
                        </InputGroup>
                      </FormGroup>
                    </Col>
                    <Col md="2">
                      <Button color="primary" type="submit">
                        Search
                      </Button>
                    </Col>
                  </Row>
                </Form>
              </div>
              
              {error && (
                <Alert color="danger" className="mx-4">
                  {error}
                </Alert>
              )}
              
              {loading ? (
                <div className="text-center py-5">
                  <Spinner color="primary" />
                </div>
              ) : (
                <Table className="align-items-center table-flush" responsive>
                  <thead className="thead-light">
                    <tr>
                      <th scope="col">ID</th>
                      <th scope="col">Username</th>
                      <th scope="col">Email</th>
                      <th scope="col">Role</th>
                      <th scope="col">Status</th>
                      <th scope="col">Last Login</th>
                      <th scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {admins.length > 0 ? (
                      admins.map((admin) => (
                        <tr key={admin.id}>
                          <td>{admin.id}</td>
                          <td>{admin.username}</td>
                          <td>{admin.email}</td>
                          <td>
                            <Badge color={admin.role === 'Super Admin' ? 'danger' : 'info'}>
                              {admin.role}
                            </Badge>
                          </td>
                          <td>{getStatusBadge(admin.is_active)}</td>
                          <td>
                            {admin.last_login || admin.last_login_at 
                              ? new Date(admin.last_login || admin.last_login_at).toLocaleString() 
                              : 'Never'}
                          </td>
                          <td>
                            <UncontrolledDropdown>
                              <DropdownToggle
                                className="btn-icon-only text-light"
                                color=""
                                role="button"
                                size="sm"
                              >
                                <i className="fas fa-ellipsis-v" />
                              </DropdownToggle>
                              <DropdownMenu className="dropdown-menu-arrow" right>
                                <DropdownItem
                                  onClick={() => handleEdit(admin.id)}
                                >
                                  Edit
                                </DropdownItem>
                                {isSuperAdmin() && (
                                  <DropdownItem
                                    onClick={() => handleDelete(admin.id)}
                                    className="text-danger"
                                  >
                                    Delete
                                  </DropdownItem>
                                )}
                              </DropdownMenu>
                            </UncontrolledDropdown>
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="7" className="text-center">
                          No admins found
                        </td>
                      </tr>
                    )}
                  </tbody>
                </Table>
              )}
              
              {/* Pagination */}
              {totalPages > 1 && (
                <div className="card-footer py-4">
                  <nav aria-label="...">
                    <Pagination
                      className="pagination justify-content-end mb-0"
                      listClassName="justify-content-end mb-0"
                    >
                      <PaginationItem className={currentPage === 1 ? "disabled" : ""}>
                        <PaginationLink
                          onClick={() => setCurrentPage(currentPage - 1)}
                          tabIndex="-1"
                        >
                          <i className="fas fa-angle-left" />
                          <span className="sr-only">Previous</span>
                        </PaginationLink>
                      </PaginationItem>
                      
                      {/* Generate page numbers */}
                      {[...Array(totalPages).keys()].map(number => (
                        <PaginationItem
                          key={number + 1}
                          className={currentPage === number + 1 ? "active" : ""}
                        >
                          <PaginationLink onClick={() => setCurrentPage(number + 1)}>
                            {number + 1}
                          </PaginationLink>
                        </PaginationItem>
                      ))}
                      
                      <PaginationItem className={currentPage === totalPages ? "disabled" : ""}>
                        <PaginationLink onClick={() => setCurrentPage(currentPage + 1)}>
                          <i className="fas fa-angle-right" />
                          <span className="sr-only">Next</span>
                        </PaginationLink>
                      </PaginationItem>
                    </Pagination>
                  </nav>
                </div>
              )}
            </Card>
          </div>
        </Row>
      </Container>
    </>
  );
};

export default AdminList;
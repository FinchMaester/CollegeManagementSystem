<?php
defined('BASEPATH') OR exit('No direct script access allowed');




function my_autoloader($class)
{




    if (substr($class, 0, 9) == "MY_Addon_") {
   
       if (file_exists($file = APPPATH . 'core/' . $class . '.php')) {
            include $file;
        }
    }
}
spl_autoload_register('my_autoloader');




$route['default_controller'] = 'welcome/index';
$route['user/resetpassword/([a-z]+)/(:any)'] = 'site/resetpassword/$1/$2';
$route['admin/resetpassword/(:any)'] = 'site/admin_resetpassword/$1';
$route['admin/unauthorized'] = 'admin/admin/unauthorized';
$route['parent/unauthorized'] = 'parent/parents/unauthorized';
$route['student/unauthorized'] = 'user/user/unauthorized';
$route['teacher/unauthorized'] = 'teacher/teacher/unauthorized';
$route['accountant/unauthorized'] = 'accountant/accountant/unauthorized';
$route['librarian/unauthorized'] = 'librarian/librarian/unauthorized';




// $route['404_override'] = '';
$route['404_override'] = 'welcome/show_404';




$route['translate_uri_dashes'] = FALSE;
$route['cron/hemis_students/(:any)'] = 'cron/hemis_students/$1';
$route['cron/hemis_errors_prune/(:any)'] = 'cron/hemis_errors_prune/$1';
$route['cron/tokens_prune/(:any)'] = 'cron/tokens_prune/$1';
$route['cron/hemis_go_live/(:any)'] = 'cron/hemis_go_live/$1';
$route['cron/(:any)'] = 'cron/index/$1';




//======= front url rewriting==========
$route['page/(:any)'] = 'welcome/page/$1';
$route['read/(:any)'] = 'welcome/read/$1';
$route['online_admission'] = 'welcome/admission';
$route['examresult'] = 'welcome/examresult';
$route['frontend'] = 'welcome';




$route['online_course'] = 'course';
$route['programs'] = 'programs/index';
$route['programs/create'] = 'programs/create';
$route['programs/edit/(:num)'] = 'programs/edit/$1';
$route['programs/view/(:num)'] = 'programs/view/$1';
$route['programs/delete/(:num)'] = 'programs/delete/$1';




// API Routes
$route['api/Student/GetAllStudent'] = 'api/student/GetAllStudent';
$route['api/Student/GetAllStudent/(:any)'] = 'api/student/GetAllStudent/$1';

// Add new admin dashboard route
$route['admin'] = 'admin/admin/dashboard';

// HEMIS-shaped create must hit REST api/Student::create_post (not the admin web Student::create form).
$route['api/Student/Create'] = 'api/student/create';

// HEMIS §5.8.4 Get Student by ID (campus uses local students.id).
$route['api/Student/(:num)']['GET'] = 'api/student/view/$1';

$route['api/Student/(:num)']['PATCH'] = 'api/student/Update/$1';


$route['api/employee'] = 'api/employee/index';
$route['api/Employee/GetAllEmployeees'] = 'api/employee/getAllEmployees';
$route['api/Employee/(:num)']['patch'] = 'api/employee/index_patch/$1';


$route['api/Graduation']['GET'] = 'api/graduation/index';
$route['api/Graduation/(:num)']['GET'] = 'api/graduation/view/$1';
$route['api/Graduation/GenerateGraduationApplication'] = 'api/graduation/GenerateGraduationApplication';
$route['api/Graduation/GenerateGraduationApplicationForStudent'] = 'api/graduation/GenerateGraduationApplicationForStudent';
$route['api/Graduation/(:num)']['PUT'] = 'api/graduation/index/$1';



$route['api/ProgramMgmt/GetCollegePrograms'] = 'api/ProgramMgmt/GetCollegePrograms';




$route['api/Library']['GET'] = 'api/library/index';
$route['api/Library']['POST'] = 'api/library/index';
$route['api/Library/(:num)']['GET'] = 'api/library/view/$1';
$route['api/Library/(:num)']['PUT'] = 'api/library/index/$1';

$route['api/Employee']['POST'] = 'api/employee/index';


$route['api/Labs']['GET'] = 'api/labs/index';
$route['api/Labs/(:num)']['GET'] = 'api/labs/view/$1';
$route['api/Labs']['POST'] = 'api/labs/index';
$route['api/Labs/(:num)']['PUT'] = 'api/labs/view/$1';


$route['api/StudentUpgrade']['POST'] = 'api/studentupgrade/index';

$route['api/DropOut']['GET'] = 'api/dropout/index';
$route['api/DropOut']['POST'] = 'api/dropout/index';
$route['api/DropOut/(:num)']['GET'] = 'api/dropout/view/$1';
$route['api/DropOut/(:num)']['PUT'] = 'api/dropout/index/$1'; 

$route['api/Buildings']['GET'] = 'api/buildings/index';
$route['api/Buildings']['POST'] = 'api/buildings/index';
$route['api/Buildings/(:num)']['GET'] = 'api/buildings/view/$1';
$route['api/Buildings/(:num)']['PUT'] = 'api/buildings/index/$1';

$route['api/FurnitureEquipmentVehicle']['GET'] = 'api/furnitureequipmentvehicle/index';
$route['api/FurnitureEquipmentVehicle']['POST'] = 'api/furnitureequipmentvehicle/index';
$route['api/FurnitureEquipmentVehicle/(:num)']['GET'] = 'api/furnitureequipmentvehicle/view/$1';
$route['api/FurnitureEquipmentVehicle/(:num)']['PUT'] = 'api/furnitureequipmentvehicle/index/$1';

$route['api/Hostels']['GET'] = 'api/hostels/index';
$route['api/Hostels']['POST'] = 'api/hostels/index';
$route['api/Hostels/(:num)']['GET'] = 'api/hostels/view/$1';
$route['api/Hostels/(:num)']['PUT'] = 'api/hostels/index/$1';

$route['api/Lands']['GET'] = 'api/lands/index';
$route['api/Lands']['POST'] = 'api/lands/index';
$route['api/Lands/(:num)']['GET'] = 'api/lands/view/$1';
$route['api/Lands/(:num)']['PUT'] = 'api/lands/index/$1';

$route['api/Management/Department']['GET'] = 'api/management/department_list';
$route['api/Management/AddDepartment']['POST'] = 'api/management/add_department';
$route['api/Management/UpdateDepartment/(:num)']['PUT'] = 'api/management/update_department/$1';
$route['api/Managent/Section']['GET'] = 'api/management/section_list';
$route['api/Managent/AddSection']['POST'] = 'api/management/add_section';
$route['api/Management/UpdateSection/(:num)']['PUT'] = 'api/management/update_section/$1';

$route['api/Batch']['GET'] = 'api/batch/index';
$route['api/FiscalYear']['GET'] = 'api/fiscalyear/index';
$route['api/Address/GetAll']['GET'] = 'api/address/getall';
$route['api/EmployeePositions']['GET'] = 'api/employeepositions/index';
$route['api/EmployeePositions/(:num)']['GET'] = 'api/employeepositions/view/$1';

$route['admin/examresult/viewmarksheet'] = 'admin/examresult/viewmarksheet';

// Common misspelling: "attendance" vs controller name Subjectattendence
$route['admin/subjectattendance/(:any)'] = 'admin/subjectattendence/$1';
$route['admin/subjectattendance']         = 'admin/subjectattendence';


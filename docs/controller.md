Enhanced Controller
====================

The bundled MY_Controller has the following functions:

1. Manage Layout
-----------------
The now allow you to use layout. There are several way to manage layout.

 * Set a protected $_layout = 'your_layout' in your controller to use the layout for your full controller
 * You can customize your layout per action also using $this->set_layout('your_layout') api;
 * Set a protected $_layout = 'global_layout' in the MY_Controller to use this throughout your application

2. Render with/out layout:
----------------------
A function named "viewWithLayout" added to the Core loader library to render a view file along with layout. The function call is same as view.
you can call $this->load->viewWithLayout('view',$data); or  $this->load->viewWithLayout('view',$data, true);

There is a shortcut yet power full render function available "$this->render()"; The render function can detect the template type with its extension. It will use Twig render engine if the view extention .twig found.
When you use render function without twig for ajax call the template will render without layout. If you need render a template with layout for an ajax call, you have to use  $this->load->viewWithLayout('view',$data); instead.

If you want to render without a layout you can use default $this->load->view('view',$data) or shortcut $this->_render('view',$data);


3. Create Layout:
-----------------
Create layout is same as create a view file. You just need to echo the $content variable where you like to display the partial view within the layout

4. Twig Instance:
-----------------
You can access the Twig instance from your controller by $this->twig();
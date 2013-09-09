Enhanced Controller
====================

The bundled MY_Controller has the following functions:

1. Manage Layout
-----------------
The controller now allow you to use layout. There are several way to manage layout.

 * Set a protected $_layout = 'your_layout' in your controller to use the layout for your full controller
 * You can customize your layout per action also using $this->set_layout('your_layout') api;
 * Set a protected $_layout = 'global_layout' in the MY_Controller to use this throughout your application

2. Render with/out layout:
----------------------
A function named "viewWithLayout" added to the Core loader library to render a view file along with layout. The function call is same as view.
you can call <code>$this->load->viewWithLayout('view',$data);</code> or  <code>$this->load->viewWithLayout('view',$data, true);</code>
There is a shortcut yet power full render function available ["$this->render()"](./controller.md#3-the-render-function);
If you want to render without a layout you can use default $this->load->view('view',$data) or shortcut $this->_render('view',$data);

3. The render function:
-----------------------
The render function can detect the template type with its extension.  It will use Twig render engine if the view extention .twig found.
When you use render function without twig for ajax call the template will render without layout. If you need render a template with layout for an ajax call, you have to use  $this->load->viewWithLayout('view',$data); instead.
Besides default functionality the render function support some other powerful/smart/intelligent features. You can call the render function as:

 ```php
 
 //Render can auto detect the template file.
 //To render "{controller}/{method}.twig" or "{controller}/{method}.php"
 //you can write render function with any of the following way:
 $this->render();
 $this->render(true);                                   //Will return the output
 $this->render(array('data'=>'value'));
 $this->render(array('data'=>'value'), true);           //Will return the output
 $this->render(null, array('data'=>'value'));
 $this->render(null, array('data'=>'value'), true));    //Will return the output
 $this->render('', array('data'=>'value'));
 $this->render('', array('data'=>'value'), true));      //Will return the output

 //To render specific template you can call like follows. The render engine will auto detect
 //with the extension. If template has a .twig extension it will be rendered with Twig
 $this->render('template_name');
 $this->render('template_name', true);                                  //Will return the output
 $this->render('template_name', array('data'=>'value'));
 $this->render('template_name', array('data'=>'value'), true));         //Will return the output
 $this->render('template_name.twig', array('data'=>'value'));           //Will render with Twig
 $this->render('template_name.twig', array('data'=>'value'), true));    //Will return the output
 
 ```

4. Twig or Codeigniter Template?
--------------------------------
$this->render() have some auto detraction system to chose the render engine. However you can chose your preferred Template engine whenever you need/like. Use following code to render your template with codeigniter template.

```php
 $this->load->view('view',$data);           //Display The rendered template without layout
 $this->load->view('view',$data, true);     //Return the rendered template without layout
 $this->load->viewWithLayout('view',$data); //Display The rendered template with layout
 $this->load->viewWithLayout('view',$data, true); //Return the rendered template with layout

```

If you like to render using Twig you can use the following codes:

```php
  $this->twig()->display('view.twig', $data);   //Render and display "view.twig" template
  //You can also omit the ".twig" part.
  $this->twig()->display('view', $data);        //This will also Render and display "view.twig" template

  //Following two line will Render and return "view.twig" template
  $this->twig()->render('view.twig', $data);
  $this->twig()->render('view', $data);

```

5. Create Layout:
-----------------
Create layout is same as create a view file. You just need to echo the $content variable where you like to display the partial view within the layout

6. Twig Instance:
-----------------
You can access the Twig instance from your controller by $this->twig();

7. Twig template location:
--------------------------
The Twig template is configurable throw a config variable. Set <code>$config['twig_dir'] = 'your_chosen_dir';</code> at **{APPDIR}/config/config.php**. By default it is "twig".